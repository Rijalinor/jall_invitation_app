/* Ensō — the ring draws itself once, then the garden breathes.
 *
 * The invitation must be fully readable without this file: nothing here makes
 * content appear. Animations are armed only after the script runs, and the cover
 * opens through a real anchor even when nothing below executes.
 */
const root = document.body;
const music = document.querySelector('[data-music]');
const musicToggle = document.querySelector('[data-music-toggle]');
const html = document.documentElement;
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const mode = root.dataset.motion || 'calm';
const motionAllowed = mode !== 'off' && !reducedMotion;

document.querySelectorAll('[data-cover-video]').forEach((video) => {
    if (reducedMotion) return video.remove();
    video.play().catch(() => {});
    video.addEventListener('error', () => video.remove(), { once: true });
});

function openInvitation() {
    root.classList.add('invitation-open');
    document.querySelector('#invitation-content')?.focus();
}

document.querySelector('[data-open-invitation]')?.addEventListener('click', async () => {
    openInvitation();
    if (music) {
        await music.play().catch(() => {});
        musicToggle?.setAttribute('data-playing', 'true');
        const label = musicToggle?.querySelector('span');
        if (label) label.textContent = '♫';
    }
});

// A reload that lands directly on the invitation fragment hides the cover by CSS;
// without this the gate would still be shut and the guest would see nothing.
if (document.querySelector('#invitation-content:target')) {
    openInvitation();
}

musicToggle?.addEventListener('click', async () => {
    if (!music) return;
    const label = musicToggle.querySelector('span');
    if (music.paused) {
        await music.play().catch(() => {});
        musicToggle.setAttribute('data-playing', 'true');
        musicToggle.setAttribute('aria-label', 'Jeda musik');
        if (label) label.textContent = '♫';
    } else {
        music.pause();
        musicToggle.setAttribute('data-playing', 'false');
        musicToggle.setAttribute('aria-label', 'Putar musik');
        if (label) label.textContent = '♪';
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

// The nav keeps a small mark on the section the guest is reading.
if ('IntersectionObserver' in window) {
    const links = Array.from(document.querySelectorAll('[data-nav-link]'));
    const tracked = links.map((link) => document.getElementById(link.dataset.navLink)).filter(Boolean);

    if (tracked.length) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                links.forEach((link) => link.classList.toggle('is-active', link.dataset.navLink === entry.target.id));
            });
        }, { rootMargin: '-45% 0px -45% 0px' });
        tracked.forEach((section) => observer.observe(section));
    }
}

/* One scroll listener publishes two numbers — pixels scrolled and page progress —
   and the washes, petals and brush rule read them. rAF-throttled and passive, so
   it never reads layout per frame. Skipped entirely when motion is off. */
(function () {
    if (!motionAllowed) return;

    let ticking = false;

    const update = () => {
        const range = document.documentElement.scrollHeight - window.innerHeight;
        html.style.setProperty('--en-scroll', String(Math.round(window.scrollY)));
        html.style.setProperty('--en-progress', String(range > 0 ? Math.min(1, window.scrollY / range) : 0));
        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(update);
    }, { passive: true });

    update();
})();

/* Reveal in one pass. `.en-armed` is added only when motion is allowed, so a
   blocked or failed script leaves every section already visible. */
(function () {
    if (!motionAllowed || !('IntersectionObserver' in window)) return;

    html.classList.add('en-armed');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: .12, rootMargin: '0px 0px -8% 0px' });

    document.querySelectorAll('[data-reveal]').forEach((element) => {
        // Siblings inside one group settle in sequence; unrelated sections do not.
        const siblings = Array.from(element.parentElement?.children || []).filter((child) => child.hasAttribute('data-reveal'));
        element.style.setProperty('--en-i', String(Math.min(Math.max(siblings.indexOf(element), 0), 5)));
        observer.observe(element);
    });
})();
