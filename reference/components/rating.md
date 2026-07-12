# x-rating

Star rating input rendered with real inline `<svg>` shapes (star/heart/circle) — **no daisyUI `rating`/`mask` classes** (migrated 2026-07-09 per CLAUDE.md invariant 6). Renders a set of `<input type="radio">` + `<svg>` sibling pairs (one pair per star, or two pairs per star in `half` mode) plus a hidden "no rating" radio so users can clear the selection. CSS-only — no JS.

**Playground page**: [`pinion-ui-playground/resources/views/pages/rating.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/rating.blade.php) — full variant matrix and live demos.

## When to use

- Capturing a user score on a fixed scale — review, feedback, satisfaction.
- For non-rating progress indicators use [`<x-progress>`](./progress.md); for thumbs-up/down use a pair of `<x-button>` instead.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string` | *(required)* | Radio group name. **Must be unique per page** — multiple `<x-rating>` with the same `name` will collapse into one group. Throws `InvalidArgumentException` if omitted. |
| `value` | `float \| int` | `0` | Current rating. Rounded to the nearest step (1 in default mode, 0.5 in `half` mode). |
| `max` | `int` | `5` | Number of stars. Coerced to `max(1, …)`. |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg' \| 'xl'` | `'md'` | Star size — `w-4 h-4` (xs) through `w-8 h-8` (xl), same 1rem/1.25rem/1.5rem/1.75rem/2rem scale daisyUI used. |
| `color` | `'warning' \| 'primary' \| 'secondary' \| 'accent' \| 'info' \| 'success' \| 'error'` | `'warning'` | Star fill color. Maps to Tailwind's `fill-{color}` utility (semantic color, per invariant 6) on the `<svg>`. |
| `half` | `bool` | `false` | Half-star granularity. Doubles the radio+svg pair count per star; each `<svg>` is the SAME shape cropped to its left/right half via its own `viewBox` (no `mask-half-1`/`mask-half-2`). |
| `readonly` | `bool` | `false` | Disables all radios so the value is displayed but not editable. |
| `shape` | `'star' \| 'heart' \| 'circle'` | `'star'` | Which shape is rendered as an inline `<svg><path>` (star/heart, geometry lifted verbatim from daisyUI 5's own mask data URIs) or `<svg><circle>` (circle). No CSS mask involved. |

All other attributes pass through to the root `<div>` (e.g. `class`, `aria-label`) — **except `wire:model`**, which is detected and forwarded onto the individual radio inputs (with native `value` attributes) for Livewire two-way binding, and stripped from the root `<div>`. The component itself ships no Alpine/JS runtime; `wire:model` is handled entirely by Livewire.

## Slots

This component has no public slots — stars come from `max` and the radio inputs are auto-generated.

## Examples

### Basic

```blade
<x-rating name="review_score" :value="4" />
```

### Half-star granularity

```blade
<x-rating name="precision_score" :value="3.5" half />
```

### Custom shape + color

```blade
<x-rating name="like" :value="2" :max="5" shape="heart" color="error" />
```

### Read-only display

```blade
<x-rating name="display_score" :value="4.5" half readonly />
```

### Sizes

```blade
<x-rating name="r_xs" :value="3" size="xs" />
<x-rating name="r_lg" :value="3" size="lg" />
```

## Class composition

Class strings live in [`src/Compose/RatingComposer.php`](../../src/Compose/RatingComposer.php); the blade renders the resulting dict plus shape geometry from the separate pure helper `RatingComposer::shapeGeometry()` (kept out of `compose()`'s dict per the Compose purity invariant — same pattern as `StatComposer::arrowChar`). Keys: `root` (`pn-rating inline-flex items-center`, no size/half suffix — sizing now lives per-item, not on root), `hidden` (the value=0 clear radio, size-independent), `input` / `inputHalf` (the invisible radio, sized to match its `<svg>`'s box), `star` / `starHalf` (the visible `<svg>`, `fill-{color}` + size box + a matching `-ml-*` negative margin that pulls it back on top of its own `<input>`).

## Related

- [`<x-progress>`](./progress.md) — non-input progress display.
- [`<x-radio>`](./radio.md) — the underlying primitive (this component renders radios under the hood).

## Notes

- **daisyUI migration (2026-07-09)**: this component used to render each star as the `<input>` itself, CSS-masked (`mask` + `mask-star`/`mask-half-1`/…) into shape. That violated CLAUDE.md invariant 6 (no daisyUI structural classes) and has been replaced with a real inline `<svg>` per star, positioned as a sibling of an invisible same-size `<input>` (no wrapper element — see below for why). The star/heart path data was lifted verbatim from daisyUI 5.5.18's compiled `mask-star`/`mask-heart` data-URIs (`node_modules/daisyui/daisyui.css`) so the shapes are visually identical to the old render.
- **Checked/unchecked mechanism**: verified against daisyUI 5.5.18's own compiled CSS, which uses `.rating :where(*) { opacity: .2 }` plus `.rating * { &:checked, &:has(~:checked) { opacity: 1 } }` — an input is fully opaque if it's checked OR it `:has()` a *later* DOM sibling that's checked (stars are flat, ascending DOM order, so "a later/higher-numbered star is checked" means "light up all preceding stars too"). pinion-ui's CSS (`src/resources/css/pinion-ui.css`, section "rating stars — daisyUI-free") retargets the identical `:has()` selector from the input itself onto the adjacent `<svg data-pn-star>`: `.pn-rating input:checked + svg[data-pn-star], .pn-rating svg[data-pn-star]:has(~ input:checked) { opacity: 1 }`.
- **Flat DOM, no wrapper**: the `<input>`/`<svg>` pairs are direct siblings of the root `<div>` — not each wrapped in its own `<label>`/`<span>` — because the `+`/`~` sibling combinators above only match siblings sharing the same parent. Each `<input>` is sized exactly to its `<svg>`'s box (`w-N h-N`) and made invisible (`opacity-0`, not `display:none`/`sr-only`, so it still occupies its layout box and stays clickable); the `<svg>` is pulled back on top of it with a matching negative left margin (`-ml-N`) and `pointer-events: none`, so clicks land on the `<input>` beneath.
- **Half-star crop**: instead of daisyUI's `mask-half-1`/`mask-half-2` (`mask-position: 0/100%` + `mask-size: 200%`), each half `<svg>` renders the exact same `path`/`circle` markup but with its own `viewBox` cropped to the shape's left half (`"0 0 W/2 H"`) or right half (`"W/2 0 W/2 H"`) of the full shape's coordinate space — native SVG cropping, no CSS mask involved.
- A hidden clear radio (value=0, class `pn-rating-hidden`) is always rendered so users can clear their rating — selecting it gives `value=0`. No `<svg>` follows it.
- The `aria-label="評価なし"` on the clear radio is Japanese ("no rating"); change locally if you need a different default. (English equivalent: "No rating".)
- Submitted value: the native radio for the chosen step. In `half` mode the form receives the radio's `value` attribute — wire your own controller logic if you need the half-star numeric score (each `step` maps to `step * 0.5`).
- **⚠ Needs manual visual spot-check**: the `:has(~ input:checked)` opacity-toggle CSS was verified by careful reasoning against daisyUI's own compiled selector (and the fixture/unit tests all pass), but it has **not been exercised in a live browser** in this environment. Before considering this migration fully done, open `<x-rating>` in the playground (`http://pinion-ui-playground.pizza/rating` or similar) and click through several star positions in both `half=false` and `half=true` mode, in at least one browser, to confirm: (1) clicking star N visually fills stars 1..N and dims N+1..max, (2) half-star clicks fill the correct half, (3) `readonly` truly blocks interaction, (4) focus-visible outline appears on keyboard nav. `:has()` is supported in all evergreen browsers (Chrome/Safari/Firefox, 2023+), so this is a low-risk but unverified assumption.
