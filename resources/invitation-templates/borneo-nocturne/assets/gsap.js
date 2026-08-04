import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin(ScrollTrigger, SplitText);

window.__bnGsapLoaded = true;

const root = document.body;
const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
const motion = root.dataset.motion;
const canAnimate = motion !== 'off' && !reduced;
const expressive = motion === 'expressive' && !reduced;

if (!canAnimate) {
    document.querySelectorAll('.bn-observe').forEach((el) => el.classList.add('is-visible'));
    root.classList.add('bn-cover-live');
} else {
    root.classList.add('bn-gsap');
    requestAnimationFrame(() => {
        initialize().catch(() => {});
    });
}

async function initialize() {
    coverIntro();

    try {
        const fontsReady = document.fonts?.ready ? Promise.race([document.fonts.ready, new Promise((resolve) => setTimeout(resolve, 300))]) : Promise.resolve();
        await fontsReady;
    } catch (error) {}

    gsap.utils.toArray('.bn-cover__scene i, .bn-hero__scene i, .bn-global-fireflies i').forEach((fly) => {
        gsap.to(fly, {
            x: gsap.utils.random(-120, 120),
            y: gsap.utils.random(-100, 60),
            opacity: gsap.utils.random(.2, .95),
            scale: gsap.utils.random(.5, 1.3),
            duration: gsap.utils.random(3.5, 6.5),
            ease: 'sine.inOut',
            repeat: -1,
            yoyo: true,
            delay: gsap.utils.random(0, 2.5),
        });
    });

    if (expressive) {
        initRiver();
        initCountdownFlip();
        initMagnetic();
        initParallax();
    }

    document.querySelectorAll('.bn-heading').forEach((heading) => {
        const eyebrow = heading.querySelector('.bn-eyebrow');
        const title = heading.querySelector('h2, h3');
        gsap.set(heading, { autoAlpha: 0 });
        ScrollTrigger.create({
            trigger: heading,
            start: 'top 84%',
            once: true,
            onEnter: () => {
                gsap.to(heading, { autoAlpha: 1, duration: .4 });
                if (eyebrow) gsap.fromTo(eyebrow, { autoAlpha: 0, y: 8 }, { autoAlpha: 1, y: 0, duration: .6, delay: .05 });
                if (title) {
                    let split = null;
                    try { split = new SplitText(title, { type: 'lines' }); } catch (error) {}
                    if (split) gsap.fromTo(split.lines, { yPercent: 105, autoAlpha: 0 }, { yPercent: 0, autoAlpha: 1, stagger: .07, duration: .9, delay: .12, ease: 'power3.out' });
                    else gsap.fromTo(title, { autoAlpha: 0, y: 24 }, { autoAlpha: 1, y: 0, duration: .7, delay: .12 });
                }
            },
        });
    });

    revealGroup('#couple .bn-couple__grid > article', 'x');
    revealGroup('.bn-story ol > li', 'x');
    revealGroup('.bn-events__grid > article', 'y');
    revealGroup('.bn-gallery__mosaic figure', 'zoom');

    const dedicated = gsap.utils.toArray('#couple .bn-couple__grid > article, .bn-story ol > li, .bn-events__grid > article, .bn-gallery__mosaic figure');
    const generic = gsap.utils.toArray('.bn-observe').filter((el) => !el.classList.contains('bn-heading') && !el.closest('.bn-hero') && !dedicated.includes(el));
    revealGroup(generic, 'fade');

    const ringFill = document.querySelector('.bn-compass__ring-fill');
    if (ringFill) {
        gsap.fromTo(ringFill, { strokeDashoffset: 100 }, {
            strokeDashoffset: 0,
            ease: 'none',
            scrollTrigger: { start: 0, end: 'max', scrub: .4 },
        });
    }

    addEventListener('load', () => ScrollTrigger.refresh());
}

