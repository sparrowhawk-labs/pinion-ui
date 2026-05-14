# x-textarea

Multi-line text input that shares the same field shell as `<x-input>` (color × appearance × size). Adds optional autoresize, a character counter, and a `maxlength` cap. No icons / prefix-suffix — keep textareas plain.

**Playground page**: [`pinion-ui-playground/resources/views/pages/textarea.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/textarea.blade.php) — full variant matrix and live demos.

## When to use

- Any multi-line text entry — bio, message body, comment, description.
- When you want the field to grow with content (`autoresize`) or show typed characters (`counter` / `maxlength`).
- For single-line text use [`<x-input>`](./input.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string \| null` | `null` | Form field name. Also seeds the auto-generated `id`. |
| `label` | `string \| null` | `null` | Field label. Rendered above the field. |
| `hint` | `string \| null` | `null` | Helper text below the field. Hidden while `error` is set. |
| `error` | `string \| null` | `null` | Error message below the field. Overrides `hint` and flips to the `error` color. |
| `cornerHint` | `string \| null` | `null` | Small right-aligned text on the label row (e.g. "Optional"). |
| `color` | `'neutral' \| 'primary' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'neutral'` | Drives the field's focus / border accent. |
| `appearance` | `'outline' \| 'soft' \| 'underline' \| 'ghost'` | `'outline'` | Field shell style — same set as `<x-input>`. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Text size / padding. Height is governed by `rows` and content. |
| `rows` | `int` | `3` | Native `rows` attribute — initial visible line count. |
| `maxlength` | `int \| null` | `null` | Native `maxlength`. When set, the counter (right-aligned hint) shows `count / max`. |
| `counter` | `bool` | `false` | Show a typed-character counter even without `maxlength`. |
| `autoresize` | `bool` | `false` | Grow with content. Disables manual resize handle (`resize-none`). |
| `required` | `bool` | `false` | Native `required` attribute. Adds a red `*` next to the label. |
| `disabled` | `bool` | `false` | Native `disabled` + visual dim. |
| `readonly` | `bool` | `false` | Native `readonly` attribute. |

All other attributes pass through to the `<textarea>` (e.g. `placeholder`, `wire:model`, `x-model`).

## Slots

- **default** — initial value (rendered as the textarea's text content). Use this OR `wire:model` / `value`.

## Examples

### Basic

```blade
<x-textarea name="bio" label="Bio" placeholder="Tell us about yourself" />
```

### With character counter

```blade
<x-textarea name="tweet" label="Message" :maxlength="280" />
```

### Autoresize

```blade
<x-textarea name="notes" label="Notes" autoresize :rows="2" />
```

### Error state

```blade
<x-textarea name="bio" label="Bio" :error="$errors->first('bio')" />
```

## Class composition

Class strings live in [`src/Compose/TextareaComposer.php`](../../src/Compose/TextareaComposer.php) and the blade renders only the resulting dict. Keys: `wrapper`, `textareaClass`, `labelColor`, `hintColor`. The shell variants are shared with `<x-input>` via `FieldVariants`.

## Related

- [`<x-input>`](./input.md) — single-line counterpart with the same shell.
- [`<x-select>`](./select.md) / [`<x-file-upload>`](./file-upload.md) — siblings sharing the field-shell token system.

## Notes

- The component mounts a small Alpine block only when `autoresize` or counter (`counter` / `maxlength`) is enabled. Plain textareas render without Alpine.
- `autoresize` flips the textarea to `resize-none overflow-hidden` so the JS-managed height isn't fought by the manual resize handle.
- `counter` and `maxlength` independently activate the counter UI — `maxlength` alone is enough.
