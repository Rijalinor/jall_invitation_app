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

## 5. Immersive Section Templates

For premium wedding templates, prefer an immersive phone-first reading model unless the brief asks otherwise:

- Make each major section occupy one full viewport and use scroll snapping so one deliberate scroll lands on the next section.
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

Use this pattern for designs similar to cinematic botanical gold, editorial vow, formal luxury, or any user request that says each scroll should reveal one full page.

## 6. Typography and Content

- Use display type for emotional hierarchy and a highly readable body face for details.
- Limit the number of font families and weights.
- Provide font-loading fallbacks and avoid invisible text while fonts load.
- Test long Indonesian names, honorifics, venue names, and addresses.
- Preserve semantic heading order even when the visual order is unconventional.
- Keep event date, time, venue, and navigation actions visually unambiguous.

## 7. Motion and Sound

- Tie motion to the concept: page turns for a letter, slow reveal for cinematic work, gentle parallax for botanical depth, or crisp cuts for editorial layouts.
- Animate opacity and transforms preferentially; avoid layout-thrashing effects.
- Do not hide essential content behind a long intro.
- Respect `prefers-reduced-motion` and provide a no-motion path.
- Begin audio only after the visitor opens the invitation or presses play.
- Keep audio controls visible, keyboard accessible, and understandable without icons alone.

## 8. Media and Cultural Care

- Use user-provided or properly licensed media and fonts.
- Provide meaningful alternative text for informative images; mark purely decorative art accordingly.
- Do not invent religious quotations, family names, titles, dates, or ceremonial details.
- Make ornamental and cultural elements appropriate to the user's requested context.
- Crop focal subjects safely across phone aspect ratios.
- Avoid embedding full-resolution originals when responsive derivatives are available.
