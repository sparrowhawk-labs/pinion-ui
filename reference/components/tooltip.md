# x-tooltip

No-JS hover/focus tooltip built on daisyUI 5's `tooltip` class. Trigger goes in the slot, label text goes in `text`. Four positions, ten color choices (light default + nine daisyUI variants), and an `open` prop for always-shown onboarding callouts.

**Playground page**: [`pinion-ui-playground/resources/views/pages/tooltip.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/tooltip.blade.php) — position / surface / color matrix plus icon-button / disabled-explanation / form-helper patterns.

## When to use

- Add a short label to an icon-only button or other terse control.
- Explain why a `disabled` action is unavailable (wrap the trigger in a `<span class="inline-block">` since `disabled` elements don't fire hover events on their own).
- Show overflow / truncated text in full on hover.
- For richer hover panels (interactive content) use [`<x-dropdown>`](./dropdown.md) with hover trigger.
- For persistent inline status use [`<x-alert>`](./alert.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `text` | `string` | `''` | Tooltip label. Set on the root as `data-tip` — daisyUI renders it via `::before`. |
| `position` | `'top' \| 'right' \| 'bottom' \| 'left'` | `'top'` | Where the bubble appears relative to the trigger (`tooltip-{position}`). |
| `color` | `null \| 'neutral' \| 'primary' \| 'secondary' \| 'accent' \| 'info' \| 'success' \| 'warning' \| 'error' \| 'base-100' \| 'base-200' \| 'base-300'` | `null` | `null` (default) → `tooltip-light` (soft base-200 fill, no border) — the package's house style. `neutral` → no modifier class, falls through to daisyUI's stock dark grey bubble. `base-100` / `base-200` / `base-300` → explicit surface-tinted variants. The eight semantic colors map to daisyUI's `tooltip-{color}`. |
| `open` | `bool` | `false` | When `true`, adds `tooltip-open` to keep the bubble visible regardless of hover/focus. Useful for onboarding tours and highlighting. |

All other attributes pass through to the root wrapper `<div>`.

## Slots

- **default** — the trigger element. Hover / focus on this slot shows the tooltip.

## Examples

### Basic

```blade
<x-tooltip text="Hi from tooltip">
    <x-button>Hover me</x-button>
</x-tooltip>
```

### Positions

```blade
<x-tooltip text="Above" position="top"><x-button>top</x-button></x-tooltip>
<x-tooltip text="Right" position="right"><x-button>right</x-button></x-tooltip>
<x-tooltip text="Below" position="bottom"><x-button>bottom</x-button></x-tooltip>
<x-tooltip text="Left" position="left"><x-button>left</x-button></x-tooltip>
```

### Colors

```blade
{{-- light (default) — soft base-200 fill --}}
<x-tooltip text="Default">…</x-tooltip>

{{-- neutral — daisyUI stock dark grey bubble --}}
<x-tooltip text="Stock dark" color="neutral">…</x-tooltip>

{{-- semantic --}}
<x-tooltip text="Saved" color="success">…</x-tooltip>
<x-tooltip text="Required" color="error">…</x-tooltip>

{{-- explicit surface tints (rare — for matching a specific background) --}}
<x-tooltip text="Surface-matched" color="base-300">…</x-tooltip>
```

### Always open (onboarding)

```blade
<x-tooltip text="Click here to start" :open="true" color="primary">
    <x-button>Start</x-button>
</x-tooltip>
```

### Icon button (the canonical use case)

```blade
<x-tooltip text="Close" position="top">
    <button class="btn btn-circle btn-ghost" aria-label="close">
        <x-i type="close-circle" variant="linear" class="w-5 h-5" />
    </button>
</x-tooltip>
```

### Explaining a disabled button

`disabled` elements don't fire hover events themselves — wrap the trigger in a `<span class="inline-block">` so the tooltip wrapper receives hover correctly:

```blade
<x-tooltip text="Fill in required fields to save" position="right" color="error">
    <span class="inline-block">
        <x-button color="primary" disabled>Save</x-button>
    </span>
</x-tooltip>
```

## Class composition

Class strings come from [`SparrowhawkLabs\PinionUi\Compose\TooltipComposer`](../../src/Compose/TooltipComposer.php) — a single `root` key combining `tooltip` + position + color + optional `tooltip-open`. The blade puts the label on `data-tip`; daisyUI's CSS does the rest.

## Related

- [`<x-dropdown>`](./dropdown.md) — interactive hover/click panel for rich content.
- [`<x-alert>`](./alert.md) — persistent inline messaging with the same color vocabulary.
- [`<x-button>`](./button.md) — the most common trigger for an icon-button + tooltip pair.

## Notes

- The default `tooltip-light` is a project-level override (not a stock daisyUI class) — it renders a soft `base-200` bubble. The CSS hooks `--tt-bg` to `var(--color-base-200)` so the arrow inherits the same color as the body, avoiding the "border-on-arrow" rendering glitch that stock daisyUI tooltips show under custom borders.
- `color="neutral"` is the explicit opt-out: it adds no `tooltip-*` class so the bubble falls through to daisyUI's stock dark-grey behavior. Use it when you specifically want the classic look.
- Tooltips are pure CSS — no JS, no ARIA-live, no focus trap. They are decorative; **always** keep an `aria-label` (or visible text) on the trigger for assistive tech.
- The wrapper is `display: block` by default (daisyUI's `tooltip` is `display: inline-block` once daisyUI loads). If layout shifts, wrap the trigger in a sized container or set `class="inline-block"` on the tooltip itself.
