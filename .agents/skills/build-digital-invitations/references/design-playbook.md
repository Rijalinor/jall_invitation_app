# Design Playbook

The visual method for invitation templates, plus the inventory of the seven designs already
shipped. Read this before creating or reshaping a design.

## Contents

1. Creative brief
2. Structural differentiation
3. Design tokens
4. Mobile composition
5. Immersive sections
6. Typography and content
7. Motion and sound
8. Media and cultural care
9. Template inventory

## 1. Creative brief

Write a short direction before coding:

- a concept name
- three mood words
- intended audience and event
- cultural or religious cues to include or avoid
- primary visual metaphor
- palette roles, not just hex values
- heading and body type character
- navigation and reading flow
- motion intensity

When the user gives a reference, extract its principles — never copy protected artwork or
produce a near-duplicate. When none is given, propose a distinct direction for the event and
record the assumption.

## 2. Structural differentiation

A new design should differ from nearby templates in at least **three** of: hero composition,
navigation model, section order or reading flow, section framing and transitions,
typographic hierarchy, photo crop or gallery behaviour, ornament/illustration system,
background construction, motion language, opening interaction. Changing only colours, font
names, border radius, or icons does not create a distinct template.

Avoid generic AI styling unless the brief asks for it: excessive gradients, floating glass
cards, uniform centred sections, identical rounded rectangles, random sparkles, and motion
on every element.

## 3. Design tokens

Define template-local CSS custom properties under the template root, then map admin-editable
settings to allow-listed token values only:

```css
.template-root {
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
  --motion-fast: 140ms;
  --motion-base: 320ms;
  --motion-slow: 900ms;
}
```

This is a shape, not a mandatory palette. Add or remove tokens to suit the concept. Derive
decorative colours from the accent setting so a couple's chosen colour repaints every
ornament, rather than hardcoding them.

## 4. Mobile composition

The phone is the canvas; desktop is the enhancement. Compose at 360–390px first, judge it
there, then widen. A desktop-only view is not evidence that a template works.

- Design at 360–390px, enhance at ~430px, tablet, and desktop. Check the shortest common
  phone height as well as the tallest, and re-check after every change.
- Treat vertical space as scarce. Compose each section to read in about one thumb-scroll;
  never leave a large blank area just to fill a full-height section.
- Keep the couple side by side on phones and keep names/headings on whole words — no
  one-word-per-line stacks and no narrow columns squeezing names, addresses, or venues.
- Make every action a comfortable touch target (~44px), reachable one-handed and clear of
  browser chrome and safe-area insets.
- Prefer full-width stacked blocks and one readable column; move secondary detail into
  cards, accordions, or dialogs instead of shrinking type.
- Reflow, do not crop: choose aspect ratios and focal points that survive a phone portrait
  crop, and verify long Indonesian names, honorifics, and addresses at the narrowest width.
- Give a shared form a real width (for example `width: min(100%, 40rem)`); a form centred
  with `margin: auto` inside a `display: grid` section collapses to its widest label. Give
  fields a visible border and focus ring.
- Keep a header or navigation on one line: let it scroll horizontally rather than wrap to a
  second row, which reads as broken on a phone.
- Use the template's own breakpoint consistently (for example `48rem`) and verify both sides
  of it.

## 5. Immersive sections

For premium templates, prefer an immersive phone-first reading model unless the brief says
otherwise:

- Let each major section occupy one viewport, but keep the page scroll normal and free. Do
  not force vertical scroll snapping or one-screen jumps; reserve `scroll-behavior: smooth`
  for anchor links. Horizontal snapping inside a gallery carousel is fine.
- Fill the viewport with composed content or useful interaction — never blank space.
- Reduce prose before shrinking readable text; move detail into compact cards or actions.
- For couple sections keep the two people visually side by side on phones, using asymmetric
  portraits, arched/oval masks, layered borders, or offset frames rather than plain squares.
- For event sections prioritise date, time, venue, address, and actions with dividers, map,
  or grouped controls. For story sections use a vertical flow on mobile when carousels crop.
  For galleries use an editorial mosaic or swipe pages that keep the big/small rhythm.
- Keep floating controls (especially music) icon-first and compact, with `aria-label` or
  visually hidden text rather than a label that wraps inside a circular button.
- A fixed bottom navigation/dock overlaps the lower edge of a full-height section. Reserve
  bottom padding equal to its height plus its offset, and let it step aside while the guest
  scrolls forward so it never covers the actions it floats above.
- Support an optional cover video with desktop/mobile sources, a poster fallback, focal
  position, overlay opacity, and a reduced-motion fallback. Start audio only from a gesture;
  video may autoplay muted when permitted.

## 6. Typography and content

