/* Gallery lightbox, shared by every template.
 *
 * One photo at a time is not a gallery: the guest has to be able to move to the
 * next one. This wires every thumbnail to a single dialog and adds prev/next
 * controls — injected here, so no template has to declare them — plus arrow keys
 * and a horizontal swipe on touch.
 *
 * Nothing here is what makes content appear. Without the script the invitation
 * and its gallery still render; only the enlarged view is lost.
 */
(function () {
    const lightbox = document.querySelector('[data-lightbox]');
    const image = lightbox ? lightbox.querySelector('[data-lightbox-image]') : null;
    const triggers = Array.from(document.querySelectorAll('[data-lightbox-src]'));

    if (!lightbox || !image || !triggers.length) {
        return;
    }

    const open = () => {
        if (typeof lightbox.showModal === 'function') {
            if (!lightbox.open) lightbox.showModal();
        } else {
            lightbox.setAttribute('open', '');
        }
    };

    const close = () => {
        if (typeof lightbox.close === 'function') lightbox.close();
        else lightbox.removeAttribute('open');
    };

    let index = 0;

    const render = () => {
        const trigger = triggers[index];

        if (!trigger) return;

        image.src = trigger.dataset.lightboxSrc || '';
        image.alt = trigger.dataset.lightboxAlt || '';
    };

    const step = (delta) => {
        if (triggers.length < 2) return;

        index = (index + delta + triggers.length) % triggers.length;
        render();
    };

    const control = (attribute, label, glyph, delta) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.setAttribute(attribute, '');
        button.setAttribute('aria-label', label);
        button.textContent = glyph;
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            step(delta);
        });
        lightbox.append(button);
    };

    if (triggers.length > 1) {
        control('data-lightbox-prev', 'Gambar sebelumnya', '‹', -1);
        control('data-lightbox-next', 'Gambar berikutnya', '›', 1);
    }

    triggers.forEach((trigger, i) => trigger.addEventListener('click', () => {
        index = i;
        render();
        open();
    }));

    lightbox.querySelector('[data-lightbox-close]')?.addEventListener('click', close);
    lightbox.addEventListener('click', (event) => { if (event.target === lightbox) close(); });

    lightbox.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') step(-1);
        else if (event.key === 'ArrowRight') step(1);
    });

    // A dragged image would start a native drag and swallow the pointer-up, so
    // the photo itself is not draggable.
    image.addEventListener('dragstart', (event) => event.preventDefault());

    // Touch swipe, for phones.
    let touchX = 0;
    let touchY = 0;
    let trackingTouch = false;

    lightbox.addEventListener('touchstart', (event) => {
        if (event.touches.length !== 1) {
            trackingTouch = false;
            return;
        }

        trackingTouch = true;
        touchX = event.touches[0].clientX;
        touchY = event.touches[0].clientY;
    }, { passive: true });

    lightbox.addEventListener('touchend', (event) => {
        if (!trackingTouch) return;
        trackingTouch = false;

        const touch = event.changedTouches[0];
        if (!touch) return;

        const dx = touch.clientX - touchX;
        const dy = touch.clientY - touchY;

        // Only a mostly horizontal drag counts, so a vertical scroll never flips
        // the photo by accident.
        if (Math.abs(dx) < 40 || Math.abs(dx) <= Math.abs(dy)) return;

        step(dx < 0 ? 1 : -1);
    }, { passive: true });

    // Mouse drag, so "geser" also works with a trackpad or mouse on desktop.
    // Only the mouse pointer type is handled here; touch is left to the block
    // above so a swipe is never counted twice.
    let mouseX = 0;
    let mouseY = 0;
    let draggingMouse = false;

    lightbox.addEventListener('pointerdown', (event) => {
        if (event.pointerType !== 'mouse' || event.button !== 0 || event.target.closest('button')) return;

        draggingMouse = true;
        mouseX = event.clientX;
        mouseY = event.clientY;
    });

    lightbox.addEventListener('pointerup', (event) => {
        if (!draggingMouse || event.pointerType !== 'mouse') return;
        draggingMouse = false;

        const dx = event.clientX - mouseX;
        const dy = event.clientY - mouseY;

        if (Math.abs(dx) < 40 || Math.abs(dx) <= Math.abs(dy)) return;

        step(dx < 0 ? 1 : -1);
    });

    lightbox.addEventListener('pointercancel', () => { draggingMouse = false; });
})();
