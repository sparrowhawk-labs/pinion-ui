# x-toggle

iOS-style switch for boolean state. Renders a native `<input type="checkbox" role="switch">` (kept `sr-only`) plus a custom track + thumb driven entirely by `peer-checked:` selectors. Optional `ON / OFF` text labels inside the track.

**Playground page**: [`pinion-ui-playground/resources/views/pages/toggle.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/toggle.blade.php) — full variant matrix and live demos.

## When to use

- A single binary on/off setting that takes effect immediately — feature flag, notification preference, dark-mode toggle.
- For an opt-in checkbox tied to form submission (terms agreement, mailing list) prefer [`<x-checkbox>`](./checkbox.md) — checkboxes read as "select this option", toggles read as "this thing is on/off".
- For mutually-exclusive choices use [`<x-radio>`](./radio.md) / [`<x-radio-group>`](./radio-group.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string \| null` | `null` | Form field name. Also seeds the auto-generated `id`. |
| `value` | `mixed` | `null` | Native `value` attribute. |
| `label` | `string \| null` | `null` | Inline label text. Falls back to the default slot. |
| `description` | `string \| null` | `null` | Secondary text rendered below the row. |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'primary'` | Drives the checked-state color (rail in `solid`, thumb in `soft`). |
| `appearance` | `'solid' \| 'soft'` | `'solid'` | `solid` flows color into the rail and keeps the thumb white. `soft` keeps the rail muted and flows color into the thumb. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Track / thumb size. |
| `error` | `string \| null` | `null` | When truthy, flips the visual + label to the `error` color. |
| `disabled` | `bool` | `false` | Native `disabled` + visual dim. |
| `checked` | `bool` | `false` | Native `checked` attribute (initial state). Use `wire:model` / `x-model` for two-way binding. |
| `stateLabel` | `bool` | `false` | Render tiny `ON` / `OFF` labels inside the track. Requires `appearance="solid"` and `size` of `md` or `lg` (silently ignored otherwise). |

All other attributes pass through to the `<input>` (e.g. `wire:model`, `x-model`, `data-*`).

## Slots

- **default** — alternative to the `label` prop.

## Examples

### Basic

```blade
<x-toggle name="notifications" label="Email notifications" checked />
```

### Soft appearance (color flows into the thumb)

```blade
<x-toggle name="darkmode" appearance="soft" color="primary" label="Dark mode" />
```

### With ON / OFF state label

```blade
<x-toggle name="live" label="Broadcasting" stateLabel checked />
```

### Disabled + description

```blade
<x-toggle name="beta" label="Beta features" description="Coming soon" disabled />
```

## Class composition

Class strings live in [`src/Compose/ToggleComposer.php`](../../src/Compose/ToggleComposer.php) and the blade renders only the resulting dict. Keys: `wrapper`, `row`, `input` (`peer sr-only`), `track`, `thumb`, `stateOn`, `stateOff`, `label`, `description`.

The track / thumb / state-label state changes are driven from the track itself via `peer-checked:[&_.xy-thumb]:translate-x-*` (and friends) — the thumb / labels live *inside* the track, not as input siblings, so the descendant selector reaches them.

## Related

- [`<x-checkbox>`](./checkbox.md) — opt-in style boolean.
- [`<x-radio>`](./radio.md) / [`<x-radio-group>`](./radio-group.md) — mutually-exclusive choice.

## Notes

- `appearance` is intentionally limited to `solid` / `soft`. `base-100` / `base-200` / `base-300` were dropped — a switch's track can't simultaneously be a surface tone and a state indicator.
- `stateLabel` only renders when `appearance="solid"` AND `size` is `md` or `lg`. The soft track lacks color contrast for the labels; `sm` lacks room.
- Travel distances are precomputed per size to avoid an extra layer of CSS variables: `sm: translate-x-4`, `md: translate-x-5`, `lg: translate-x-6`.
