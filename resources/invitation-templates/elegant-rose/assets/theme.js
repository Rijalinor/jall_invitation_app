const root = document.body;
const music = document.querySelector('[data-music]');
const musicToggle = document.querySelector('[data-music-toggle]');

document.querySelector('[data-open-invitation]')?.addEventListener('click', async () => {
    root.classList.add('invitation-open');
    const content = document.querySelector('#invitation-content');
    content?.removeAttribute('inert');
    content?.focus();
    if (music) await music.play().catch(() => {});
});

musicToggle?.addEventListener('click', async () => {
    if (music.paused) await music.play().catch(() => {}); else music.pause();
    musicToggle.textContent = music.paused ? 'Putar musik' : 'Jeda musik';
    musicToggle.setAttribute('aria-label', musicToggle.textContent);
});

document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
    await navigator.clipboard.writeText(button.dataset.copy).catch(() => {});
    button.textContent = 'Berhasil disalin';
}));

document.querySelector('[data-share]')?.addEventListener('click', async () => {
    const url = document.querySelector('[data-share]').dataset.shareUrl;
    if (navigator.share) await navigator.share({ title: document.title, url }).catch(() => {});
    else await navigator.clipboard.writeText(url).catch(() => {});
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

document.querySelectorAll('[data-countdown]').forEach((element) => {
    const output = element.querySelector('[data-countdown-output]');
    const update = () => {
        const seconds = Math.floor((new Date(element.dataset.countdown) - new Date()) / 1000);
        if (seconds <= 0) return output.textContent = 'Hari bahagia telah tiba';
        const days = Math.floor(seconds / 86400);
        const hours = Math.floor(seconds % 86400 / 3600);
        const minutes = Math.floor(seconds % 3600 / 60);
        output.textContent = `${days} hari · ${hours} jam · ${minutes} menit`;
    };
    update();
    setInterval(update, 60000);
});

if (root.dataset.motion !== 'off' && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const sections = document.querySelectorAll('.er-section');
    sections.forEach((section) => section.classList.add('er-reveal'));
    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('er-reveal--visible');
        observer.unobserve(entry.target);
    }), { threshold: .08 });
    sections.forEach((section) => observer.observe(section));
}
