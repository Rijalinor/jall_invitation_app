const root = document.body;
const music = document.querySelector('[data-music]');
const musicToggle = document.querySelector('[data-music-toggle]');
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const animated = root.dataset.motion !== 'off' && !reducedMotion;

/* Content is only hidden once this script can reveal it again, so a slow,
   blocked, or failed script never leaves a blank page. */
if (animated && 'IntersectionObserver' in window) {
    root.classList.add('pl-armed');
}

/* Cover video — only when motion is welcome */

document.querySelectorAll('[data-cover-video]').forEach((video) => {
    if (reducedMotion) {
        video.remove();
        return;
    }
    video.play().catch(() => {});
    video.addEventListener('error', () => video.remove(), { once: true });
});

/* Section reveals start once the cover lifts, so the opening gets its entrance */

let revealsStarted = false;
const startReveals = () => {
    if (revealsStarted || !animated) return;
    revealsStarted = true;
    const items = document.querySelectorAll('[data-reveal]');
    if (!('IntersectionObserver' in window)) {
        items.forEach((item) => item.classList.add('is-visible'));
        return;
    }
    const observer = new IntersectionObserver((entries) => {
        entries.filter((entry) => entry.isIntersecting).forEach((entry, index) => {
            entry.target.style.transitionDelay = `${Math.min(index, 4) * 90}ms`;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -6% 0px' });
    items.forEach((item) => observer.observe(item));
};

document.querySelector('[data-open-invitation]')?.addEventListener('click', async () => {
    root.classList.add('invitation-open');
    ['#top', '.pl-rail', '.pl-mobile-nav'].forEach((selector) => document.querySelector(selector)?.removeAttribute('inert'));
    document.querySelector('#top')?.focus({ preventScroll: true });
    startReveals();
    if (music) await music.play().catch(() => {});
});

/* Music — state follows the audio element so autoplay and toggle stay in sync */

const syncMusicState = () => {
    if (!musicToggle) return;
    const playing = Boolean(music) && !music.paused;
    musicToggle.setAttribute('aria-pressed', String(playing));
    musicToggle.setAttribute('aria-label', playing ? 'Jeda musik' : 'Putar musik');
};

if (music) {
    music.addEventListener('play', syncMusicState);
    music.addEventListener('pause', syncMusicState);
    syncMusicState();
}

musicToggle?.addEventListener('click', async () => {
    if (!music) return;
    if (music.paused) await music.play().catch(() => {});
    else music.pause();
    syncMusicState();
});

/* Countdown */

document.querySelectorAll('[data-countdown]').forEach((element) => {
    const output = element.querySelector('[data-countdown-output]');
    if (!output) return;
    const update = () => {
        const seconds = Math.floor((new Date(element.dataset.countdown) - new Date()) / 1000);
        if (seconds <= 0) {
            output.classList.add('is-done');
            output.textContent = 'Hari bahagia telah tiba';
            return false;
        }
        const values = {
            days: Math.floor(seconds / 86400),
            hours: Math.floor((seconds % 86400) / 3600),
            minutes: Math.floor((seconds % 3600) / 60),
            seconds: seconds % 60,
        };
        Object.entries(values).forEach(([unit, value]) => {
            const target = output.querySelector(`[data-countdown-unit="${unit}"]`);
            if (target) target.textContent = String(value).padStart(2, '0');
        });
        return true;
    };
    if (update() !== false) {
        const timer = setInterval(() => {
            if (update() === false) clearInterval(timer);
        }, 1000);
    }
});

/* Clipboard — only claim success when text was really copied */

const copyText = async (text) => {
    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch {
            /* fall through to the legacy path */
        }
    }
    try {
        const field = document.createElement('textarea');
        field.value = text;
        field.setAttribute('readonly', '');
        field.style.position = 'fixed';
        field.style.top = '-1000px';
        field.style.opacity = '0';
        document.body.appendChild(field);
        field.select();
        const copied = document.execCommand('copy');
        field.remove();
        return copied;
    } catch {
        return false;
    }
};

document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
    const label = button.textContent;
    if (!await copyText(button.dataset.copy)) return;
    button.textContent = 'Tersalin';
    setTimeout(() => {
        button.textContent = label;
    }, 1500);
}));

document.querySelectorAll('[data-share]').forEach((button) => button.addEventListener('click', async () => {
    const url = button.dataset.shareUrl;
    if (navigator.share) {
        await navigator.share({ title: document.title, url }).catch(() => {});
        return;
    }
    const label = button.querySelector('[data-share-label]');
    if (await copyText(url) && label) {
        const original = label.textContent;
        label.textContent = 'Tersalin';
        setTimeout(() => {
            label.textContent = original;
        }, 1500);
    }
}));

/* Lightbox — lock the page behind it without losing the guest's scroll position */

const lightbox = document.querySelector('[data-lightbox]');
let lightboxScroll = 0;

document.querySelectorAll('[data-lightbox-src]').forEach((button) => button.addEventListener('click', () => {
    if (!lightbox) return;
    const image = lightbox.querySelector('[data-lightbox-image]');
    image.src = button.dataset.lightboxSrc;
    image.alt = button.dataset.lightboxAlt;
    lightboxScroll = window.scrollY;
    lightbox.showModal();
    root.classList.add('is-lightbox-open');
}));

document.querySelector('[data-lightbox-close]')?.addEventListener('click', () => lightbox?.close());
lightbox?.addEventListener('click', (event) => {
    if (event.target === lightbox) lightbox.close();
});
lightbox?.addEventListener('close', () => {
    root.classList.remove('is-lightbox-open');
    window.scrollTo({ top: lightboxScroll, behavior: 'instant' });
});
