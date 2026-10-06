/* Celestial Vow — "the sky writes your names".
 *
 * The invitation must be fully readable without this file: nothing here makes
 * content appear. Animations are armed only after the script runs, and the cover
 * opens through a real anchor even when nothing below executes.
 */
const root = document.body;
const music = document.querySelector('[data-music]');
const musicToggle = document.querySelector('[data-music-toggle]');
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.querySelectorAll('[data-cover-video]').forEach((video) => {
    if (reducedMotion) return video.remove();
    video.play().catch(() => {});
    video.addEventListener('error', () => video.remove(), { once: true });
});

function openInvitation() {
    root.classList.add('invitation-open');
    document.querySelector('#celestial-content')?.focus();
}

document.querySelector('[data-open-invitation]')?.addEventListener('click', async () => {
    openInvitation();
    if (music) {
        await music.play().catch(() => {});
        musicToggle?.setAttribute('data-playing', 'true');
    }
});

// A reload that lands directly on the invitation fragment hides the cover by CSS;
// without this the gate would still be shut and the guest would see nothing.
if (document.querySelector('#celestial-content:target')) {
    openInvitation();
}

musicToggle?.addEventListener('click', async () => {
    if (!music) return;
    if (music.paused) {
        await music.play().catch(() => {});
        musicToggle.setAttribute('data-playing', 'true');
        musicToggle.querySelector('span').textContent = '♩';
        musicToggle.setAttribute('aria-label', 'Jeda musik');
    } else {
        music.pause();
        musicToggle.setAttribute('data-playing', 'false');
        musicToggle.querySelector('span').textContent = '♪';
        musicToggle.setAttribute('aria-label', 'Putar musik');
    }
});

document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
    const label = button.textContent;
    await navigator.clipboard.writeText(button.dataset.copy).catch(() => {});
    button.textContent = 'Tersalin';
    setTimeout(() => { button.textContent = label; }, 1500);
}));

document.querySelectorAll('[data-share]').forEach((button) => button.addEventListener('click', async () => {
    const url = button.dataset.shareUrl;
    if (navigator.share) {
        await navigator.share({ title: document.title, url }).catch(() => {});
    } else {
        await navigator.clipboard.writeText(url).catch(() => {});
    }
}));

document.querySelectorAll('[data-countdown]').forEach((element) => {
    const output = element.querySelector('[data-countdown-output]');
    const update = () => {
        if (!output) return;
        const seconds = Math.floor((new Date(element.dataset.countdown) - new Date()) / 1000);
        if (seconds <= 0) {
            output.textContent = 'Hari bahagia telah tiba';
            return;
        }
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

// The dot rail marks where the reader is, so a long scroll never loses them.
if ('IntersectionObserver' in window) {
    const dots = Array.from(document.querySelectorAll('[data-dot-link]'));
    const tracked = dots
        .map((dot) => document.getElementById(dot.dataset.dotLink))
        .filter(Boolean);

    if (tracked.length) {
        const dotObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                dots.forEach((dot) => dot.classList.toggle('is-active', dot.dataset.dotLink === entry.target.id));
            });
        }, { rootMargin: '-45% 0px -45% 0px' });
        tracked.forEach((section) => dotObserver.observe(section));
    }
}

/* The star layers drift a little as the guest scrolls. One rAF-throttled
   listener writes a single custom property; the CSS does the rest. */
if (!reducedMotion) {
    let ticking = false;

    window.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;

        window.requestAnimationFrame(() => {
            document.documentElement.style.setProperty('--cel-scroll', String(Math.round(window.scrollY)));
            ticking = false;
        });
    }, { passive: true });
}

/* Reveal in one pass. `.cel-armed` is added only when motion is allowed, so a
   blocked or failed script leaves every section already visible. */
(function () {
    const mode = root.dataset.motion || 'expressive';

    if (mode === 'off' || reducedMotion || !('IntersectionObserver' in window)) {
        return;
    }

    document.documentElement.classList.add('cel-armed');

    const targets = Array.from(document.querySelectorAll('[data-reveal]'));
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: .12, rootMargin: '0px 0px -8% 0px' });

    targets.forEach((element) => {
        // Siblings inside one group rise in sequence; unrelated sections do not.
        const siblings = Array.from(element.parentElement?.children || []).filter((child) => child.hasAttribute('data-reveal'));
        element.style.setProperty('--cel-reveal-i', String(Math.min(Math.max(siblings.indexOf(element), 0), 5)));
        observer.observe(element);
    });
})();
