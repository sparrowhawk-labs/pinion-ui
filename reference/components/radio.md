# x-radio

Single radio input with an inline label and optional description. Wraps a native `<input type="radio">` (kept `sr-only`) and renders a custom visual circle with a CSS-only checked dot — no JS. Pair multiple `<x-radio>` with the same `name` for a manual group, or use [`<x-radio-group>`](./radio-group.md) which renders them for you.

**Playground page**: [`pinion-ui-playground/resources/views/pages/radio.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/radio.blade.php) — full variant matrix and live demos.

## When to use

- One mutually-exclusive choice in a set — payment method, role, plan tier.
- When you need to lay radios out manually (mixed with other markup) rather than using the array-driven `<x-radio-group>`.
- For independent booleans use [`<x-checkbox>`](./checkbox.md); for switch-style booleans use [`<x-toggle>`](./toggle.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string \| null` | `null` | Form field name. Radios sharing a `name` form one mutually-exclusive group. |
| `value` | `mixed` | `null` | Native `value` attribute — submitted when this radio is selected. |
| `label` | `string \| null` | `null` | Inline label text. Falls back to the default slot if both are absent. |
| `description` | `string \| null` | `null` | Secondary text rendered below the label (indented to align). |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'primary'` | Drives the checked-state color. |
| `appearance` | `'solid' \| 'soft' \| 'base-100' \| 'base-200' \| 'base-300'` | `'solid'` | Visual style of the circle. `solid` fills on check, `soft` tinted, `base-*` matches a surface tone. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Circle / label size. |
| `error` | `string \| null` | `null` | When truthy, flips the visual + label to the `error` color. |
| `disabled` | `bool` | `false` | Native `disabled` + visual dim. |
| `checked` | `bool` | `false` | Native `checked` attribute (initial state). Use `wire:model` / `x-model` for two-way binding. |

All other attributes pass through to the `<input type="radio">` (e.g. `wire:model`, `x-model`, `data-*`).

## Slots

- **default** — alternative to the `label` prop (e.g. when you need markup inside the label).

## Examples

### Manual group

```blade
<div class="flex flex-col gap-2">
    <x-radio name="plan" value="free"  label="Free" checked />
    <x-radio name="plan" value="pro"   label="Pro" />
    <x-radio name="plan" value="team"  label="Team" description="Up to 5 seats" />
</div>
```

### Color × appearance

```blade
<x-radio name="status" value="ok"  color="success" appearance="solid" label="OK" checked />
<x-radio name="status" value="bad" color="error"   appearance="soft"  label="Bad" />
```

### Disabled

```blade
<x-radio name="tier" value="legacy" label="Legacy plan (closed)" disabled />
```

## Class composition

Class strings live in [`src/Compose/RadioComposer.php`](../../src/Compose/RadioComposer.php) and the blade renders only the resulting dict. Keys: `wrapper`, `row`, `input` (`peer sr-only`), `visualBox`, `dot`, `label`, `description`.

The checked dot scales in via `peer-checked:[&_.xy-dot]:scale-100` against the `sr-only` input — no JS.

## Related

- [`<x-radio-group>`](./radio-group.md) — array / slot driven wrapper that renders many `<x-radio>` with shared name + label + error.
- [`<x-checkbox>`](./checkbox.md) — independent boolean.
- [`<x-toggle>`](./toggle.md) — switch-style boolean.

## Notes

- The auto-generated `id` combines `name`, `value`, and `uniqid()` so manual groups don't collide; pass an explicit `id="..."` to override.
- `appearance="soft"` with `color="neutral"` uses `base-content` rather than the (invisible) `neutral` tone for contrast — same trick `<x-checkbox>` and `<x-toggle>` use.
- The visual is fully CSS-driven; there's no Alpine on this component.
