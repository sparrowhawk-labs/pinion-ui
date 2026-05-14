# x-input

Text input with label, hint/error, prefix/suffix slots, optional leading/trailing icons, and an optional floating-label variant. Wraps a native `<input>` so every standard `type` value (`text`, `email`, `password`, `number`, `search`, `date`, …) and all browser-side validation just work.

**Playground page**: [`pinion-ui-playground/resources/views/pages/input.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/input.blade.php) — full variant matrix and live demos.

## When to use

- Any single-line text entry — form field, search box, inline editor.
- When you need an attached prefix/suffix (e.g. `$` on amount, `.com` on domain) or a leading/trailing icon.
- For multi-line text use [`<x-textarea>`](./textarea.md); for a fixed value list use [`<x-select>`](./select.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `type` | `string` | `'text'` | Forwarded to native `<input type>` — any HTML input type. |
| `name` | `string \| null` | `null` | Form field name. Also seeds the auto-generated `id`. |
| `label` | `string \| null` | `null` | Field label. Rendered above the field, or floating inside the field when `floating` is true. |
| `hint` | `string \| null` | `null` | Helper text below the field. Hidden while `error` is set. |
| `error` | `string \| null` | `null` | Error message below the field. Overrides `hint` and flips the field to the `error` color. |
| `cornerHint` | `string \| null` | `null` | Small right-aligned text on the label row (e.g. "Optional"). Only shown with a non-floating `label`. |
| `color` | `'neutral' \| 'primary' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'neutral'` | Drives the field's focus / border accent via `FieldVariants`. |
| `appearance` | `'outline' \| 'soft' \| 'underline' \| 'ghost'` | `'outline'` | Field shell style. `outline` border-only, `soft` tinted bg, `underline` bottom-border only, `ghost` transparent + hover surface. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Field height / padding / text size via `--h-field-*`, `--px-input-*`, `--text-field-*` tune tokens. |
| `iconLeft` | `string \| null` | `null` | Pinion-icons name rendered inside the input, left side. |
| `iconRight` | `string \| null` | `null` | Pinion-icons name rendered inside the input, right side. |
| `prefix` | `string \| null` | `null` | Attached addon on the left of the field (e.g. `$`). Separated by a divider. |
| `suffix` | `string \| null` | `null` | Attached addon on the right of the field (e.g. `.com`). |
| `floating` | `bool` | `false` | Floating-label mode — label sits inside the field and animates up on focus / value. |
| `required` | `bool` | `false` | Native `required` attribute. Adds a red `*` next to the label. |
| `disabled` | `bool` | `false` | Native `disabled` + visual dim. |
| `readonly` | `bool` | `false` | Native `readonly` attribute. |

All other attributes pass through to the `<input>` (e.g. `placeholder`, `min`, `max`, `pattern`, `wire:model`, `x-model`).

## Slots

- **append** — markup appended after `suffix`, inside the field shell at full height. Useful for attaching a `<x-button>` (e.g. "Copy", "Apply").

## Examples

### Basic

```blade
<x-input name="email" type="email" label="Email" placeholder="you@example.com" />
```

### With prefix, suffix, and hint

```blade
<x-input name="amount" type="number" prefix="$" suffix="USD" hint="Tax excluded" />
```

### Floating label

```blade
<x-input name="title" label="Title" floating />
```

### Error state

```blade
<x-input name="email" label="Email" :error="$errors->first('email')" />
```

### Appended button

```blade
<x-input name="invite" label="Invite link" readonly value="https://...">
    <x-slot:append>
        <x-button class="rounded-l-none" color="primary">Copy</x-button>
    </x-slot:append>
</x-input>
```

## Class composition

Class strings live in [`src/Compose/InputComposer.php`](../../src/Compose/InputComposer.php) and the blade renders only the resulting dict. Keys: `wrapper`, `inputClass`, `addonBase`, `floatingLabel`, `labelColor`, `hintColor`. The field shell (color × appearance) is shared with `<x-textarea>`, `<x-select>`, and `<x-file-upload>` via `FieldVariants`.

## Related

- [`<x-textarea>`](./textarea.md) — multi-line counterpart with identical shell tokens.
- [`<x-select>`](./select.md) — same shell, single-value picker.
- [`<x-file-upload>`](./file-upload.md) — same shell, file picker / dropzone.
- `<x-i>` (from [`sparrowhawk-labs/pinion-icons`](https://github.com/sparrowhawk-labs/pinion-icons)) — used by `iconLeft` / `iconRight`.

## Notes

- `error` takes priority over `hint` and `color` — passing both shows the error and the field switches to the `error` palette.
- The auto-generated `id` includes `uniqid()` to keep label `for=` unique; pass an explicit `id="..."` attribute to override.
- Floating label requires the input to have a placeholder for the CSS-only state machine — the blade injects `placeholder=" "` automatically when `floating` + `label` are set.
