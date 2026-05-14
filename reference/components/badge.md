# x-badge

Small inline label for status, counts, or category tags. Renders `<span>` with eight semantic colors × seven appearances × four sizes, plus pill shape, optional left icon, and a special `dot` appearance that renders a neutral chip with a colored indicator dot.

**Playground page**: [`pinion-ui-playground/resources/views/pages/badge.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/badge.blade.php) — full variant matrix and live demos.

## When to use

- Labeling status (`Active`, `Draft`, `Failed`) inside tables, lists, or headers.
- Showing counts ("3 new") — pair with `<x-indicator>` to anchor at a corner of another element.
- For inline keyboard keys use `<x-kbd>`; for full alerts use `<x-alert>`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'primary'` | Semantic color. For `appearance="dot"` this drives the dot color only; the chip stays neutral. |
| `appearance` | `'solid' \| 'outline' \| 'soft' \| 'base-100' \| 'base-200' \| 'base-300' \| 'dot'` | `'soft'` | Visual style. `solid` filled; `outline` border-only; `soft` tinted bg + colored text; `base-*` surface bg + colored text; `dot` neutral chip with a colored dot prefix. |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg'` | `'md'` | Controls text size, padding, and gap. |
| `icon` | `string \| null` | `null` | Pinion-icons name rendered left of the slot. Ignored when `appearance="dot"` (the dot takes the leading spot). |
| `pill` | `bool` | `false` | Use a fully rounded (`rounded-full`) shape instead of the default selector radius. |

All other attributes pass through to the root element.

## Slots

- **default** — badge label / content, placed after the optional dot or icon.

## Examples

### Basic

```blade
<x-badge>New</x-badge>
<x-badge color="success">Active</x-badge>
<x-badge color="warning" appearance="outline">Pending</x-badge>
```

### Sizes

```blade
<x-badge size="xs">xs</x-badge>
<x-badge size="sm">sm</x-badge>
<x-badge size="md">md</x-badge>
<x-badge size="lg">lg</x-badge>
```

### Pill + icon

```blade
<x-badge pill icon="check" color="success">Verified</x-badge>
```

### Dot indicator

```blade
<x-badge appearance="dot" color="success">Online</x-badge>
<x-badge appearance="dot" color="error">Offline</x-badge>
```

### Surface-on-surface

```blade
<div class="bg-base-200 p-4">
    <x-badge appearance="base-200" color="info">Tagged</x-badge>
</div>
```

## Class composition

Badge composes classes **inline** in [`src/resources/views/components/badge.blade.php`](../../src/resources/views/components/badge.blade.php) — it predates the Composer pattern used by form components. Class strings are stable across patch versions. Override with `class="..."` (Tailwind classes merge naturally via the attribute bag).

## Related

- [`<x-indicator>`](./indicator.md) — anchors a badge at a corner of arbitrary child content.
- [`<x-alert>`](./alert.md) — full-width feedback message for the same color palette.
- [`<x-kbd>`](./kbd.md) — keyboard-key chip; visually similar but semantic `<kbd>`.

## Notes

- This wrapper does NOT use daisyUI's `badge` class — it builds the chip from scratch so the eight appearances (especially `soft` and `base-*`) stay consistent with `<x-button>` and `<x-card>`.
- `appearance="dot"` is the only variant where `color` is decoupled from the chip itself — the chip is always `bg-base-100 text-base-content` and only the dot inherits `color`.
- `whitespace-nowrap` is baked in — long labels won't wrap inside the chip; use a wider container if you need multi-line.
