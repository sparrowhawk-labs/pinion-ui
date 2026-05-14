# x-input-number

Quantity selector — a numeric `<input type="number">` flanked by joined decrement / increment buttons. Hides the native browser spinner arrows. Step / min / max are enforced both by the input's HTML attributes (for browser validation) and by the Alpine inc/dec logic (so clicking past the bound is silently clamped).

**Playground page**: [`pinion-ui-playground/resources/views/pages/input-number.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/input-number.blade.php) — full variant matrix and live demos.

## When to use

- E-commerce quantity selectors ("how many to add to cart").
- Settings with a discrete, bounded numeric range that the user adjusts in small steps.
- For continuous values or visual magnitude, prefer [`<x-range-slider>`](./range-slider.md).
- For free-form numeric entry without ± buttons, use [`<x-input>`](./input.md) with `type="number"`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string \| null` | `null` | Form field name; submitted with the form. |
| `label` | `string \| null` | `null` | Visible label above the row. |
| `hint` | `string \| null` | `null` | Helper text below; shadowed by `error`. |
| `error` | `string \| null` | `null` | Error message; flips label/hint colour to `text-error` and replaces `hint`. |
| `value` | `int \| float \| string` | `0` | Initial value. Stored in Alpine `v` so click and keyboard input both update the same source. |
| `min` | `int \| float \| null` | `null` | Lower bound. When set, the decrement button auto-disables at the bound and HTML `min=` is emitted. |
| `max` | `int \| float \| null` | `null` | Upper bound. Symmetric to `min`. |
| `step` | `int \| float` | `1` | Increment / decrement amount per button click. |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg'` | `'md'` | Drives both the button box (`w-[var(--h-field-{size})] h-[var(--h-field-{size})]`) and the input height/text-size. |
| `width` | `string \| null` | `null` (= `'w-fit'`) | Override the outer wrapper's width utility — e.g. `'w-32'`, `'w-full'`. The default fits the natural width derived from `digits` (or auto-computed from min/max/value digit count). |
| `digits` | `int \| null` | `null` (auto) | Explicit digit-width override. When `null` (default), the input's HTML `size` attribute is computed as `max(strlen(max), strlen(min), strlen(value), 2) + 1` so the row visually fits its widest possible content. Pass a number to override (e.g. `digits=6` for a fixed 6-character cell). |
| `disabled` | `bool` | `false` | Disables input and both buttons. |
| `readonly` | `bool` | `false` | Input is readonly; buttons still work (so users can click +/− but not type). |
| `required` | `bool` | `false` | Renders `*` next to the label and adds the `required` attribute. |

`wire:`-prefixed attributes are forwarded to the input for Livewire. All other attributes pass through to the input too.

## Slots

None — fully prop-driven.

## Examples

### Basic

```blade
<x-input-number name="quantity" label="Quantity" :value="1" :min="1" />
```

### Bounded with step

```blade
<x-input-number
    name="seats"
    label="Number of seats"
    :value="2" :min="1" :max="8" :step="1"
    hint="1〜8 まで" />
```

### Decimal step

```blade
<x-input-number
    name="rating"
    label="Score"
    :value="3.5" :min="0" :max="5" :step="0.5"
    width="w-40" />
```

### Size variants

```blade
<x-input-number size="xs" :value="1" />
<x-input-number size="sm" :value="1" />
<x-input-number size="lg" :value="1" />
```

### Error state

```blade
<x-input-number name="qty" label="Quantity" :value="0" :min="1" error="At least 1 required" />
```

### Livewire model

```blade
<x-input-number name="qty" wire:model.live="qty" :value="$qty" :min="0" :max="99" />
```

## Class composition

See [`src/Compose/InputNumberComposer.php`](../../src/Compose/InputNumberComposer.php). Returns `wrapper`, `button`, `input`, `labelColor`, `hintColor`.

- **`wrapper`** uses the same join-via-Tailwind-arbitrary-variant trick as [`<x-button-group>`](./button-group.md) — radii zeroed on every direct child, restored on first/last, inner border collapsed. Self-contained; daisyUI's `.join` is not used.
- **`button`** is a base-100 squared cell with hover tint, focus ring, and disabled state — sized by the active `--h-field-{size}` tune token so the row reads as one unit at any size.
- **`input`** centres its text, suppresses the WebKit / Firefox spinner arrows (`[&::-webkit-inner-spin-button]:appearance-none` + `[appearance:textfield]`), and shares the same `--h-field-{size}` height as the buttons.

## Related

- [`<x-range-slider>`](./range-slider.md) — visual magnitude input for the same numeric-bounded shape.
- [`<x-input>`](./input.md) — generic text/number input without ± buttons.
- [`<x-button-group>`](./button-group.md) — the same join technique used here, generalised for any button row.

## Notes

- The Alpine `x-data` block lives inside the component — no global JS needed. Buttons clamp to `min` / `max` and the decrement / increment buttons auto-disable when the value is at the bound.
- `tabindex="-1"` on both buttons keeps keyboard focus on the input — tab and shift-tab move you to the next field, not into the ± buttons. Use the input itself plus its native ↑ / ↓ keys (still wired) for keyboard adjustment.
- The default `width="w-fit"` fits the row to its natural width — the input's HTML `size` attribute (`{digits}+1`) is the dominant width driver. This means a `min=1 max=9` quantity selector ends up visibly narrower than a `min=0 max=10000` price input, without having to hand-tune widths. Pass `width="w-full"` to stretch the row, or `digits=N` to fix the cell width regardless of bounds.
- Decimal `step` values are stored as strings in the Alpine `v` model — `parseFloat()` is called every inc/dec. Watch out for floating-point drift over many clicks (e.g. `0.1 * 7 → 0.7000000000000001`); set `step` precision deliberately.
- `inputmode="numeric"` is always emitted to surface the numeric keypad on mobile.
