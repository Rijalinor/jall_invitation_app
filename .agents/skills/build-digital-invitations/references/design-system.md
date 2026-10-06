# Invitation Design Protocol

## Contents

1. Creative brief
2. Structural differentiation
3. Design tokens
4. Mobile composition
5. Immersive section templates
6. Typography and content
7. Motion and sound
8. Media and cultural care

## 1. Creative Brief

Write a short direction before coding. Include:

- a concept name
- three mood words
- intended audience and event
- cultural or religious cues to include or avoid
- primary visual metaphor
- palette roles, not just hex values
- heading and body type character
- navigation and reading flow
- motion intensity

Examples of concepts include an editorial love letter, botanical storybook, cinematic night, geometric Islamic celebration, scrapbook journey, tropical daylight, or formal monochrome gallery. Treat examples as prompts, not a fixed catalog.

## 2. Structural Differentiation

A new design should differ from nearby templates in at least three of these dimensions:

- hero composition
- navigation model
- section order or reading flow
- section framing and transitions
- typographic hierarchy
- photograph crop or gallery behavior
- ornament and illustration system
- background construction
- motion language
- opening interaction

Changing only colors, font names, border radius, or decorative icons does not create a distinct template.

Avoid generic AI styling habits unless the brief calls for them: excessive gradients, floating glass cards, uniform centered sections, identical rounded rectangles, random sparkles, and motion on every element.

## 3. Design Tokens

Define template-local CSS custom properties for:

```css
.invitation-theme {
  --color-canvas: #f7f2ea;
  --color-surface: #fffaf3;
  --color-text: #302722;
  --color-muted: #746961;
  --color-accent: #765448;
  --color-on-accent: #ffffff;
  --font-display: "Cormorant Garamond", serif;
  --font-body: "Inter", sans-serif;
  --space-section: clamp(4rem, 14vw, 8rem);
  --content-width: 42rem;
  --radius-control: 999px;
  --shadow-soft: 0 1rem 3rem rgb(48 39 34 / 0.10);
  --motion-fast: 180ms;
  --motion-slow: 700ms;
}
```

This is a shape, not a mandatory palette. Add or remove tokens to suit the concept. Scope them below a template root. Map admin-editable settings only to allow-listed token values.

## 4. Mobile Composition

- Start at 360–390 px widths and enhance for larger screens.
- Keep essential text readable without zooming.
- Give touch targets adequate size and spacing.
- Keep fixed controls clear of browser chrome and safe-area insets.
- Prevent names, addresses, and long guest names from overflowing.
- Avoid desktop artwork merely scaled down; recombine the composition for narrow screens.
- Ensure the closed cover, open invitation, forms, gallery, map action, and music controls all work with one hand.
- A form centred with `margin: auto` inside a `display: grid` section shrinks to its widest label. Give shared forms a definite width such as `width: min(100%, 40rem)`, and give fields a visible border and focus ring against the surface behind them.

## 5. Immersive Section Templates

For premium wedding templates, prefer an immersive phone-first reading model unless the brief asks otherwise:

- Make each major section occupy one full viewport, but keep the page scroll normal and free. Do not force vertical scroll snapping or jump the guest one screen at a time; reserve `scroll-behavior: smooth` for anchor and navigation links only. Horizontal snapping inside a gallery carousel is fine.
- Fill the viewport with composed content, ornamental framing, media, or useful interaction. Do not leave large blank areas merely to satisfy full-screen height.
- Keep section content focused. Reduce prose before shrinking readable text; details can move into compact cards, accordions, or secondary actions.
- Preserve density across device heights. Test short phones, tall phones, and common desktop widths with responsive spacing rather than fixed vertical gaps.
- For couple sections, keep the two people visually side by side on mobile when possible. Use asymmetric portraits, arched or oval masks, layered borders, and offset frames instead of plain square cards.
- Keep names readable. Avoid narrow columns that force important names or headings into one-word-per-line stacks.
- For event sections, prioritize date, time, venue, address, and actions. Remove decorative filler and make the layout feel intentionally full with dividers, maps, timeline marks, or grouped controls.
- For story sections, use a vertical reading flow on mobile when carousel cards create cropped or empty layouts.
- For gallery sections, use an editorial mosaic or horizontal swipe pages that preserve the big/small photo rhythm. Avoid harsh subject cropping; use focal-position controls, `object-fit: cover` only where the composition survives it, and `object-fit: contain` or alternate aspect ratios for photos that must remain fully visible.
- Keep floating controls, especially music, icon-first and compact. Use `aria-label` or visually hidden text instead of visible labels that wrap inside a circular button.
- Support optional cover video with desktop/mobile sources, poster fallback, focal-position settings, overlay opacity, and reduced-motion fallback. Start audio only from a user gesture; video may be muted autoplay when permitted.
- A fixed bottom navigation or dock overlaps the lower edge of a full-height section. Reserve bottom padding equal to its height plus its offset, and let it step aside while the guest scrolls forward so it never covers the actions it floats above.

Use this pattern for designs similar to cinematic botanical gold, editorial vow, formal luxury, or any user request for full-screen sections that read on a normal scroll.

### Elegant Rose Benchmark Guidelines

