const root = document.body;
const content = document.querySelector('#top');
const music = document.querySelector('[data-music]');
const musicToggle = document.querySelector('[data-music-toggle]');

function openInvitation() {
    root.classList.add('invitation-open');
    content?.removeAttribute('inert');
    document.querySelectorAll('.bn-hero .bn-observe').forEach((el) => el.classList.add('is-visible'));
}

document.querySelector('[data-open-invitation]')?.addEventListener('click', async () => {
    openInvitation();
    content?.focus();
    if (music) await music.play().catch(() => {});
    musicToggle?.classList.toggle('is-playing', music && !music.paused);
});

if (window.location.hash && window.location.hash !== '#top') {
    openInvitation();
}

musicToggle?.addEventListener('click', async () => {
    if (music.paused) await music.play().catch(() => {}); else music.pause();
    musicToggle.classList.toggle('is-playing', !music.paused);
    musicToggle.querySelector('span').textContent = music.paused ? 'Musik' : 'Jeda';
    musicToggle.setAttribute('aria-label', music.paused ? 'Putar musik' : 'Jeda musik');
});

document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
    const label = button.textContent;
    await navigator.clipboard.writeText(button.dataset.copy).catch(() => {});
    button.textContent = 'Tersalin ✓';
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

setTimeout(() => {
    if (window.__bnGsapLoaded) return;
    root.classList.add('bn-cover-live');
    const revealItems = document.querySelectorAll('.bn-observe');
    if ('IntersectionObserver' in window && root.dataset.motion !== 'off') {
        const revealObserver = new IntersectionObserver((entries) => entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            revealObserver.unobserve(entry.target);
        }), { threshold: .12 });
        revealItems.forEach((item) => revealObserver.observe(item));
    } else revealItems.forEach((item) => item.classList.add('is-visible'));
}, 0);

const compass = document.querySelector('.bn-compass');
const compassToggle = document.querySelector('[data-nav-toggle]');
const navLinks = [...document.querySelectorAll('.bn-compass > div a:not([href="#top"])')];
compassToggle?.addEventListener('click', (event) => {
    event.stopPropagation();
    const open = compass.classList.toggle('is-open');
    compassToggle.setAttribute('aria-expanded', String(open));
});
document.querySelectorAll('.bn-compass a').forEach((link) => link.addEventListener('click', () => {
    compass.classList.remove('is-open');
    compassToggle.setAttribute('aria-expanded', 'false');
}));
document.addEventListener('click', (event) => {
    if (compass && compass.classList.contains('is-open') && !compass.contains(event.target)) {
        compass.classList.remove('is-open');
        compassToggle?.setAttribute('aria-expanded', 'false');
    }
});
addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    if (lightbox?.open) lightbox.close();
    compass?.classList.remove('is-open');
    compassToggle?.setAttribute('aria-expanded', 'false');
});
if ('IntersectionObserver' in window && navLinks.length) {
    const navObserver = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        navLinks.forEach((link) => {
            const active = link.hash === `#${entry.target.id}`;
            link.classList.toggle('is-active', active);
            if (active) link.setAttribute('aria-current', 'location'); else link.removeAttribute('aria-current');
        });
    }), { rootMargin: '-25% 0px -60%' });
    navLinks.forEach((link) => navObserver.observe(document.querySelector(link.hash)));
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