const root = document.body;
const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const motion = root.dataset.motion !== 'off' && !reduced;
const music = document.querySelector('[data-music]');
const musicButton = document.querySelector('[data-music-toggle]');
const content = document.querySelector('#tp-content');

/* ── Motion: the loom ────────────────────────────────────────────────────────
   The page is only armed when this script can reveal it again, so a slow,
   blocked, or failed script never leaves a blank invitation behind the cover. */
const canReveal = motion && 'IntersectionObserver' in window;

if (canReveal) {
    document.documentElement.classList.add('tp-armed', root.dataset.motion === 'calm' ? 'tp-calm' : 'tp-expressive');
}

const reveal = () => {
    const items = document.querySelectorAll('[data-reveal]');

    items.forEach((item) => {
        // Stagger inside a group; never across unrelated sections.
        const siblings = Array.from(item.parentElement?.children || [])
            .filter((child) => child.hasAttribute('data-reveal'));
        item.style.setProperty('--tp-i', String(Math.min(Math.max(siblings.indexOf(item), 0), 4)));
    });

    if (!canReveal) {
        items.forEach((item) => item.classList.add('is-woven'));

        return;
    }

    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-woven');
        observer.unobserve(entry.target);
    }), { threshold: .12 });
    items.forEach((item) => observer.observe(item));
};

/* ── Cover ───────────────────────────────────────────────────────────────── */

document.querySelector('[data-open-invitation]')?.addEventListener('click', async (event) => {
    // The control is a real anchor so the cover can also be dismissed by the
    // :has(main:target) rule when this script never loads.
    event.preventDefault();
    root.classList.add('invitation-open');
    content?.removeAttribute('inert');
    content?.focus();
    reveal();
    await music?.play().catch(() => {});
});

if (canReveal) {
    content?.setAttribute('inert', '');
}

/* ── Navigation: the knot follows the section in view ────────────────────── */

const nav = document.querySelector('.tp-nav');
const knot = nav?.querySelector('.tp-nav__knot');
const navLinks = nav ? Array.from(nav.querySelectorAll('a')) : [];

if (nav && navLinks.length && 'IntersectionObserver' in window) {
    const mark = (link) => {
        navLinks.forEach((item) => item.removeAttribute('aria-current'));
        link.setAttribute('aria-current', 'true');

        if (knot) {
            knot.style.setProperty('--tp-knot-x', link.offsetLeft + 'px');
            knot.style.setProperty('--tp-knot-w', link.offsetWidth + 'px');
        }
    };

    const sections = navLinks
        .map((link) => document.getElementById(link.getAttribute('href').slice(1)))
        .filter(Boolean);

    const navObserver = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const link = navLinks.find((item) => item.getAttribute('href') === '#' + entry.target.id);
        if (link) mark(link);
    }), { rootMargin: '-45% 0px -50% 0px' });

    sections.forEach((section) => navObserver.observe(section));
}

/* ── Music ───────────────────────────────────────────────────────────────── */

const syncMusic = () => {
    if (!musicButton) return;
    const playing = Boolean(music) && !music.paused;
    musicButton.setAttribute('aria-pressed', String(playing));
    musicButton.setAttribute('aria-label', playing ? 'Jeda musik' : 'Putar musik');
};

music?.addEventListener('play', syncMusic);
music?.addEventListener('pause', syncMusic);
musicButton?.addEventListener('click', async () => {
    if (!music) return;
    music.paused ? await music.play().catch(() => {}) : music.pause();
});

/* ── Countdown ───────────────────────────────────────────────────────────── */

document.querySelectorAll('[data-countdown]').forEach((element) => {
    const update = () => {
        const output = element.querySelector('[data-countdown-output]');
        const seconds = Math.floor((new Date(element.dataset.countdown) - new Date()) / 1000);

        if (seconds <= 0) {
            if (output) output.textContent = 'Hari bahagia telah tiba';

            return false;
        }

        const values = {
            days: Math.floor(seconds / 86400),
            hours: Math.floor(seconds % 86400 / 3600),
            minutes: Math.floor(seconds % 3600 / 60),
            seconds: seconds % 60,
        };

        Object.entries(values).forEach(([unit, value]) => {
            const node = element.querySelector(`[data-countdown-unit="${unit}"]`);
            if (node) node.textContent = String(value).padStart(2, '0');
        });

        return true;
    };

    if (update()) {
        const timer = setInterval(() => {
            if (!update()) clearInterval(timer);
        }, 1000);
    }
});

/* ── Copy, share, lightbox ───────────────────────────────────────────────── */

const copy = async (value) => {
    try {
        await navigator.clipboard.writeText(value);

        return true;
    } catch {
        return false;
    }
};

document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
    const label = button.textContent;
    if (await copy(button.dataset.copy)) {
        button.textContent = 'Tersalin';
        setTimeout(() => { button.textContent = label; }, 1500);
    }
}));

document.querySelectorAll('[data-share]').forEach((button) => button.addEventListener('click', async () => {
    const url = button.dataset.shareUrl;
    if (navigator.share) return navigator.share({ title: document.title, url }).catch(() => {});

    const label = button.querySelector('[data-share-label]');
    if (label && await copy(url)) {
        const previous = label.textContent;
        label.textContent = 'Tersalin';
        setTimeout(() => { label.textContent = previous; }, 1500);
    }
}));

const dialog = document.querySelector('[data-lightbox]');

document.querySelectorAll('[data-lightbox-src]').forEach((button) => button.addEventListener('click', () => {
    const image = dialog?.querySelector('[data-lightbox-image]');
    if (!dialog || !image) return;
    image.src = button.dataset.lightboxSrc;
    image.alt = button.dataset.lightboxAlt;
    dialog.showModal();
}));

document.querySelector('[data-lightbox-close]')?.addEventListener('click', () => dialog?.close());
dialog?.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
