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
| `text` | `string` | `''` | Tooltip label rendered inside the bubble. Plain text only (escaped by Blade). |
| `position` | `'top' \| 'right' \| 'bottom' \| 'left'` | `'top'` | Where the bubble appears relative to the trigger. CSS-positioned; no auto-flip on viewport collision. |
| `color` | `'base-100' \| 'base-200' \| 'base-300' \| 'neutral' \| 'primary' \| 'secondary' \| 'accent' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'base-100'` | Bubble background and content tone. Default `'base-100'` (since v0.3.14) renders as a page-surface card with subtle `base-300` border — the lightest, most readable variant. `'base-200'` / `'base-300'` step the surface darker; `'neutral'` is daisyUI's stock dark bubble for the classic look. Eight semantic colours map to `bg-{color} text-{color}-content border-{color}` on both bubble and arrow. |
| `open` | `bool` | `false` | When `true`, the tooltip stays open regardless of hover / focus — useful for onboarding tours and highlighting. |

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

See [`src/Compose/TooltipComposer.php`](../../src/Compose/TooltipComposer.php). Returns `root` (`relative inline-block`), `bubble` (absolute + placement + bg/text/border + radius + shadow), `arrow` (rotated diamond with two visible borders matching the bubble's border colour), `placement` (normalised string), `forceOpen` (bool from the `open` prop).

The blade wires Alpine `x-on:mouseenter` / `mouseleave` / `focusin` / `focusout` to a local `open` boolean. When `forceOpen` is true, the wiring is skipped and the bubble stays visible.

**v0.3.11 rewrite**: prior versions used daisyUI's `tooltip` + `data-tip` system (CSS-only, no JS). That arrow uses a `mask-image` whose fill is `--tt-bg` only — when bg was `base-100` (page-coloured), the arrow vanished. The custom diamond arrow used by [`<x-popover>`](./popover.md) renders cleanly for every bg, so tooltip was rewritten to mirror it. Surface props (`text`, `position`, `color`, `open`) are unchanged; internal DOM and classes are different.

## Related

- [`<x-dropdown>`](./dropdown.md) — interactive hover/click panel for rich content.
- [`<x-alert>`](./alert.md) — persistent inline messaging with the same color vocabulary.
- [`<x-button>`](./button.md) — the most common trigger for an icon-button + tooltip pair.

## Notes

- The default (`color=null`) is a soft `bg-base-200` bubble — the package's house style. All four `base-*` surface variants now ship a visible arrow (v0.3.11 fix).
- **`color="neutral"`** uses daisyUI's `bg-neutral` semantic colour (typically dark on light themes, near-black) — the classic dark-tooltip look.
- **Alpine required**: the rewrite swapped daisyUI's CSS-only hover for Alpine handlers. Pinion-ui already requires Alpine (`ui:install` wires it), so this is essentially free.
- **No viewport collision detection**: same trade-off as [`<x-popover>`](./popover.md) — pick the placement that fits where the trigger lives. For auto-flip, integrate Floating UI separately.
- Tooltips are decorative — **always** keep an `aria-label` (or visible text) on the trigger for assistive tech. The bubble has `role="tooltip"` but a screen reader only picks it up reliably when the trigger has `aria-describedby` pointing at the bubble's ID (not done automatically — wire it yourself for critical labels).
- The wrapper is `inline-block` so it doesn't break inline flow. If you need block layout for the trigger, override with `class="block"` on the tooltip.
