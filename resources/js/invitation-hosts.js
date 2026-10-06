/* Host detail dialog, shared by every template.
 *
 * A compact host card opens this dialog for the full profile. The inline
 * details are collapsed only after the dialog is wired, so a blocked or failed
 * script leaves every detail visible in the card.
 */
(function () {
    const dialog = document.querySelector('[data-host-dialog]');
    const openers = Array.from(document.querySelectorAll('[data-host-open]'));

    if (!dialog || !openers.length || typeof dialog.showModal !== 'function') {
        return;
    }

    const roleEl = dialog.querySelector('[data-host-dialog-role]');
    const nameEl = dialog.querySelector('[data-host-dialog-name]');
    const bodyEl = dialog.querySelector('[data-host-dialog-body]');

    openers.forEach((button) => {
        button.addEventListener('click', () => {
            const card = button.closest('[data-host]');
            const details = card?.querySelector('[data-host-details]');

            if (!details) {
                return;
            }

            if (roleEl) roleEl.textContent = button.dataset.hostRole || '';
            if (nameEl) nameEl.textContent = button.dataset.hostName || '';
            if (bodyEl) bodyEl.replaceChildren(details.cloneNode(true));

            dialog.showModal();
        });
    });

    dialog.querySelector('[data-host-dialog-close]')?.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });

    // Collapse the inline details only once the dialog can actually open.
    document.documentElement.classList.add('hosts-armed');
})();
