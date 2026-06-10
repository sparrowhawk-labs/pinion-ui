# x-select

Picker for one or more values. Renders a native `<select>` by default — same field shell as `<x-input>` — and opts into an Alpine-driven custom UI (chips for multi-select, styled dropdown) with `custom`. Supports listbox mode (`rows` / `multiple`) and floating label.

**Playground page**: [`pinion-ui-playground/resources/views/pages/select.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/select.blade.php) — full variant matrix and live demos.

## When to use

- Choosing from a fixed list of values — single-select dropdown, multi-select, or listbox.
- When you need accessible chip-style multi-select that still posts a real `<select multiple>` to the server (`custom multiple`).
- For free-form text use [`<x-input>`](./input.md); for radio-style mutually-exclusive UI use [`<x-radio-group>`](./radio-group.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string \| null` | `null` | Form field name. `multiple` mode appends `[]` automatically. |
| `label` | `string \| null` | `null` | Field label. Rendered above the field, or floating when `floating` is true. |
| `hint` | `string \| null` | `null` | Helper text below the field. |
| `error` | `string \| null` | `null` | Error message below the field. Overrides `hint` and flips to `error` color. |
| `cornerHint` | `string \| null` | `null` | Small right-aligned text on the label row. |
| `placeholder` | `string \| null` | `null` | Disabled / hidden first option shown when nothing is selected. Ignored in `multiple` mode. |
| `color` | `'neutral' \| 'primary' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'neutral'` | Drives focus / border accent. |
| `appearance` | `'outline' \| 'soft' \| 'underline' \| 'ghost'` | `'outline'` | Field shell — same set as `<x-input>`. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Field height / text size. |
| `multiple` | `bool` | `false` | Multi-select. Native list (with `rows`) or chip-based when combined with `custom`. |
| `rows` | `int \| null` | `null` | When set, renders as a native listbox with this many visible rows (sets `size=` on `<select>`). |
| `floating` | `bool` | `false` | Floating-label mode. Ignored in listbox or `custom` mode. |
| `required` | `bool` | `false` | Native `required` attribute. Adds a red `*` next to the label. |
| `disabled` | `bool` | `false` | Native `disabled` + visual dim. |
| `custom` | `bool` | `true` | Replace the native trigger with an Alpine-driven UI (chips for multi-select, themed dropdown). The native `<select>` stays in the DOM (`sr-only`) so form submission and screen readers continue to work. |

All other attributes pass through to the `<select>` (e.g. `wire:model`, `x-model`, `data-*`).

## Slots

- **default** — `<option>` elements. Anything you'd put inside a normal `<select>` (including `<optgroup>`) is fine.

## Examples

### Basic

```blade
<x-select name="country" label="Country" placeholder="Choose…">
    <option value="jp">Japan</option>
    <option value="us">United States</option>
    <option value="de">Germany</option>
</x-select>
```

### Listbox (multiple rows visible)

```blade
<x-select name="tags" label="Tags" multiple :rows="5">
    <option value="design">Design</option>
    <option value="code">Code</option>
    <option value="ops">Ops</option>
</x-select>
```

### Custom (Alpine) multi-select with chips

```blade
<x-select name="skills" label="Skills" multiple custom placeholder="Pick a few">
    <option value="php">PHP</option>
    <option value="ts">TypeScript</option>
    <option value="rust">Rust</option>
</x-select>
```

### Floating label

```blade
<x-select name="role" label="Role" floating>
    <option value="admin">Admin</option>
    <option value="user">User</option>
</x-select>
```

## Class composition

Class strings live in [`src/Compose/SelectComposer.php`](../../src/Compose/SelectComposer.php) and the blade renders only the resulting dict. Key groups:

- Native mode: `wrapper`, `selectClass`, `floatingLabel`, `labelColor`, `hintColor`.
- Custom mode: `trigger`, `triggerInner`, `triggerText`, `placeholder`, `chip`, `chipRemove`, `chevron`, `dropdown`, `option`, `optionSelected`, `optionDisabled`, `optionCheck`.

Shell variants come from `FieldVariants`, shared with `<x-input>` / `<x-textarea>` / `<x-file-upload>`.

## Related

- [`<x-input>`](./input.md) — same shell, free-form text.
- [`<x-radio-group>`](./radio-group.md) — inline mutually-exclusive choice (small lists).
- [`<x-checkbox>`](./checkbox.md) — small fixed multi-select on a form surface.

## Notes

- `custom` + `floating` is rejected (the floating-label pattern depends on the visible native `<select>`); the blade falls back to the standard custom UI.
- In `multiple` mode the `name` is automatically suffixed with `[]` so Laravel receives an array.
- Custom mode keeps the native `<select>` `sr-only` for screen readers and form submission; an Alpine `init()` mirrors the option list at mount.
