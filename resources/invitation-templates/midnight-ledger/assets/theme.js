const root = document.body;
const content = document.querySelector('#top');
const music = document.querySelector('[data-music]');
const musicToggle = document.querySelector('[data-music-toggle]');
const progress = document.querySelector('[data-scroll-progress]');

document.querySelector('[data-open-invitation]')?.addEventListener('click', async () => {
    root.classList.add('invitation-open');
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
    const label = button.textContent;
    await navigator.clipboard.writeText(button.dataset.copy).catch(() => {});
    button.textContent = 'Berhasil disalin';
    setTimeout(() => button.textContent = label, 1800);
}));

document.querySelector('[data-share]')?.addEventListener('click', async (event) => {
    const url = event.currentTarget.dataset.shareUrl;
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

const revealItems = document.querySelectorAll('[data-reveal], .ml-hosts article, .ml-story li, .ml-gallery figure');
if ('IntersectionObserver' in window && root.dataset.motion !== 'off') {
    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
    }), { threshold: .12 });
    revealItems.forEach((item) => {
        item.dataset.reveal = '';
        observer.observe(item);
    });
} else revealItems.forEach((item) => item.classList.add('is-visible'));

if (progress) addEventListener('scroll', () => {
    const range = document.documentElement.scrollHeight - innerHeight;
    progress.style.transform = `scaleY(${range > 0 ? scrollY / range : 0})`;
}, { passive: true });

const mobileLinks = [...document.querySelectorAll('.ml-mobile-nav a')];
if ('IntersectionObserver' in window && mobileLinks.length) {
    const sectionObserver = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        mobileLinks.forEach((link) => {
            const active = link.hash === `#${entry.target.id}`;
            link.classList.toggle('is-active', active);
            if (active) link.setAttribute('aria-current', 'location'); else link.removeAttribute('aria-current');
        });
    }), { rootMargin: '-25% 0px -60%' });
    mobileLinks.forEach((link) => sectionObserver.observe(document.querySelector(link.hash)));
}

document.querySelectorAll('[data-countdown]').forEach((element) => {
    const output = element.querySelector('[data-countdown-output]');
    const units = Object.fromEntries([...output.querySelectorAll('[data-countdown-unit]')].map((unit) => [unit.dataset.countdownUnit, unit]));
    let timer;
    const update = () => {
        const seconds = Math.floor((new Date(element.dataset.countdown) - new Date()) / 1000);
        const remaining = Math.max(0, seconds);
        units.days.textContent = String(Math.floor(remaining / 86400)).padStart(2, '0');
        units.hours.textContent = String(Math.floor(remaining % 86400 / 3600)).padStart(2, '0');
        units.minutes.textContent = String(Math.floor(remaining % 3600 / 60)).padStart(2, '0');
        units.seconds.textContent = String(remaining % 60).padStart(2, '0');
        if (seconds <= 0) clearInterval(timer);
    };
    update();
    if (new Date(element.dataset.countdown) > new Date()) timer = setInterval(update, 1000);
});
