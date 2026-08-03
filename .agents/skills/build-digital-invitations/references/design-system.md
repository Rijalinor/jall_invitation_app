# Invitation Design Protocol

## Contents

1. Creative brief
2. Structural differentiation
3. Design tokens
4. Mobile composition
5. Typography and content
6. Motion and sound
7. Media and cultural care

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

## 5. Typography and Content

- Use display type for emotional hierarchy and a highly readable body face for details.
- Limit the number of font families and weights.
- Provide font-loading fallbacks and avoid invisible text while fonts load.
- Test long Indonesian names, honorifics, venue names, and addresses.
- Preserve semantic heading order even when the visual order is unconventional.
- Keep event date, time, venue, and navigation actions visually unambiguous.

## 6. Motion and Sound

- Tie motion to the concept: page turns for a letter, slow reveal for cinematic work, gentle parallax for botanical depth, or crisp cuts for editorial layouts.
- Animate opacity and transforms preferentially; avoid layout-thrashing effects.
- Do not hide essential content behind a long intro.
- Respect `prefers-reduced-motion` and provide a no-motion path.
- Begin audio only after the visitor opens the invitation or presses play.
- Keep audio controls visible, keyboard accessible, and understandable without icons alone.

## 7. Media and Cultural Care

- Use user-provided or properly licensed media and fonts.
- Provide meaningful alternative text for informative images; mark purely decorative art accordingly.
- Do not invent religious quotations, family names, titles, dates, or ceremonial details.
- Make ornamental and cultural elements appropriate to the user's requested context.
- Crop focal subjects safely across phone aspect ratios.
- Avoid embedding full-resolution originals when responsive derivatives are available.
