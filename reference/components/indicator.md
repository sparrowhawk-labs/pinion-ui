# x-indicator

Positions a badge or a small dot at a corner (or midpoint) of arbitrary child content — useful for notification counters on icons, status dots on avatars, "new" labels on cards. Positioning is plain Tailwind (`relative` root + `absolute` chip anchored with `top`/`bottom`/`start`/`end` + half-size translate); daisyUI's `indicator`/`indicator-item`/`indicator-{position}` classes are not used (per the project-wide rule against daisyUI structural classes).

**Playground page**: [`pinion-ui-playground/resources/views/pages/indicator.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/indicator.blade.php) — full variant matrix and live demos.

## When to use

- Adding an unread-count badge to an icon button.
- Decorating an avatar with a status indicator that isn't one of the four `<x-avatar status="...">` presets.
- For full-width inline labels use `<x-badge>` directly; for floating tooltips use `<x-tooltip>`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `position` | `'top-start' \| 'top-center' \| 'top-end' \| 'middle-start' \| 'middle-center' \| 'middle-end' \| 'bottom-start' \| 'bottom-center' \| 'bottom-end'` | `'top-end'` | Corner / midpoint anchor for the indicator. Maps to a plain-Tailwind `top`/`bottom`/`start`/`end` + half-size translate combo (e.g. `top-0 end-0 -translate-y-1/2 translate-x-1/2` for `top-end`), not daisyUI's `indicator-*` classes. |
| `dot` | `bool` | `false` | If `true`, renders a small colored dot (fixed 12px circle, no slot content). If `false`, renders a normal badge using the `badge` slot. |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'error'` | Indicator color. Drives the chip's utility color classes (same grammar as `<x-badge>`). Defaults to `error` because the most common use case is unread / alert counts. |
| `appearance` | `'solid' \| 'soft' \| 'outline' \| 'ghost' \| 'dash'` | `'solid'` | Chip style (utility-composed since v0.4.2; daisyUI `badge-*` classes are no longer emitted). `'solid'` (default — full fill) keeps the alert-feel of a notification dot strong. Opt into `'soft'` for a tinted bubble when indicators stack close together and the saturated fill feels heavy. `'outline'` / `'dash'` sit on an opaque `base-100` fill so they stay readable over the decorated content; `'ghost'` is a neutral `base-200` chip that ignores `color`. |

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

### Soft variant (tinted bubble)

```blade
<x-indicator appearance="soft" color="error">
    <x-slot:badge>3</x-slot:badge>
    <x-button appearance="ghost"><x-i type="bell" class="w-5 h-5" /></x-button>
</x-indicator>
```

## Class composition

See [`src/Compose/IndicatorComposer.php`](../../src/Compose/IndicatorComposer.php). Returns `root` (`relative inline-flex w-fit`) and `item` (`absolute z-10` + a plain-Tailwind position combo + a utility-composed chip: size/shape base + literal appearance × color classes mirroring `<x-badge>`'s grammar). Neither daisyUI's `.badge` classes (v0.4.2) nor its `.indicator`/`.indicator-item`/`.indicator-*` positioning classes are used — both are on the preset's daisyUI exclude list and would not render in consumer builds anyway.

## Related

- [`<x-badge>`](./badge.md) — standalone version of the chip used internally by indicator.
- [`<x-avatar>`](./avatar.md) — has its own four-state `status` dot baked in; reach for `<x-indicator>` when you need different colors or a count badge.

## Notes

- The wrapper itself sizes to its child (the default slot), so position anchors are relative to the child's edges, not the page.
- `dot=true` hides any content passed to the `badge` slot — the indicator becomes purely decorative.
- `neutral` is supported as of v0.2.3 (was previously falling through to the `error` color — a real bug). Use it for low-key indicator dots where the alert-feel of `error` is too loud.
- **v0.3.4 default reverted**: `appearance` defaults to `'solid'` again. v0.3.0 briefly flipped this to `'soft'` for the calmer stack-of-badges case, but in practice the saturated fill is what reads as "notification needs attention" — the soft tint was too quiet. Pass `appearance="soft"` to opt back into the muted bubble per call site.
- For dynamic counts that may hit zero, conditionally render `<x-indicator>` outside the markup — there is no `hideOnZero` prop.
