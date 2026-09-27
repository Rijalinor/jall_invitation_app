const root = document.body;
const music = document.querySelector('[data-music]');
const musicToggle = document.querySelector('[data-music-toggle]');
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.querySelectorAll('[data-cover-video]').forEach((video) => {
    if (reducedMotion) return video.remove();
    video.play().catch(() => {});
    video.addEventListener('error', () => video.remove(), { once: true });
});

document.querySelector('[data-open-invitation]')?.addEventListener('click', async () => {
    root.classList.add('invitation-open');
    const content = document.querySelector('#invitation-content');
    content?.removeAttribute('inert');
    content?.focus();
    if (music) {
        await music.play().catch(() => {});
        musicToggle?.setAttribute('data-playing', 'true');
    }
});

musicToggle?.addEventListener('click', async () => {
    if (!music) return;
    if (music.paused) {
        await music.play().catch(() => {});
        musicToggle.setAttribute('data-playing', 'true');
        musicToggle.querySelector('span').textContent = '🎵';
        musicToggle.setAttribute('aria-label', 'Jeda musik');
    } else {
        music.pause();
        musicToggle.setAttribute('data-playing', 'false');
        musicToggle.querySelector('span').textContent = '🔇';
        musicToggle.setAttribute('aria-label', 'Putar musik');
    }
});

document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
    const label = button.textContent;
    await navigator.clipboard.writeText(button.dataset.copy).catch(() => {});
    button.textContent = '✅ Tersalin!';
    setTimeout(() => button.textContent = label, 1800);
}));

document.querySelectorAll('[data-share]').forEach((button) => button.addEventListener('click', async () => {
    const url = button.dataset.shareUrl;
    if (navigator.share) await navigator.share({ title: document.title, url }).catch(() => {});
    else {
        await navigator.clipboard.writeText(url).catch(() => {});
        alert('Tautan undangan berhasil disalin! 🚀');
    }
}));

const lightbox = document.querySelector('[data-lightbox]');
document.querySelectorAll('[data-lightbox-src]').forEach((button) => button.addEventListener('click', () => {
    const image = lightbox?.querySelector('[data-lightbox-image]');
    if (image && lightbox) {
        image.src = button.dataset.lightboxSrc;
        image.alt = button.dataset.lightboxAlt;
        lightbox.showModal();
    }
}));

document.querySelector('[data-lightbox-close]')?.addEventListener('click', () => lightbox?.close());
lightbox?.addEventListener('click', (event) => { if (event.target === lightbox) lightbox?.close(); });

document.querySelectorAll('[data-countdown]').forEach((element) => {
    const output = element.querySelector('[data-countdown-output]');
    const update = () => {
        if (!output) return;
        const seconds = Math.floor((new Date(element.dataset.countdown) - new Date()) / 1000);
        if (seconds <= 0) return output.textContent = '🎉 HARI BAHAGIA TELAH TIBA! 🎉';
        const values = {
            days: Math.floor(seconds / 86400),
            hours: Math.floor(seconds % 86400 / 3600),
            minutes: Math.floor(seconds % 3600 / 60),
            seconds: seconds % 60,
        };
        Object.entries(values).forEach(([unit, value]) => {
            const unitEl = output.querySelector(`[data-countdown-unit="${unit}"]`);
            if (unitEl) unitEl.textContent = String(value).padStart(2, '0');
        });
    };
    update();
    setInterval(update, 1000);
});

/* ── Motion: "the page turns, and things pop up" ───────────────────────────
   Elements are hidden only under `.pop-armed`, a class this script adds to the
   document root. If scripting is blocked, slow, or fails, nothing is hidden and
   the whole invitation stays readable. */
(function () {
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const mode = root.dataset.motion || 'expressive';

    if (mode === 'off' || prefersReduced || !('IntersectionObserver' in window)) {
        return;
    }

    document.documentElement.classList.add('pop-armed', 'pop-' + mode);

    const REVEAL = [
        '.fsb-section-header',
        '.fsb-hero__text',
        '.fsb-speech-bubble',
        '.fsb-host',
        '.fsb-event',
        '.fsb-countdown__card',
        '.fsb-story-card',
        '.fsb-gallery__item',
        '.fsb-map-card',
        '.fsb-gift-card',
        '.fsb-contact-card',
    ];

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-up');
            observer.unobserve(entry.target);
        });
    }, { rootMargin: '0px 0px -12% 0px', threshold: 0.1 });

    REVEAL.forEach((selector) => {
        document.querySelectorAll(selector).forEach((element) => {
            // Siblings inside one group rise in sequence; separate sections do not.
            const siblings = Array.from(element.parentElement?.children || [])
                .filter((child) => child.matches(selector));
            const index = Math.min(Math.max(siblings.indexOf(element), 0), 5);

            element.dataset.pop = '';
            element.style.setProperty('--pop-i', String(index));
            observer.observe(element);
        });
    });

    /* Signature moment: the cover leaves like a turning page. */
    document.querySelector('[data-open-invitation]')?.addEventListener('click', () => {
        document.documentElement.classList.add('pop-cover-turning');

        // Cleared again so restoring the page from cache cannot leave the cover
        // visible with an already turned card.
        window.setTimeout(() => document.documentElement.classList.remove('pop-cover-turning'), 1100);
    });

    /* Countdown digits flip like a paper clock. */
    document.querySelectorAll('[data-countdown-unit]').forEach((unit) => {
        let previous = unit.textContent;

        new MutationObserver(() => {
            if (unit.textContent === previous) return;
            previous = unit.textContent;
            unit.classList.remove('is-flipping');
            void unit.offsetWidth;
            unit.classList.add('is-flipping');
        }).observe(unit, { childList: true, characterData: true, subtree: true });
    });
})();