function coverIntro() {
    const tl = gsap.timeline({ delay: .2 });
    tl.fromTo('.bn-cover header', { autoAlpha: 0, y: -14 }, { autoAlpha: 1, y: 0, duration: .7, ease: 'power3.out' }, 0);
    document.querySelectorAll('.bn-cover [data-split]').forEach((el) => {
        gsap.set(el, { autoAlpha: 1 });
        let split = null;
        try { split = new SplitText(el, { type: 'lines' }); } catch (error) {}
        const targets = split ? split.lines : [el];
        tl.fromTo(targets, { yPercent: 120, autoAlpha: 0 }, { yPercent: 0, autoAlpha: 1, stagger: .08, duration: .95, ease: 'power3.out' }, '<.08');
    });
    tl.fromTo('.bn-cover__line', { scaleX: 0 }, { scaleX: 1, duration: .9, ease: 'power3.inOut' }, '-=.7');
    tl.fromTo('.bn-cover > button', { autoAlpha: 0, y: 16 }, { autoAlpha: 1, y: 0, duration: .7, ease: 'power3.out' }, '-=.45');
}

function heroIntro() {
    const hero = document.querySelector('[data-hero]');
    if (!hero) return;
    const tl = gsap.timeline({ delay: .05 });
    const title = hero.querySelector('h2[data-split]');
    gsap.set(title, { autoAlpha: 1 });
    let split = null;
    try { split = new SplitText(title, { type: 'lines' }); } catch (error) {}
    if (split) tl.fromTo(split.lines, { yPercent: 112, autoAlpha: 0 }, { yPercent: 0, autoAlpha: 1, stagger: .07, duration: 1, ease: 'power3.out' });
    else tl.fromTo(title, { autoAlpha: 0, y: 30 }, { autoAlpha: 1, y: 0, duration: .9, ease: 'power3.out' });
    tl.fromTo(hero.querySelectorAll('.bn-eyebrow, .bn-hero__splash, .bn-hero__lead, .bn-scroll-cue'), { autoAlpha: 0, y: 18 }, { autoAlpha: 1, y: 0, stagger: .1, duration: .8, ease: 'power3.out' }, '-=.5');
    const copy = hero.querySelector('.bn-hero__copy');
    if (copy) tl.fromTo(copy, { autoAlpha: 0, y: 24 }, { autoAlpha: 1, y: 0, duration: .9 }, '-=.45');
}

function revealGroup(selector, mode) {
    const els = typeof selector === 'string' ? gsap.utils.toArray(selector) : selector;
    if (!els.length) return;
    els.forEach((el, index) => {
        gsap.set(el, {
            autoAlpha: 0,
            y: mode === 'x' ? 26 : mode === 'zoom' ? 44 : 40,
            x: mode === 'x' && index % 2 ? 36 : 0,
            scale: mode === 'zoom' ? .94 : 1,
        });
    });
    ScrollTrigger.batch(els, {
        start: 'top 90%',
        once: true,
        onEnter: (batch) => batch.forEach((el) => {
            gsap.to(el, { autoAlpha: 1, y: 0, x: 0, scale: 1, duration: 1, ease: 'power3.out' });
            if (mode === 'zoom') {
                const img = el.querySelector('img');
                if (img) gsap.fromTo(img, { scale: .88 }, { scale: 1, duration: 1.5, ease: 'power2.out', delay: .1 });
            }
            const subTitle = el.querySelector('h3[data-split], h2[data-split]');
            if (subTitle) {
                try {
                    const split = new SplitText(subTitle, { type: 'lines' });
                    gsap.fromTo(split.lines, { yPercent: 80, autoAlpha: 0 }, { yPercent: 0, autoAlpha: 1, stagger: .05, duration: .7, delay: .15, ease: 'power3.out' });
                } catch (error) {}
            }
        }),
    });
}

