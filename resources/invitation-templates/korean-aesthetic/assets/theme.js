document.addEventListener('DOMContentLoaded', () => {
    // Open Invitation Handling
    const cover = document.getElementById('opening-cover');
    const mainContent = document.getElementById('invitation-content');
    const openBtn = document.querySelector('[data-open-invitation]');
    const audio = document.querySelector('[data-music]');
    const musicBtn = document.querySelector('[data-music-toggle]');

    if (openBtn && cover && mainContent) {
        openBtn.addEventListener('click', () => {
            document.body.classList.add('invitation-open');
            mainContent.removeAttribute('inert');

            if (audio) {
                audio.play().catch(() => {});
            }
        });
    }

    // Music Control
    if (musicBtn && audio) {
        musicBtn.addEventListener('click', () => {
            if (audio.paused) {
                audio.play();
                musicBtn.classList.remove('is-muted');
            } else {
                audio.pause();
                musicBtn.classList.add('is-muted');
            }
        });
    }

    // Copy to Clipboard Buttons
    document.querySelectorAll('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const text = btn.getAttribute('data-copy');
            if (!text) return;

            try {
                await navigator.clipboard.writeText(text);
                const original = btn.textContent;
                btn.textContent = '✓ Copied!';
                setTimeout(() => {
                    btn.textContent = original;
                }, 2000);
            } catch (err) {
                console.error('Failed to copy', err);
            }
        });
    });

    // Countdown Timer
    const countdownEl = document.querySelector('[data-countdown]');
    if (countdownEl) {
        const timestamp = parseInt(countdownEl.getAttribute('data-countdown'), 10) * 1000;
        if (!isNaN(timestamp)) {
            const updateCountdown = () => {
                const now = new Date().getTime();
                const diff = timestamp - now;

                if (diff <= 0) {
                    return;
                }

                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                const setUnit = (unit, val) => {
                    const el = countdownEl.querySelector(`[data-countdown-unit="${unit}"]`);
                    if (el) el.textContent = String(val).padStart(2, '0');
                };

                setUnit('days', days);
                setUnit('hours', hours);
                setUnit('minutes', minutes);
                setUnit('seconds', seconds);
            };

            updateCountdown();
            setInterval(updateCountdown, 1000);
        }
    }

    // Lightbox Gallery
    const lightbox = document.querySelector('[data-lightbox]');
    const lightboxImg = document.querySelector('[data-lightbox-image]');
    const lightboxClose = document.querySelector('[data-lightbox-close]');

    if (lightbox && lightboxImg) {
        document.querySelectorAll('[data-lightbox-src]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const src = btn.getAttribute('data-lightbox-src');
                const alt = btn.getAttribute('data-lightbox-alt') || '';
                lightboxImg.src = src;
                lightboxImg.alt = alt;
                lightbox.showModal();
            });
        });

        if (lightboxClose) {
            lightboxClose.addEventListener('click', () => {
                lightbox.close();
            });
        }

        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                lightbox.close();
            }
        });
    }

    // Native Web Share API
    const shareBtn = document.querySelector('[data-share]');
    if (shareBtn) {
        shareBtn.addEventListener('click', async () => {
            const url = shareBtn.getAttribute('data-share-url') || window.location.href;
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: document.title,
                        url: url,
                    });
                } catch (e) {}
            } else {
                navigator.clipboard.writeText(url).then(() => {
                    alert('Link undangan berhasil disalin!');
                });
            }
        });
    }
});