- Use display type for emotional hierarchy and a highly readable body face for detail; limit
  families and weights.
- Size a heading to the box it sits in, not to the page. A title inside a narrow cover card
  must be capped (a smaller `clamp`) and allowed to wrap (`overflow-wrap: anywhere`), so a
  long pair of names never spills outside the panel on desktop.
- Provide font-loading fallbacks and avoid invisible text while fonts load.
- Test long Indonesian names, honorifics, venue names, and addresses.
- Preserve semantic heading order even when the visual order is unconventional.
- Keep date, time, venue, and navigation actions visually unambiguous.
- Prefer content centred within invitation sections, matching the overall centred
  composition; let sibling blocks under a form share that form's column width.

## 7. Motion and sound

### Name the concept

Motion belongs to the brief, not a finishing pass. State the movement in one sentence and
hold every effect to it. If it cannot be said in one sentence, there is no motion design yet.
Examples: page turn / seal breaking (letter), pop-up layers (storybook), slow reveal (cinematic),
petal drift (botanical), typeset reveal (editorial/ledger), light sweep (luminous), fireflies
(nocturnal).

### Spend the budget on one signature moment

Give each template exactly **one** memorable motion moment, normally the cover-to-content
transition; keep everything around it quiet. Motion on every element is the clearest sign of
a generated template. If a section needs no motion, give it none.

### Give scrolling its own highlight

A template has two kinds of motion and needs **both**:

- **Entrance reveals** — a section or element animates once as it scrolls into view. This is
  where the one signature moment lives.
- **A scroll-linked highlight** — one effect that responds to the scroll *position*
  continuously, so the guest feels the page move under their thumb. Without it a long
  invitation scrolls like a plain document however good the entrance reveals are — the guest
  gets bored and stops reading.

Pick one that fits the concept and name it:

| Concept | Scroll-linked highlight |
|---|---|
| Botanical, dusk, night, lanterns | A drifting ambient layer behind the sections, at two or more parallax depths |
| Deco, geometric, editorial | A rule or spine that draws with page progress; a slowly turning sunburst |
| Cinematic, ledger | A progress bar or film-strip scrubber; bars that tighten |
| Storybook, scrapbook, paper | Pages that tilt/settle in; stickers that arrive as you scroll |
| Seaside, airy, daylight | A horizon that rises; waves or light that recede with the scroll |
| Travel, journey, itinerary | A route that draws down the page; a compass needle that answers the scroll |

Rules:

- Drive it from **one** rAF-throttled, passive scroll listener that writes CSS custom
  properties (`--<prefix>-scroll` in pixels, `--<prefix>-progress` from 0 to 1) and let CSS
  read them. Never a per-frame handler that reads layout, and never several listeners.
- **Always layer it on both sides of the content.** A slow layer behind the sections plus a
  faster, softly blurred layer in front gives real 3D depth; a scroll effect that only sits
  behind the content is half the effect. Keep every layer `pointer-events: none` and
  `aria-hidden`, and keep the near layer light enough that text stays readable.
- Add **one detail that animates on its own** — a marching stitched border, drifting specks, a
  slow shimmer — so the page never reads as static even before the guest scrolls. Keep it
  subtle, `pointer-events: none`, `aria-hidden`, and stop it under `motion: off` and
  `prefers-reduced-motion`.
- **Loop ambient particles across the whole viewport.** A decorative layer that only drifts
  with `scrollY` gets pushed off the page once the guest scrolls far, so it vanishes from the
  lower sections. Give leaves/specks/lanterns a self-paced loop that runs them from one edge
  to the other (fall or rise) on a stagger, and let scroll add only a small, bounded drift for
  depth. Whatever the scroll position, the layer is always present top to bottom.
- Animate `transform` and `opacity` only; keep it cheap on phones (no large blurred or
  shadowed layers driven every frame).
- It is decoration, never structure: it must not move body copy while it is read, block
  navigation or forms, or hijack the scroll.
- Remove it entirely under `motion: off` and `prefers-reduced-motion`, so the no-motion path
  still reads as a finished page, not a broken one.

### Reveal by role, not habit

- Vary treatment by element: headings reveal by line, media by scale/clip, lists by stagger,
  cards by offset from their own edge.
- Stagger siblings inside one group; never stagger unrelated sections. Reveal once, then stop
  observing. Keep travel short — 1–2rem reads as arrival, 4rem reads as a slideshow.

### Time it deliberately

Use tokens (`--motion-fast` for press/hover, `--motion-base` for component entry,
`--motion-slow` for the signature moment). Duration scales with distance and weight, and exits
are faster than entrances. A reveal longer than ~1.2s delays reading.

### Never fight the reader

