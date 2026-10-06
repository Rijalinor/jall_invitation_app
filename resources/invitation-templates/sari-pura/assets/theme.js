/* Sari Pura — "carved lines draw once".
 *
 * The invitation must be fully readable without this file: nothing here is
 * what makes content appear. Reveals are armed only after the script has run,
 * and the cover opens through a real anchor even when nothing below executes.
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
    if (root.dataset.motion === 'expressive' && !reducedMotion) {
        document.querySelector('.sp-cover__engraving')?.classList.add('is-drawing');
    }
    document.querySelector('#sari-content')?.focus();
}

document.querySelector('[data-open-invitation]')?.addEventListener('click', async () => {
    openInvitation();
    if (music) {
        await music.play().catch(() => {});
        musicToggle?.setAttribute('data-playing', 'true');
    }
});

// A reload that lands directly on the invitation fragment hides the cover by
// CSS; without this the gate would still be shut and the guest would see nothing.
if (document.querySelector('#sari-content:target')) {
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

// The index marks where the reader currently is, so a long scroll never loses them.
if ('IntersectionObserver' in window) {
    const navLinks = Array.from(document.querySelectorAll('[data-dock-link]'));
    const tracked = navLinks
        .map((link) => document.getElementById(link.dataset.dockLink))
        .filter(Boolean);

    if (tracked.length) {
        const navObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                navLinks.forEach((link) => link.classList.toggle('is-active', link.dataset.dockLink === entry.target.id));
            });
        }, { rootMargin: '-45% 0px -45% 0px' });
        tracked.forEach((section) => navObserver.observe(section));
    }
}

/* Reveal in one pass. `.sp-armed` is added only when motion is allowed, so a
   blocked or failed script leaves every section already visible. */
(function () {
    const mode = root.dataset.motion || 'calm';

    if (mode === 'off' || reducedMotion || !('IntersectionObserver' in window)) {
        return;
    }

    document.documentElement.classList.add('sp-armed');

    const targets = Array.from(document.querySelectorAll('[data-reveal]'));
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: .12, rootMargin: '0px 0px -8% 0px' });

    targets.forEach((element) => {
        // Siblings inside one group arrive in sequence; unrelated sections do not.
        const siblings = Array.from(element.parentElement?.children || []).filter((child) => child.hasAttribute('data-reveal'));
        element.style.setProperty('--sp-reveal-i', String(Math.min(Math.max(siblings.indexOf(element), 0), 5)));
        observer.observe(element);
    });
})();

/* The dock steps aside while the reader moves forward, so it never covers the
   bottom of a full-height section. It returns as soon as they scroll back up. */
(function () {
    const nav = document.querySelector('.sp-index');

    if (!nav) {
        return;
    }

    let lastY = window.scrollY;
    let ticking = false;

    window.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;

        window.requestAnimationFrame(() => {
            const y = window.scrollY;

            if (y > lastY + 8 && y > 200) {
                nav.classList.add('is-hidden');
            } else if (y < lastY - 8) {
                nav.classList.remove('is-hidden');
            }

            lastY = y;
            ticking = false;
        });
    }, { passive: true });
})();
