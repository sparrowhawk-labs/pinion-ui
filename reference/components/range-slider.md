# x-range-slider

Styled `<input type="range">` with label, value display, and helper/error chrome. Eight semantic colours × five sizes. Native pseudo-element styling (`.pn-range` in `pinion-ui.css`) — no daisyUI class involved (migrated off `range`/`range-{color}`/`range-{size}` per CLAUDE.md invariant 6). Keyboard-accessible by default, value persists with the `name` attribute on form submit.

**Playground page**: [`pinion-ui-playground/resources/views/pages/range-slider.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/range-slider.blade.php) — full variant matrix and live demos.

## When to use

- Bounded numeric inputs where the user benefits from seeing the magnitude visually (volume, brightness, price ceiling).
- Settings that don't need a precise number — for exact entry pair with `<x-input type="number">` (or `<x-input-number>`).
- For star-rating input use `<x-rating>` instead.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string \| null` | `null` | Form field name; submitted with the form. Omit for purely visual sliders. |
| `label` | `string \| null` | `null` | Visible label rendered above the input. |
| `hint` | `string \| null` | `null` | Helper text below the slider. Shadowed by `error` when both are set. |
| `error` | `string \| null` | `null` | Error message; flips label and hint colours to `text-error` and replaces `hint`. |
| `min` | `int \| float` | `0` | Minimum value. |
| `max` | `int \| float` | `100` | Maximum value. |
| `value` | `int \| float \| null` | `null` (= `min`) | Initial value. |
| `step` | `int \| float` | `1` | Increment. |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'primary'` | Semantic colour — drives the filled portion of the track + the thumb's ring border. |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg' \| 'xl'` | `'md'` | Track/thumb height. `'md'` is the stock size and emits no modifier. |
| `showValue` | `bool` | `false` | When `true`, shows the current value next to the label (or below the slider if no label), updated live via Alpine. |
| `disabled` | `bool` | `false` | Disables the input. |
| `required` | `bool` | `false` | Renders a `*` next to the label and adds the `required` attribute. |

`wire:`-prefixed attributes are forwarded to the input for Livewire models. All other attributes pass through to the input as well.

## Slots

None — the component is fully prop-driven.

## Examples

### Basic

```blade
<x-range-slider name="volume" label="Volume" />
```

### With value display + step

```blade
<x-range-slider
    name="brightness"
    label="Brightness"
    :min="0" :max="100" :value="50" :step="5"
    showValue />
```

### Color + size variants

```blade
<x-range-slider color="success" size="lg" label="Quality" :value="80" showValue />
<x-range-slider color="warning" size="sm" label="Risk"   :value="20" showValue />
<x-range-slider color="error"   size="xs" label="Volume — hot zone" :value="95" />
```

### Error state

```blade
<x-range-slider
    name="price"
    label="Max price"
    :min="0" :max="1000" :value="50"
    error="Must be at least 100" />
```

### Livewire model

```blade
<x-range-slider name="zoom" wire:model.live="zoom" :min="50" :max="200" :value="$zoom" showValue />
```

## Class composition

See [`src/Compose/RangeSliderComposer.php`](../../src/Compose/RangeSliderComposer.php). Returns `input` (`pn-range w-full pn-range-{color}` + optional `pn-range-{size}`), `labelColor`, and `hintColor`. Size `'md'` emits no modifier because `.pn-range` is the medium variant by default. Label / hint colours flip to `text-error` when an `error` prop is passed — same `FieldVariants::labelColor / hintColor` helpers used by `<x-input>` etc.

## Related

- [`<x-rating>`](./rating.md) — star input for ratings; same `name`-required idea.
- [`<x-input>`](./input.md) — text/number input; combine for "type or drag" patterns.
- [`<x-progress>`](./progress.md) — non-interactive bar; use this when the value is read-only.

## Notes

- Both `min` and `max` attributes are required on a native `<input type="range">`; the wrapper always emits them (defaults 0 / 100) so you don't have to remember.
- The Alpine `showValue` binding (`x-data="{ v: ... }"`) keeps the displayed value in sync without a server roundtrip. If you only need the final value, omit `showValue` and read the form value on submit.
- `size="md"` emits no modifier — consistent with other Pinion components. Pass an explicit non-md size when you want to test against multiple sizes side by side.
- **Implementation (post-daisyUI migration, 2026-07-09)**: `.pn-range` in `pinion-ui.css` styles the native `<input type="range">` directly via `::-webkit-slider-thumb`/`::-webkit-slider-runnable-track` (WebKit/Chromium) and `::-moz-range-thumb`/`::-moz-range-track`/`::-moz-range-progress` (Firefox), keyed off `var(--color-*)` (theme-reactive) and `var(--radius-selector)` / `var(--size-selector)` (tune-reactive). Firefox fills the track natively via `::-moz-range-progress` (no JS needed). WebKit has no native fill pseudo-element, so the "filled" portion up to the current value is faked with a `linear-gradient(...)` on the track background keyed to a `--range-fill: N%` custom property — computed once server-side in `range-slider.blade.php` for the initial paint, then kept live by a small Alpine `x-on:input` handler (`pct` in the component's `x-data`, always present regardless of `showValue`).
- **Confidence / caveat**: this replaces daisyUI's own trick, which fakes the WebKit fill via an undocumented container-query-width + box-shadow technique (see `node_modules/daisyui/components/range.css`) rather than a gradient. The gradient approach here is the more conventional/portable technique but was **not verified in a live browser** in this environment — no Playwright/browser tooling was run against it. Before considering this migration fully done, do a manual visual spot-check of `<x-range-slider>` across at least Chrome + Firefox (and ideally Safari), and across a couple of `data-tune` presets (e.g. `default`, `sharp`, `pixel`) and both themes (`pinion`, `reactive`), to confirm: (1) the thumb is vertically centered on the track, (2) the gradient fill boundary lines up with the actual thumb position while dragging, and (3) the pill/thumb radius tracks the active tune as expected.
