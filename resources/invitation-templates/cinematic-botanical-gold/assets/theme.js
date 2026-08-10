const root = document.body;
const main = document.querySelector('main');
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
    main?.removeAttribute('inert');
    main?.focus();
    if (music) await music.play().catch(() => {});
    if (musicToggle) {
        musicToggle.dataset.playing = music?.paused ? 'false' : 'true';
        musicToggle.setAttribute('aria-label', music?.paused ? 'Putar musik' : 'Jeda musik');
    }
});

musicToggle?.addEventListener('click', async () => {
    if (music.paused) await music.play().catch(() => {}); else music.pause();
    musicToggle.dataset.playing = music.paused ? 'false' : 'true';
    musicToggle.setAttribute('aria-label', music.paused ? 'Putar musik' : 'Jeda musik');
});

document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
    const label = button.textContent;
    await navigator.clipboard.writeText(button.dataset.copy).catch(() => {});
    button.textContent = 'Tersalin';
    setTimeout(() => button.textContent = label, 1600);
}));

document.querySelector('[data-share]')?.addEventListener('click', async (event) => {
    const url = event.currentTarget.dataset.shareUrl;
    if (navigator.share) await navigator.share({ title: document.title, url }).catch(() => {});
    else await navigator.clipboard.writeText(url).catch(() => {});
});

document.querySelectorAll('[data-countdown]').forEach((element) => {
    const output = element.querySelector('[data-countdown-output]');
    const update = () => {
        const seconds = Math.floor((new Date(element.dataset.countdown) - new Date()) / 1000);
        if (seconds <= 0) return output.textContent = 'Hari bahagia telah tiba';
        const values = {
            days: Math.floor(seconds / 86400),
            hours: Math.floor(seconds % 86400 / 3600),
            minutes: Math.floor(seconds % 3600 / 60),
            seconds: seconds % 60,
        };
        Object.entries(values).forEach(([unit, value]) => output.querySelector(`[data-countdown-unit="${unit}"]`).textContent = String(value).padStart(2, '0'));
    };
    update();
    setInterval(update, 1000);
});

const lightbox = document.querySelector('[data-lightbox]');
document.querySelectorAll('[data-lightbox-src]').forEach((button) => button.addEventListener('click', () => {
    const image = lightbox.querySelector('[data-lightbox-image]');
    image.src = button.dataset.lightboxSrc;
    image.alt = button.dataset.lightboxAlt;
    lightbox.showModal();
}));
document.querySelector('[data-lightbox-close]')?.addEventListener('click', () => lightbox.close());
lightbox?.addEventListener('click', (event) => { if (event.target === lightbox) lightbox.close(); });

if (root.dataset.motion !== 'off' && !reducedMotion) {
    const revealables = document.querySelectorAll('.cbg-section, .cbg-countdown, .cbg-ribbon, .cbg-closing');
    revealables.forEach((element) => element.classList.add('cbg-reveal'));
    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('cbg-reveal--visible');
        observer.unobserve(entry.target);
    }), { threshold: .12 });
    revealables.forEach((element) => observer.observe(element));
}

const pages = [...document.querySelectorAll('main > .cbg-section, main > .cbg-countdown, main > .cbg-shared, main > .cbg-ribbon, main > .cbg-closing')];
let paging = false;

window.addEventListener('wheel', (event) => {
    if (!root.classList.contains('invitation-open') || Math.abs(event.deltaY) < 28 || paging) return;
    const current = pages.reduce((best, page, index) => {
        const distance = Math.abs(page.getBoundingClientRect().top - parseFloat(getComputedStyle(root).getPropertyValue('--cbg-nav-height')));
        return distance < best.distance ? { index, distance } : best;
    }, { index: 0, distance: Infinity }).index;
    const next = Math.max(0, Math.min(pages.length - 1, current + Math.sign(event.deltaY)));

    if (next === current) return;
    event.preventDefault();
    paging = true;
    pages[next].scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth' });
    setTimeout(() => paging = false, 760);
}, { passive: false });