- Animate `transform` and `opacity` only; anything that changes layout janks.
- Do not move body copy while it is read, and never drift text horizontally.
- Do not delay the first meaningful content behind more than one interaction, and never let
  motion block navigation, forms, or the music control.
- Scroll effects must never trap scrolling or hijack the wheel.
- Drive scroll effects from `IntersectionObserver` or one throttled listener — never a
  per-frame `scroll` handler that reads layout.

### Honour the motion setting

Every manifest declares `motion` (`calm`, `expressive`, `off`) as a contract:

- `calm` and `expressive` must differ in kind, not merely in speed.
- `off` renders the complete invitation with no reveal and no scroll effects.
- Honour `prefers-reduced-motion` independently: it produces the `off` experience even when
  the template is configured `expressive`.

### Never hide content behind JavaScript

Content may only be hidden by CSS when JavaScript has marked the document as animation
capable:

```js
document.documentElement.classList.add('js-ready'); // added by the template script
```

```css
.template-root.js-ready .observable { opacity: 0; }
.template-root.js-ready .observable.is-visible { opacity: 1; }
```

Hiding based on a server-rendered attribute means a slow or failed script leaves a blank
invitation. With scripting unavailable the whole invitation must already be visible.

### Sound

Start audio only after the guest opens the invitation or presses play; keep controls visible
and keyboard accessible; never restart audio on re-render and remember the last choice.

## 8. Media and cultural care

- Use user-provided or properly licensed media and fonts.
- Provide meaningful alt text for informative images; mark decorative art as such.
- Do not invent religious quotations, family names, titles, dates, or ceremonial details.
- Make ornamental and cultural elements appropriate to the requested context.
- Crop focal subjects safely across phone aspect ratios; avoid embedding full-resolution
  originals when derivatives exist.

## 9. Template inventory

Thirteen designs ship today. Use the right one as the benchmark for the quality you are
judging, and check a new design against at least one.

| id | concept | event types | motion | use as benchmark for |
|---|---|---|---|---|
| `elegant-rose` | Rose & deep maroon, arched portraits, numbered event cards, pill nav | wedding, engagement | calm | The golden standard: spacious card layout, section rhythm, mobile padding, responsive type |
| `coastal-vow` | Airy seaside "Tide Lines", horizon hero, bottom dock that steps aside, swipe gallery | wedding, engagement | calm | Light editorial layout, warm palette built from CSS layers, dock navigation |
| `celestial-vow` | Night sky "the sky writes your names", self-drawing constellation cover, star-dot nav, parallax | wedding, engagement | expressive | Immersive cover moment, parallax depth, vertical grid gallery; stars, twinkle and shooting stars sit behind the content only |
| `midnight-ledger` | Art Nouveau "Sulur": organic gold vines on cream, arched frames | wedding, engagement | calm | Organic vine curves, arched framing, scroll-drawn gold linework |
| `mahligai` | Islamic celebration, emerald and antique gold | wedding, engagement | calm | Cultural/religious tone, gentle auto-scroll reading |
| `sari-pura` | Indonesian terracotta and pandan, warm batik-tinged palette | wedding, engagement | calm | Warm regional palette and understated ornament |
| `fun-storybook` | Playful comic/storybook, bright accent + background colours | wedding, engagement, birthday | expressive | Bold brand colour, playful illustration, high-energy motion |
| `lentera-senja` | Dusk lanterns: warm night, rising lanterns, reveal-on-scroll | wedding, engagement | expressive | Ambient night scene, glowing accent, gentle parallax |
| `deco-gala` | Art Deco ivory, jet and gold, Poiret One, sunburst turning on scroll | wedding, engagement | calm | Gold rules drawing on section entry, geometric spine progress |
| `aquarelle` | Watercolour pastels on ivory, EB Garamond + Nunito Sans | wedding, engagement | calm | Soft washes, blur-to-focus "bloom" reveals, colour that flows with progress |
| `origami` | Folded paper craft, cream + teal, Fredoka | wedding, engagement | calm | 3D fold reveals, marching stitched border, folded planes behind and in front |
| `jejak-perjalanan` | Travel journal / boarding pass: cream, navy and coral | wedding, engagement, general | calm | Route that draws on scroll, compass needle tied to progress, postcards drifting behind the content (this one keeps its ambient layer behind only) |
| `enso` | Japanese zen: washi paper, sumi ink, vermilion, an ensō and sakura | wedding, engagement, general | calm | A one-stroke ensō drawing itself as the signature moment, ink washes behind and petals in front, very generous whitespace (ma) |

Keep designs distinct from each other even while borrowing a benchmark's quality bar; the
habit of matching a defect fix across templates does not mean flattening every design into
one look.
