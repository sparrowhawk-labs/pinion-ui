# x-indicator

Positions a badge or a small dot at a corner (or midpoint) of arbitrary child content. Built on daisyUI's `indicator` utility — useful for notification counters on icons, status dots on avatars, "new" labels on cards.

**Playground page**: [`pinion-ui-playground/resources/views/pages/indicator.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/indicator.blade.php) — full variant matrix and live demos.

## When to use

- Adding an unread-count badge to an icon button.
- Decorating an avatar with a status indicator that isn't one of the four `<x-avatar status="...">` presets.
- For full-width inline labels use `<x-badge>` directly; for floating tooltips use `<x-tooltip>`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `position` | `'top-start' \| 'top-center' \| 'top-end' \| 'middle-start' \| 'middle-center' \| 'middle-end' \| 'bottom-start' \| 'bottom-center' \| 'bottom-end'` | `'top-end'` | Corner / midpoint anchor for the indicator. Maps to daisyUI's two-class pair (`indicator-top indicator-end`, etc.). |
| `dot` | `bool` | `false` | If `true`, renders a small colored dot (`badge-xs`, no slot content). If `false`, renders a normal badge using the `badge` slot. |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'error'` | Indicator color. Drives `badge-{color}`. Defaults to `error` because the most common use case is unread / alert counts. |

All other attributes pass through to the root `<div>`.

## Slots

- **default** — the child content the indicator decorates (button, avatar, icon, card).
- **badge** *(named)* — content for the indicator chip (e.g. a number, short label). Ignored when `dot=true`.

## Examples

### Basic dot

```blade
<x-indicator dot>
    <x-button appearance="ghost">
        <x-i type="bell" class="w-5 h-5" />
    </x-button>
</x-indicator>
```

### Counter badge

```blade
<x-indicator color="error">
    <x-slot:badge>3</x-slot:badge>
    <x-button appearance="ghost">
        <x-i type="bell" class="w-5 h-5" />
    </x-button>
</x-indicator>
```

### Custom position

```blade
<x-indicator position="bottom-start" dot color="success">
    <x-avatar src="/users/akihiko.jpg" alt="Akihiko" />
</x-indicator>
```

### Text label

```blade
<x-indicator color="warning">
    <x-slot:badge>NEW</x-slot:badge>
    <x-card class="w-64">
        <p>Featured item</p>
    </x-card>
</x-indicator>
```

## Class composition

See [`src/Compose/IndicatorComposer.php`](../../src/Compose/IndicatorComposer.php). Returns `root` (`indicator`) and `item` (`indicator-item` + position pair + `badge` + `badge-{color}` + optional `badge-xs` when `dot=true`). Per `docs/daisyui/pages/indicator.md`, the daisyUI `indicator` utility positions absolute children relative to a wrapper that auto-sizes to its non-indicator child.

## Related

- [`<x-badge>`](./badge.md) — standalone version of the chip used internally by indicator.
- [`<x-avatar>`](./avatar.md) — has its own four-state `status` dot baked in; reach for `<x-indicator>` when you need different colors or a count badge.

## Notes

- The wrapper itself sizes to its child (the default slot), so position anchors are relative to the child's edges, not the page.
- `dot=true` hides any content passed to the `badge` slot — the indicator becomes purely decorative.
- `neutral` is supported as of v0.2.3 (was previously falling through to `badge-error` — a real bug). Use it for low-key indicator dots where the alert-feel of `error` is too loud.
- For dynamic counts that may hit zero, conditionally render `<x-indicator>` outside the markup — there is no `hideOnZero` prop.
