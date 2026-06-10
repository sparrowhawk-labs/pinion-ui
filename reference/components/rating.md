# x-rating

Star rating input built on daisyUI's `rating` + `mask` system. Renders a set of radio inputs (one per star, or two per star in `half` mode) plus a hidden "no rating" radio so users can clear the selection. CSS-only — no JS.

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
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg' \| 'xl'` | `'md'` | Star size. Always emits the explicit `rating-{size}` class — see Notes. |
| `color` | `'warning' \| 'primary' \| 'secondary' \| 'accent' \| 'info' \| 'success' \| 'error'` | `'warning'` | Star fill color. Maps to `bg-{color}` (daisyUI's `mask` shapes show through as filled). |
| `half` | `bool` | `false` | Half-star granularity. Doubles the radio count and uses `mask-half-1` / `mask-half-2` per star. |
| `readonly` | `bool` | `false` | Disables all radios so the value is displayed but not editable. |
| `shape` | `'star' \| 'heart' \| 'circle'` | `'star'` | Mask shape: `mask-star`, `mask-heart`, or `mask-circle`. |

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

Class strings live in [`src/Compose/RatingComposer.php`](../../src/Compose/RatingComposer.php) and the blade renders only the resulting dict. Keys: `root` (`rating rating-{size}` + optional `rating-half`), `item` (single-star mask), `itemHalf1` / `itemHalf2` (split-star masks), `hidden` (the value=0 clear radio: `rating-hidden`).

## Related

- [`<x-progress>`](./progress.md) — non-input progress display.
- [`<x-radio>`](./radio.md) — the underlying primitive (this component renders radios under the hood).

## Notes

- **daisyUI gotcha**: `rating-half` only takes effect when combined with an explicit `rating-{size}` class. Without it, half inputs fall back to the 1.5rem default width, overlap each other, and collapse the row. So `RatingComposer` always emits the size class — including `md` (which would normally be the unstyled default). See `docs/daisyui/pages/rating.md`.
- A hidden `rating-hidden` radio (value=0) is always rendered so users can clear their rating — selecting it gives `value=0`.
- The `aria-label="評価なし"` on the clear radio is Japanese ("no rating"); change locally if you need a different default. (English equivalent: "No rating".)
- Submitted value: the native radio for the chosen step. In `half` mode the form receives the radio's `value` attribute — wire your own controller logic if you need the half-star numeric score (each `step` maps to `step * 0.5`).