When building new templates or repairing existing ones, treat **Elegant Rose** (`resources/invitation-templates/elegant-rose`) as the golden standard for structural aesthetics and UX:
1. **Hero & Cover Overlay**: Semi-transparent paper overlay (`.er-cover__paper`), gold/maroon accents, elegant typography (`Playfair Display`/`Cormorant`), clear recipient greeting, and bold CTA button.
2. **Host / Mempelai Cards**: Self-contained host articles (`.er-host`), circular or arched portraits (`.er-host__portrait`), centered role kickers ("Putra/Putri dari"), clean parent info, and styled social media actions. On narrow mobile screens, ensure host cards stack vertically or maintain comfortable padding so text and names never get squished or truncated.
3. **Event Schedule Cards**: Sequenced cards with numeric badges (`01`, `02`), prominent dates and start/end times, structured venue details, and action button groups (Directions, Google Calendar, ICS download, Salin Alamat).
4. **Section Tinting & Rhythm**: Alternate section backgrounds using light tints (`.er-section--tint`) to create visual hierarchy and prevent visual fatigue while scrolling.
5. **Floating Elements**: Pill-style sticky navigation (`.er-nav`) with backdrop blur and smooth anchor jumping, icon-only music toggle (`.er-music`), and accessible lightbox modal for galleries.

## 6. Typography and Content

- Use display type for emotional hierarchy and a highly readable body face for details.
- Limit the number of font families and weights.
- Provide font-loading fallbacks and avoid invisible text while fonts load.
- Test long Indonesian names, honorifics, venue names, and addresses.
- Preserve semantic heading order even when the visual order is unconventional.
- Keep event date, time, venue, and navigation actions visually unambiguous.

## 7. Motion and Sound

### Name the motion concept

Motion belongs to the creative brief, not to a finishing pass. Before writing code, state the movement in one sentence and hold every effect to it.

| Concept | Motion character |
|---|---|
| Letter, stationery | Page turn, fold, seal breaking |
| Storybook, scrapbook | Page turn, pop-up layers, sticker placement |
| Cinematic | Slow reveal, depth of field, long cross-fades |
| Botanical | Gentle parallax, petal drift, growth |
| Editorial, ledger | Crisp cuts, typeset reveal, rule drawing |
| Luminous, atelier | Bloom, light sweep, soft focus |
| Nocturnal, royal | Embers, fireflies, slow drift, glow |

If the concept cannot be stated in one sentence, there is no motion design yet.

### Spend the budget on one signature moment

Give each template exactly **one** memorable motion moment, normally the cover-to-content transition. Everything around it stays quiet.

Motion on every element is the clearest sign of a generated template. When every section fades and slides identically, nothing is emphasised and the guest's eye is never told what matters. If a section needs no motion, give it none.

### Reveal by role, not by habit

- Vary the treatment by what the element is: headings reveal by line, media by scale or clip, lists by stagger, cards by offset from their own edge.
- Stagger siblings inside one group. Never stagger unrelated sections against each other.
- Reveal once, then stop observing. Do not replay on scroll-back unless the concept depends on it.
- Prefer one observer per page with per-element intent over several ad-hoc observers.
- Keep travel short: 1–2rem reads as arrival, 4rem reads as a slideshow.

### Time it deliberately

Define timing as template tokens and use them everywhere:

```css
--motion-fast: 140ms;   /* press, hover, control state */
--motion-base: 320ms;   /* component entry */
--motion-slow: 900ms;   /* signature moment, hero entrance */
--ease-enter: cubic-bezier(.2, .7, .2, 1);
--ease-exit: cubic-bezier(.4, 0, 1, 1);
```

Duration scales with distance and weight, and exits are faster than entrances. A reveal longer than roughly 1.2s delays reading.

### Never fight the reader

- Animate `transform` and `opacity` only. Anything that changes layout janks on mid-range phones.
- Do not move body copy while it is being read, and never drift text horizontally.
- Do not delay the first meaningful content behind more than one interaction.
- Do not let motion block navigation, forms, or the music control.
- Scroll effects must never trap scrolling or hijack the wheel.

### Honour the motion setting

Every manifest declares `motion` (`calm`, `expressive`, `off`). Treat it as a contract:

- `calm` and `expressive` must differ in kind, not merely in speed.
- `off` renders the complete invitation with no reveal and no scroll effects, all content visible immediately.
- Honour `prefers-reduced-motion` independently: a guest who asks their device for less motion gets the `off` experience even when the template is configured `expressive`.

### Never hide content behind JavaScript

**Content may only be hidden by CSS when JavaScript has itself marked the document as animation capable.**

```js
document.documentElement.classList.add('js-ready'); // added by the template script
```

```css
.template-root.js-ready .observable { opacity: 0; }
.template-root.js-ready .observable.is-visible { opacity: 1; }
```

Hiding based on a server-rendered attribute (`[data-motion]`, a template root class) means a slow, blocked, or failed script leaves the guest staring at a blank invitation. With scripting unavailable every section must already be visible and readable. Reveal animations are an enhancement; they are never what makes content appear.

### Sound

- Begin audio only after the visitor opens the invitation or presses play.
- Keep audio controls visible, keyboard accessible, and understandable without relying on an icon alone.
- Never restart audio on re-render, and remember the visitor's last choice.

### Performance

- Animate with CSS or the Web Animations API unless the concept needs a timeline library. If a library is required, load it only on that template and keep it off the critical path.
- Drive scroll effects from `IntersectionObserver`, or one throttled listener. Never a per-frame `scroll` callback that reads layout.
- Avoid animating large blurred layers, shadows, or filters on mobile.

## 8. Media and Cultural Care

- Use user-provided or properly licensed media and fonts.
- Provide meaningful alternative text for informative images; mark purely decorative art accordingly.
- Do not invent religious quotations, family names, titles, dates, or ceremonial details.
- Make ornamental and cultural elements appropriate to the user's requested context.
- Crop focal subjects safely across phone aspect ratios.
- Avoid embedding full-resolution originals when responsive derivatives are available.
