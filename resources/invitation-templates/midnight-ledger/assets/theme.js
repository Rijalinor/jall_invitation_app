const root = document.body;
const content = document.querySelector('#top');
const music = document.querySelector('[data-music]');
const musicBtn = document.querySelector('[data-music-toggle]');
const progress = document.querySelector('[data-scroll-progress]');
const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)').matches || root.dataset.motion === 'off';

document.querySelectorAll('[data-cover-video]').forEach((video) => {
    if (reducedMotion) {
        video.remove();
        return;
    }
    video.play().catch(() => {});
    video.addEventListener('error', () => video.remove(), { once: true });
});

document.querySelector('[data-open-invitation]')?.addEventListener('click', async () => {
    root.classList.add('invitation-open');
    content?.removeAttribute('inert');
    content?.focus();
    if (music) await music.play().catch(() => {});
    if (musicBtn) updateMusicBtn();
});

function updateMusicBtn() {
    if (!musicBtn) return;
    const playing = music && !music.paused;
    musicBtn.querySelector('span').textContent = playing ? '♪' : '♩';
    musicBtn.setAttribute('aria-label', playing ? 'Jeda musik' : 'Putar musik');
}

musicBtn?.addEventListener('click', async () => {
    if (music.paused) await music.play().catch(() => {});
    else music.pause();
    updateMusicBtn();
});

document.querySelectorAll('[data-copy]').forEach((btn) => btn.addEventListener('click', async () => {
    const label = btn.textContent;
    await navigator.clipboard.writeText(btn.dataset.copy).catch(() => {});
    btn.textContent = '✓';
    setTimeout(() => btn.textContent = label, 1800);
}));

document.querySelectorAll('[data-share]').forEach((button) => button.addEventListener('click', async (e) => {
    const url = e.currentTarget.dataset.shareUrl;
    if (navigator.share) await navigator.share({ title: document.title, url }).catch(() => {});
    else await navigator.clipboard.writeText(url).catch(() => {});
}));

document.querySelector('[data-quick-actions-toggle]')?.addEventListener('click', (e) => {
    const button = e.currentTarget;
    const panel = document.getElementById(button.getAttribute('aria-controls'));
    const open = button.getAttribute('aria-expanded') !== 'true';
    button.setAttribute('aria-expanded', String(open));
    button.setAttribute('aria-label', open ? 'Tutup aksi undangan' : 'Buka aksi undangan');
    button.closest('.ml-quick-actions')?.classList.toggle('is-open', open);
    if (panel) panel.hidden = !open;
});

const lightbox = document.querySelector('[data-lightbox]');
if (lightbox) {
    document.querySelectorAll('[data-lightbox-src]').forEach((btn) => btn.addEventListener('click', () => {
        const img = lightbox.querySelector('[data-lightbox-image]');
        img.src = btn.dataset.lightboxSrc;
        img.alt = btn.dataset.lightboxAlt;
        lightbox.showModal();
    }));
    lightbox.querySelector('[data-lightbox-close]')?.addEventListener('click', () => lightbox.close());
    lightbox.addEventListener('click', (e) => { if (e.target === lightbox) lightbox.close(); });
}

const revealItems = document.querySelectorAll('[data-reveal], .ml-hosts article, .ml-story li, .ml-gallery figure');
if ('IntersectionObserver' in window && !reducedMotion) {
    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
    }), { threshold: .12 });
    revealItems.forEach((el) => { el.dataset.reveal = ''; observer.observe(el); });
} else {
    revealItems.forEach((el) => el.classList.add('is-visible'));
}

if (progress) addEventListener('scroll', () => {
    const range = document.documentElement.scrollHeight - innerHeight;
    progress.style.transform = `scaleY(${range > 0 ? scrollY / range : 0})`;
}, { passive: true });

const mobileLinks = [...document.querySelectorAll('.ml-mobile-nav a')];
if ('IntersectionObserver' in window && mobileLinks.length) {
    const targets = mobileLinks.map((a) => document.querySelector(a.hash)).filter(Boolean);
    const navObserver = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        mobileLinks.forEach((link) => {
            const active = link.hash === `#${entry.target.id}`;
            link.classList.toggle('is-active', active);
            link.toggleAttribute('aria-current', active);
        });
    }), { rootMargin: '-25% 0px -60%' });
    targets.forEach((el) => navObserver.observe(el));
}

document.querySelectorAll('[data-countdown]').forEach((el) => {
    const units = Object.fromEntries(
        [...el.querySelectorAll('[data-countdown-unit]')].map((u) => [u.dataset.countdownUnit, u])
    );
    const target = new Date(el.dataset.countdown);
    let timer;
    const update = () => {
        const remaining = Math.max(0, Math.floor((target - new Date()) / 1000));
        units.days.textContent = String(Math.floor(remaining / 86400)).padStart(2, '0');
        units.hours.textContent = String(Math.floor(remaining % 86400 / 3600)).padStart(2, '0');
        units.minutes.textContent = String(Math.floor(remaining % 3600 / 60)).padStart(2, '0');
        units.seconds.textContent = String(remaining % 60).padStart(2, '0');
        if (remaining <= 0) clearInterval(timer);
    };
    update();
    if (target > new Date()) timer = setInterval(update, 1000);
});
