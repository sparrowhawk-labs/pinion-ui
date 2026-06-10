# x-range-slider

Styled `<input type="range">` with label, value display, and helper/error chrome. Eight semantic colours × five sizes. Built on daisyUI's `range` utility — keyboard-accessible by default, value persists with the `name` attribute on form submit.

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
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'primary'` | daisyUI semantic colour — drives track and thumb tint. |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg' \| 'xl'` | `'md'` | Track/thumb height. `'md'` is daisyUI's stock size and emits no modifier. |
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

See [`src/Compose/RangeSliderComposer.php`](../../src/Compose/RangeSliderComposer.php). Returns `input` (`range w-full range-{color}` + optional `range-{size}`), `labelColor`, and `hintColor`. Size `'md'` emits no modifier because daisyUI's `range` is the medium variant by default. Label / hint colours flip to `text-error` when an `error` prop is passed — same `FieldVariants::labelColor / hintColor` helpers used by `<x-input>` etc.

## Related

- [`<x-rating>`](./rating.md) — star input for ratings; same `name`-required idea.
- [`<x-input>`](./input.md) — text/number input; combine for "type or drag" patterns.
- [`<x-progress>`](./progress.md) — non-interactive bar; use this when the value is read-only.

## Notes

- daisyUI requires both `min` and `max` attributes on the `<input>`; the wrapper always emits them (defaults 0 / 100) so you don't have to remember.
- The Alpine `showValue` binding (`x-data="{ v: ... }"`) keeps the displayed value in sync without a server roundtrip. If you only need the final value, omit `showValue` and read the form value on submit.
- `size="md"` emits no modifier — consistent with other Pinion components. Pass an explicit non-md size when you want to test against multiple sizes side by side.
- Browser native range styling varies; daisyUI's `range` overrides both WebKit and Firefox track/thumb styles. Pair with `<x-progress>` if you want a visually identical read-only counterpart.
