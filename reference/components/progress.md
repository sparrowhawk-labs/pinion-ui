# x-progress

Linear progress bar built on the native `<progress>` element. Determinate when you pass `value` (with optional `max`), indeterminate (animated stripe) when you don't. Color/size are opt-in — defaults follow daisyUI's stock progress with sized height tuned for proximity to `<x-input>` / `<x-button>` at matching sizes.

**Playground page**: [`pinion-ui-playground/resources/views/pages/progress.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/progress.blade.php) — determinate / indeterminate / color / size / label-format demos.

## When to use

- Show task progress with a known proportion — file upload %, multi-step form completion, bulk job ETA.
- Omit `value` for indeterminate "working…" states where you can't measure progress.
- For an inline "loading…" affordance use [`<x-spinner>`](./spinner.md). For placeholder block / line skeletons use [`<x-skeleton>`](./skeleton.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `value` | `int \| null` | `null` | Current value. `null` (or omitted) → indeterminate bar with daisyUI's animated stripe (`progress-indeterminate`). |
| `max` | `int` | `100` | Maximum value. Clamped to ≥ 1. |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error' \| null` | `null` | daisyUI semantic color (`progress-{color}`). `null` leaves the stock greyscale bar. |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg'` | `'md'` | Bar height — `h-1` / `h-2` / `h-3` / `h-4`. daisyUI 5 has no `progress-{size}` modifier so size is driven via Tailwind height utilities. |
| `showLabel` | `bool` | `false` | When determinate, renders a small right-aligned numeric label above the bar. Ignored for indeterminate (no value to show). |
| `labelFormat` | `'percent' \| 'fraction'` | `'percent'` | `percent` → `42%`, `fraction` → `42 / 100`. |

All other attributes pass through to the wrapper `<div>` (which has `role="progressbar"` and `aria-valuenow` / `aria-valuemin` / `aria-valuemax` set when determinate).

## Slots

None. The bar renders its own label when `showLabel` is true.

## Examples

### Determinate

```blade
<x-progress :value="42" />
<x-progress :value="42" color="success" />
<x-progress :value="7" :max="10" labelFormat="fraction" showLabel />
```

### Indeterminate

```blade
<x-progress />
<x-progress color="primary" size="sm" />
```

### Size matrix

```blade
<x-progress :value="60" size="xs" />
<x-progress :value="60" size="sm" />
<x-progress :value="60" size="md" />
<x-progress :value="60" size="lg" />
```

### With label

```blade
<x-progress :value="73" color="info" showLabel />
<x-progress :value="3" :max="5" labelFormat="fraction" showLabel />
```

## Class composition

Class strings are built by [`SparrowhawkLabs\PinionUi\Compose\ProgressComposer`](../../src/Compose/ProgressComposer.php) and returned as `root` (wrapper flex column), `bar` (the `<progress>` element), and `label`. The blade view consumes them directly.

## Related

- [`<x-spinner>`](./spinner.md) — inline loading indicator for unknown durations / button states.
- [`<x-skeleton>`](./skeleton.md) — placeholder shapes for content not yet loaded.

## Notes

- The indeterminate state relies on daisyUI's animated stripe, applied by omitting the `value` attribute. The class `progress-indeterminate` is added in PHP for clarity but daisyUI also infers it from the missing attribute.
- `value` is clamped to `[0, max]` so out-of-range input never produces a broken bar.
- ARIA: `aria-valuenow` is only set when determinate — indeterminate progressbars intentionally omit it per the ARIA spec.
