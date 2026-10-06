/* Progressive AJAX submit for the shared guest forms.
 *
 * The RSVP, guestbook and combined confirmation forms keep their plain POST
 * action, so a guest without scripting still submits and gets the server's
 * redirect and flash message. When scripting is available this intercepts the
 * submit, sends it as JSON with fetch, and writes the result back into the form,
 * so the page never reloads and the guest keeps their place.
 */
(function () {
    const forms = Array.from(document.querySelectorAll('form[data-invitation-form]'));

    if (!forms.length || typeof window.fetch !== 'function' || typeof window.FormData !== 'function') {
        return;
    }

    const firstError = (errors) => {
        for (const field of Object.keys(errors || {})) {
            const value = errors[field];

            if (Array.isArray(value) && value.length) return value[0];
            if (typeof value === 'string' && value) return value;
        }

        return 'Terjadi kesalahan. Silakan coba lagi.';
    };

    const setNotice = (form, target, message) => {
        const element = form.querySelector(target);

        if (element) {
            element.textContent = message || '';
            element.hidden = !message;
        }

        return element;
    };

    forms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();

            if (form.dataset.submitting === 'true') {
                return;
            }

            const button = form.querySelector('[type="submit"]');
            const label = button ? button.textContent : '';
            const status = setNotice(form, '[data-form-status]', '');

            setNotice(form, '[data-form-errors]', '');
            form.dataset.submitting = 'true';

            if (button) {
                button.disabled = true;
                button.textContent = 'Mengirim…';
            }

            fetch(form.action, {
                method: form.method || 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                body: new FormData(form),
                credentials: 'same-origin',
            })
                .then(async (response) => {
                    const data = await response.json().catch(() => null);

                    if (response.ok) {
                        setNotice(form, '[data-form-status]', (data && data.message) || 'Terkirim.');
                        form.reset();
                        if (status) status.scrollIntoView({ block: 'nearest' });

                        return;
                    }

                    if (response.status === 422) {
                        setNotice(form, '[data-form-errors]', firstError(data && data.errors));

                        return;
                    }

                    // Anything unexpected: fall back to the plain browser submit.
                    form.submit();
                })
                .catch(() => form.submit())
                .finally(() => {
                    form.dataset.submitting = 'false';

                    if (button) {
                        button.disabled = false;
                        button.textContent = label;
                    }
                });
        });
    });
})();