function initRiver() {
    const section = document.querySelector('.bn-story');
    const svg = section?.querySelector('.bn-river-path');
    const path = section?.querySelector('.bn-river-path__fade');
    if (!section || !svg || !path || typeof path.getTotalLength !== 'function') return;

    const total = path.getTotalLength();
    const draw = section.querySelector('.bn-river-path__draw');
    if (draw) gsap.to(draw, { strokeDashoffset: -160, ease: 'none', repeat: -1, duration: 6 });

    const boat = section.querySelector('.bn-boat');
    if (boat && total) {
        gsap.set(boat, { autoAlpha: 1 });
        ScrollTrigger.create({
            trigger: section,
            start: 'top 70%',
            end: 'bottom 60%',
            scrub: .5,
            onUpdate: (self) => {
                const point = path.getPointAtLength(self.progress * total);
                const vr = svg.getBoundingClientRect();
                const sr = section.getBoundingClientRect();
                const x = vr.left + (point.x / 400) * vr.width;
                const y = vr.top + (point.y / 1000) * vr.height;
                boat.style.transform = `translate(${x - sr.left}px, ${y - sr.top}px)`;
            },
        });
    }
}

function initCountdownFlip() {
    const output = document.querySelector('[data-countdown-output]');
    if (!output) return;
    const previous = new Map();
    output.querySelectorAll('[data-countdown-unit]').forEach((unit) => previous.set(unit, unit.textContent));
    new MutationObserver(() => {
        output.querySelectorAll('[data-countdown-unit]').forEach((unit) => {
            if (previous.get(unit) === unit.textContent) return;
            previous.set(unit, unit.textContent);
            gsap.fromTo(unit, { opacity: .2, y: -8 }, { opacity: 1, y: 0, duration: .5, ease: 'power2.out' });
        });
    }).observe(output, { childList: true, subtree: true, characterData: true });
}

function initMagnetic() {
    if (!matchMedia('(pointer: fine)').matches) return;
    gsap.utils.toArray('.bn-actions a, .bn-actions button, .bn-ribbon a, .bn-compass > button, .bn-cover > button').forEach((btn) => {
        const xTo = gsap.quickTo(btn, 'x', { duration: .45, ease: 'power3' });
        const yTo = gsap.quickTo(btn, 'y', { duration: .45, ease: 'power3' });
        btn.addEventListener('pointermove', (event) => {
            const rect = btn.getBoundingClientRect();
            xTo((event.clientX - (rect.left + rect.width / 2)) * .18);
            yTo((event.clientY - (rect.top + rect.height / 2)) * .28);
        });
        btn.addEventListener('pointerleave', () => { xTo(0); yTo(0); });
    });
}

function initParallax() {
    const mm = gsap.matchMedia();
    mm.add('(min-width: 48rem)', () => {
        const hero = document.querySelector('.bn-hero');
        if (hero) gsap.to('.bn-hero__title', { yPercent: 16, ease: 'none', scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: .5 } });
        const closing = document.querySelector('.bn-closing > div');
        if (closing) gsap.to(closing, { yPercent: -12, ease: 'none', scrollTrigger: { trigger: closing, start: 'top bottom', end: 'bottom top', scrub: .6 } });
        gsap.utils.toArray('#couple .bn-portrait').forEach((portrait) => {
            gsap.fromTo(portrait, { yPercent: -4 }, { yPercent: 4, ease: 'none', scrollTrigger: { trigger: portrait.closest('article'), start: 'top bottom', end: 'bottom top', scrub: .6 } });
        });
    });
}

document.querySelector('[data-open-invitation]')?.addEventListener('click', () => {
    if (!canAnimate) return;
    gsap.to('.bn-cover__content, .bn-cover header, .bn-cover > button', { autoAlpha: 0, y: -26, duration: .5, ease: 'power2.in', overwrite: 'auto' });
    gsap.to('.bn-cover__scene i', { opacity: 0, duration: .8, stagger: .04, ease: 'power2.in' });
    setTimeout(heroIntro, 950);
});

document.querySelectorAll('[data-lightbox-src]').forEach((button) => button.addEventListener('click', () => {
    if (!canAnimate) return;
    const image = document.querySelector('[data-lightbox-image]');
    gsap.fromTo(image, { scale: .92, autoAlpha: 0 }, { scale: 1, autoAlpha: 1, duration: .5, ease: 'power2.out' });
}));