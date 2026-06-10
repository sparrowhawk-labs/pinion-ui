# x-pin-input

OTP / verification-code input — N separate single-character boxes that auto-advance focus on type, backspace back, accept arrow nav, and fill on paste (entire code at once). Behaviour lives in an inline Alpine `x-data` block; the composer returns the box styling.

**Playground page**: [`pinion-ui-playground/resources/views/pages/pin-input.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/pin-input.blade.php) — full variant matrix and live demos.

## When to use

- SMS / email verification codes (6-digit OTP).
- 2FA TOTP entry.
- PIN-style auth where the value is N short characters.
- For a free-form text field, use [`<x-input>`](./input.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string \| null` | `null` | Form field name. When set, a hidden `<input name="...">` carries the combined value so it submits with the form. |
| `label` | `string \| null` | `null` | Visible label above the row. |
| `hint` | `string \| null` | `null` | Helper text below; shadowed by `error`. |
| `error` | `string \| null` | `null` | Error message; switches box border + ring to `error` tones and flips label/hint colours. |
| `length` | `int` | `6` | Number of boxes. |
| `type` | `'numeric' \| 'alphanumeric'` | `'numeric'` | Accepted character set. `numeric` emits `inputmode="numeric"` + `pattern="[0-9]"` (mobile numpad); `alphanumeric` accepts `[0-9a-zA-Z]`. |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg'` | `'md'` | Box width/height/text-size: `xs` = `w-8`, `sm` = `w-10`, `md` = `w-12` + `text-xl`, `lg` = `w-14` + `text-2xl`. |
| `masked` | `bool` | `false` | When `true`, each box renders as `<input type="password">` — input shows as `•`. |
| `value` | `string` | `''` | Initial filled value. First `length` characters seed the boxes. |
| `autofocus` | `bool` | `false` | Focus the first box on mount. |
| `disabled` | `bool` | `false` | Disables every box. |

`wire:`-prefixed attributes are **not** auto-forwarded — the per-box inputs are internal, and the combined value lives in the hidden `name` input. For Livewire model binding, wrap the component in your own Alpine + `wire:model` pattern.

## Slots

None — fully prop-driven.

## Examples

### Basic 6-digit OTP

```blade
<x-pin-input name="otp" label="Verification code" autofocus />
```

### 4-digit PIN with masked entry

```blade
<x-pin-input name="pin" label="PIN" :length="4" masked />
```

### Alphanumeric code

```blade
<x-pin-input name="code" label="Invite code" :length="8" type="alphanumeric" size="sm" />
```

### Pre-filled

```blade
<x-pin-input name="otp" label="Code" :length="6" value="123" hint="Last 3 digits to fill" />
```

### Error state

```blade
<x-pin-input name="otp" label="Verification code" error="Code expired — request a new one" />
```

### Compact xs row inside a card

```blade
<x-pin-input :length="6" size="xs" />
```

## Class composition

See [`src/Compose/PinInputComposer.php`](../../src/Compose/PinInputComposer.php). Returns `wrapper` (`flex items-center gap-2`), `box` (per-box class — width / height / text size / border / focus ring / disabled state), `labelColor`, `hintColor`.

The Alpine `x-data` lives in the Blade — it holds `digits` as an array of strings, exposes a computed `combined`, and provides `onInput / onKeydown / onPaste` handlers wired to each box. The combined value is bound (`x-bind:value="combined"`) to a hidden `<input name>` for form submit.

## Related

- [`<x-input>`](./input.md) — free-form text input; reach for it when the value isn't a fixed-length code.
- [`<x-input-number>`](./input-number.md) — bounded numeric entry with ± buttons (not the same use case but adjacent in the form family).

## Notes

- **Paste handling**: pasting any text into any box triggers the first-box `onPaste` listener, which normalises (filters out non-matching characters) and fills boxes left-to-right. Cursor moves to the box after the last filled digit (or stays on the last box if all are filled).
- **Backspace**: hitting backspace in an empty box jumps focus back to the previous box (the previous box's content is **not** deleted — only its focus is restored, so the user can correct it manually).
- **Arrow keys**: ← / → move focus between adjacent boxes without changing values.
- **`autocomplete="one-time-code"`** is set on the first box only — modern iOS / Android offer the SMS code suggestion above the keyboard when this attribute is present.
- **Form submission**: the visible boxes have no `name` attribute. Only the combined hidden input is submitted, so server-side you receive a single concatenated string.
- The `masked` mode uses `type="password"` — browsers may suppress the SMS-code suggestion in this case. Use `masked` only when the code shouldn't be shoulder-surfable; for normal OTP entry leave it false.
