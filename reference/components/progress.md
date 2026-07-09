# x-progress

Linear progress bar built from a plain `<div>` track/fill pair (not the native `<progress>` element — see Class composition below). Determinate when you pass `value` (with optional `max`), indeterminate (animated sweep) when you don't. Color/size are opt-in — size is tuned for proximity to `<x-input>` / `<x-button>` at matching sizes; unset color falls back to a neutral grey fill.

**Playground page**: [`pinion-ui-playground/resources/views/pages/progress.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/progress.blade.php) — determinate / indeterminate / color / size / label-format demos.

## When to use

- Show task progress with a known proportion — file upload %, multi-step form completion, bulk job ETA.
- Omit `value` for indeterminate "working…" states where you can't measure progress.
- For an inline "loading…" affordance use [`<x-spinner>`](./spinner.md). For placeholder block / line skeletons use [`<x-skeleton>`](./skeleton.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `value` | `int \| null` | `null` | Current value. `null` (or omitted) → indeterminate bar with an animated sweep (`animate-progress-indeterminate`, defined in `pinion-ui.css`). |
| `max` | `int` | `100` | Maximum value. Clamped to ≥ 1. |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error' \| null` | `null` | Semantic color utility (`bg-{color}`) applied to the fill. `null` falls back to `bg-neutral` — the div fill has no native browser default to fall back on the way `<progress>` did. |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg'` | `'md'` | Bar height — `h-1` / `h-2` / `h-3` / `h-4`. daisyUI 5 has no `progress-{size}` modifier so size is driven via Tailwind height utilities. |
| `showLabel` | `bool` | `false` | When determinate, renders a small right-aligned numeric label above the bar. Ignored for indeterminate (no value to show). |
| `labelFormat` | `'percent' \| 'fraction'` | `'percent'` | `percent` → `42%`, `fraction` → `42 / 100`. |

All other attributes pass through to the wrapper `<div>` (which has `role="progressbar"` and `aria-valuenow` / `aria-valuemin` / `aria-valuemax` set when determinate — see Class composition for why this is the sole accessibility surface now).

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

Class strings are built by [`SparrowhawkLabs\PinionUi\Compose\ProgressComposer`](../../src/Compose/ProgressComposer.php) and returned as `root` (wrapper flex column), `track` (the outer div — track background + rounded clip), `fill` (the inner div whose width is the actual progress bar), and `label`.

Per [CLAUDE.md invariant 6](../../CLAUDE.md), daisyUI's structural `progress` / `progress-{color}` / `progress-indeterminate` classes are no longer used. Those classes only styled the native `<progress>` element's `::-webkit-progress-bar` / `::-webkit-progress-value` / `::-moz-progress-bar` pseudo-elements — vendor-prefixed, non-Tailwind-addressable surfaces that made daisyUI's CSS load-bearing for this component. `<x-progress>` now renders a plain `<div>` track containing a `<div>` fill:

- `track`: `w-full bg-base-300 rounded-full overflow-hidden` + a height utility (`h-1`/`h-2`/`h-3`/`h-4` per `size`).
- `fill`: `h-full rounded-full` + a semantic `bg-{color}` utility (default `bg-neutral`), plus either `transition-[width] duration-300 ease-out` (determinate — width set inline via `style="width: {percentage}%"` in the Blade view, since it's a per-render computed value, not a class) or `w-1/3 animate-progress-indeterminate` (indeterminate — a small custom `@keyframes progress-indeterminate` sweep added to `pinion-ui.css`, replacing daisyUI's animated stripe).

**Accessibility**: the ARIA `progressbar` pattern (`role="progressbar"` + `aria-valuenow`/`aria-valuemin`/`aria-valuemax` on the wrapper `<div>`) was already the accessibility contract before this migration and needed no native `<progress>` element to work — it's a standard div-based ARIA widget pattern. No hidden/`sr-only` native `<progress>` announcer was added; the wrapper's ARIA attributes are the sole accessibility surface, same as before.

## Related

- [`<x-spinner>`](./spinner.md) — inline loading indicator for unknown durations / button states.
- [`<x-skeleton>`](./skeleton.md) — placeholder shapes for content not yet loaded.

## Notes

- The indeterminate state uses `animate-progress-indeterminate` (defined in `pinion-ui.css`, next to the tune token imports) — a `translateX(-100%)` → `translateX(300%)` sweep on a `w-1/3` fill, looping every 1.4s.
- `value` is clamped to `[0, max]` so out-of-range input never produces a broken bar.
- ARIA: `aria-valuenow` is only set when determinate — indeterminate progressbars intentionally omit it per the ARIA spec.
