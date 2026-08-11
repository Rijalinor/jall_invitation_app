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
    if (music) await music.play().catch(() => {});
});

musicToggle?.addEventListener('click', async () => {
    if (music.paused) await music.play().catch(() => {}); else music.pause();
    musicToggle.querySelector('span').textContent = music.paused ? '♪' : '♩';
    musicToggle.setAttribute('aria-label', music.paused ? 'Putar musik' : 'Jeda musik');
});

document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
    const label = button.textContent;
    await navigator.clipboard.writeText(button.dataset.copy).catch(() => {});
    button.textContent = 'Tersalin';
    setTimeout(() => button.textContent = label, 1500);
}));

document.querySelectorAll('[data-share]').forEach((button) => button.addEventListener('click', async () => {
    const url = button.dataset.shareUrl;
    if (navigator.share) await navigator.share({ title: document.title, url }).catch(() => {});
    else await navigator.clipboard.writeText(url).catch(() => {});
}));

const lightbox = document.querySelector('[data-lightbox]');
document.querySelectorAll('[data-lightbox-src]').forEach((button) => button.addEventListener('click', () => {
    const image = lightbox.querySelector('[data-lightbox-image]');
    image.src = button.dataset.lightboxSrc;
    image.alt = button.dataset.lightboxAlt;
    lightbox.showModal();
}));
document.querySelector('[data-lightbox-close]')?.addEventListener('click', () => lightbox.close());
lightbox?.addEventListener('click', (event) => { if (event.target === lightbox) lightbox.close(); });

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
        Object.entries(values).forEach(([unit, value]) => {
            output.querySelector(`[data-countdown-unit="${unit}"]`).textContent = String(value).padStart(2, '0');
        });
    };
    update();
    setInterval(update, 1000);
});

if (root.dataset.motion !== 'off' && !reducedMotion) {
    const sections = document.querySelectorAll('.er-section');
    sections.forEach((section) => section.classList.add('er-reveal'));
    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('er-reveal--visible');
        observer.unobserve(entry.target);
    }), { threshold: .08 });
    sections.forEach((section) => observer.observe(section));
}
