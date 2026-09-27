const root = document.body;
const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const motion = root.dataset.motion !== 'off' && !reduced;
const music = document.querySelector('[data-music]');
const musicButton = document.querySelector('[data-music-toggle]');

document.querySelectorAll('[data-cover-video]').forEach((video) => {
    if (reduced) return video.remove();
    video.play().catch(() => {});
    video.addEventListener('error', () => video.remove(), { once: true });
});

/* ── Motion: "light slowly filling a dark atelier" ─────────────────────────
   The page is only armed when this script can reveal it again, so a slow,
   blocked, or failed script never leaves a blank invitation behind the cover. */
const canReveal = motion && 'IntersectionObserver' in window;

if (canReveal) {
    document.documentElement.classList.add('la-armed', root.dataset.motion === 'calm' ? 'la-calm' : 'la-expressive');
}

/* While the cover sits over the page the invitation stays out of the tab order.
   Done here rather than in the markup so that a page rendered without scripting
   remains fully usable. */
const gated = [document.querySelector('.la-nav'), document.querySelector('main')];
gated.forEach((element) => element?.setAttribute('inert', ''));

const reveal = () => {
    const items = document.querySelectorAll('[data-reveal]');

    items.forEach((item) => {
        // Stagger inside a group; never across unrelated sections.
        const siblings = Array.from(item.parentElement?.children || [])
            .filter((child) => child.hasAttribute('data-reveal'));
        item.style.setProperty('--la-i', String(Math.min(Math.max(siblings.indexOf(item), 0), 4)));
    });

    if (!canReveal) {
        items.forEach((item) => item.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
    }), { threshold: .12 });
    items.forEach((item) => observer.observe(item));
};

document.querySelector('[data-open-invitation]')?.addEventListener('click', async (event) => {
    // The control is a real anchor so the cover can still be opened by the
    // :has(main:target) rule when this script never loads.
    event.preventDefault();
    root.classList.add('invitation-open');
    gated.forEach((element) => element?.removeAttribute('inert'));
    reveal();
    await music?.play().catch(() => {});
});

const syncMusic = () => {
    if (!musicButton) return;
    const playing = Boolean(music) && !music.paused;
    musicButton.setAttribute('aria-pressed', String(playing));
    musicButton.setAttribute('aria-label', playing ? 'Jeda musik' : 'Putar musik');
};
music?.addEventListener('play', syncMusic); music?.addEventListener('pause', syncMusic);
musicButton?.addEventListener('click', async () => { if (!music) return; music.paused ? await music.play().catch(() => {}) : music.pause(); });

document.querySelectorAll('[data-countdown]').forEach((element) => {
    const update = () => {
        const seconds = Math.floor((new Date(element.dataset.countdown) - new Date()) / 1000);
        if (seconds <= 0) { element.querySelector('[data-countdown-output]').textContent = 'Hari bahagia telah tiba'; return false; }
        const values = { days: Math.floor(seconds / 86400), hours: Math.floor(seconds % 86400 / 3600), minutes: Math.floor(seconds % 3600 / 60), seconds: seconds % 60 };
        Object.entries(values).forEach(([key, value]) => { const node = element.querySelector(`[data-countdown-unit="${key}"]`); if (node) node.textContent = String(value).padStart(2, '0'); });
        return true;
    };
    if (update()) { const timer = setInterval(() => { if (!update()) clearInterval(timer); }, 1000); }
});

const copy = async (value) => { try { await navigator.clipboard.writeText(value); return true; } catch { return false; } };
document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => { const label = button.textContent; if (await copy(button.dataset.copy)) { button.textContent = 'Tersalin'; setTimeout(() => { button.textContent = label; }, 1400); } }));
document.querySelectorAll('[data-share]').forEach((button) => button.addEventListener('click', async () => { const url = button.dataset.shareUrl; if (navigator.share) return navigator.share({ title: document.title, url }).catch(() => {}); const label = button.querySelector('[data-share-label]'); if (label && await copy(url)) { const old = label.textContent; label.textContent = 'Tersalin'; setTimeout(() => { label.textContent = old; }, 1400); } }));
const dialog = document.querySelector('[data-lightbox]');
document.querySelectorAll('[data-lightbox-src]').forEach((button) => button.addEventListener('click', () => { const image = dialog?.querySelector('[data-lightbox-image]'); if (!dialog || !image) return; image.src = button.dataset.lightboxSrc; image.alt = button.dataset.lightboxAlt; dialog.showModal(); }));
document.querySelector('[data-lightbox-close]')?.addEventListener('click', () => dialog?.close());
dialog?.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
