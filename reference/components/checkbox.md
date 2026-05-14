# x-checkbox

Single checkbox with an inline label and optional description. Wraps a native `<input type="checkbox">` (kept `sr-only`) and renders a custom visual box so the check / indeterminate marks can be styled consistently across browsers.

**Playground page**: [`pinion-ui-playground/resources/views/pages/checkbox.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/checkbox.blade.php) — full variant matrix and live demos.

## When to use

- Boolean opt-in / opt-out — "I agree to terms", "Subscribe to updates".
- A small fixed multi-select rendered inline on a form surface (group several `<x-checkbox>` with the same `name[]`).
- For a single on/off switch with a state-y feel, prefer [`<x-toggle>`](./toggle.md). For mutually-exclusive choices use [`<x-radio>`](./radio.md) / [`<x-radio-group>`](./radio-group.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string \| null` | `null` | Form field name. Also seeds the auto-generated `id`. |
| `value` | `mixed` | `null` | Native `value` attribute. Required when grouping multiple checkboxes under one `name[]`. |
| `label` | `string \| null` | `null` | Inline label text. Falls back to the default slot if both are absent. |
| `description` | `string \| null` | `null` | Secondary text rendered below the label (and indented to align with it). |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'primary'` | Drives the checked-state color. |
| `appearance` | `'soft' \| 'solid' \| 'base-100' \| 'base-200' \| 'base-300'` | `'soft'` | Visual style of the box. `soft` tinted, `solid` fills with `color` on check, `base-*` matches a surface tone. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Box / label size. |
| `error` | `string \| null` | `null` | When truthy, flips the visual + label to the `error` color. (No message is rendered — combine with a wrapper / `<x-radio-group>`-style field if you need one.) |
| `indeterminate` | `bool` | `false` | Mount with `indeterminate = true` (the visual shows a dash instead of a check). Set via a tiny Alpine `x-init`. |
| `disabled` | `bool` | `false` | Native `disabled` + visual dim. |
| `checked` | `bool` | `false` | Native `checked` attribute (initial state). Use `wire:model` / `x-model` for two-way binding. |

All other attributes pass through to the `<input type="checkbox">` (e.g. `wire:model`, `x-model`, `data-*`).

## Slots

- **default** — alternative to the `label` prop (e.g. when you need markup inside the label).

## Examples

### Basic

```blade
<x-checkbox name="terms" label="I agree to the terms" />
```

### With description

```blade
<x-checkbox name="newsletter" label="Subscribe" description="One weekly digest. Unsubscribe anytime." />
```

### Color × appearance

```blade
<x-checkbox color="success" appearance="solid" label="Confirmed" checked />
<x-checkbox color="error"   appearance="soft"  label="Mark as failed" />
```

### Indeterminate (parent of a partial selection)

```blade
<x-checkbox label="Select all" indeterminate />
```

### Slot for rich label

```blade
<x-checkbox name="agree">
    I have read the <a href="/terms" class="link link-primary">terms</a>
</x-checkbox>
```

## Class composition

Class strings live in [`src/Compose/CheckboxComposer.php`](../../src/Compose/CheckboxComposer.php) and the blade renders only the resulting dict. Keys: `wrapper`, `row`, `input` (`peer sr-only`), `visualBox`, `checkmark`, `indeterminate`, `label`, `description`.

The visual box uses `peer-checked:` / `peer-indeterminate:` against the `sr-only` input to drive state, so no JS is required beyond the optional `indeterminate` initializer.

## Related

- [`<x-radio>`](./radio.md) — single mutually-exclusive equivalent.
- [`<x-toggle>`](./toggle.md) — switch-style boolean.
- [`<x-radio-group>`](./radio-group.md) — grouped pattern; checkboxes don't have an equivalent wrapper (group manually).

## Notes

- Color × appearance permutations are pre-listed in the Tailwind safelist (`@source inline` in `pinion-ui.css`), so interpolating `bg-{color}` etc. is safe.
- `appearance="soft"` with `color="neutral"` uses `base-content` rather than the (invisible) `neutral` tone for contrast — same trick `<x-radio>` and `<x-toggle>` use.
- For a counted multi-select with a hint and error message, wrap several `<x-checkbox>` in your own fieldset.
