# x-alert

Inline message box for status / feedback. Eight semantic colors × eleven appearances, optional title, dismissible, and an auto-icon that adapts to the color (success → check-circle, warning → danger-triangle, error → close-circle, otherwise info-circle).

**Playground page**: [`pinion-ui-playground/resources/views/pages/alert.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/alert.blade.php) — full appearance × color matrix.

## When to use

- Surface a status message inline with content — success after save, validation error, info banner.
- For transient pop-up notifications use [`<x-notification-system>`](./notification-system.md) instead — alerts stay until dismissed (or persist for the page lifetime).
- For tiny inline labels use [`<x-badge>`](./badge.md); for full-page banners just compose `<x-alert>` at the top of the layout.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'info'` | Semantic color. Drives bg/border/text/icon depending on `appearance`. Also picks the default icon when `icon` is null. |
| `appearance` | `'solid' \| 'outline' \| 'soft' \| 'vivid' \| 'ghost' \| 'link' \| 'base-100' \| 'base-200' \| 'base-300' \| 'bordered-top' \| 'bordered-left'` | `'bordered-left'` | Visual style. `bordered-left` (default) is a surface bg with a thick left accent bar — calm and readable. `solid` fills; `outline` border-only; `soft` is a low-saturation tint with `text-base-content` for calm readability; `vivid` is the same tint shell but with `color-mix(color, base-content 20%)` text for a saturated identity; `base-*` are surface variants that sit on matching containers. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Field-token sizing — uses `--h-field-*`, `--px-field-*`, `--text-field-*` so the alert lines up with `<x-input>` / `<x-button>` at the same size. |
| `title` | `string \| null` | `null` | Optional bold heading above the slot body. When omitted the slot fills the whole right column. |
| `icon` | `string \| null` | *auto* | Pinion-icon name. When `null` the icon is auto-picked from `color`. Pass an explicit name to override (e.g. `icon="bell"`). |
| `dismissible` | `bool` | `false` | Adds an Alpine-driven × button on the right that fades the alert out. Internally uses `x-data="{ show: true }"` so it stands alone with no parent scope. |

All other attributes pass through to the root `<div>`.

## Slots

- **default** — the alert body / message text. Rendered with `opacity-80` when `title` is set, full opacity otherwise.

## Examples

### Basic

```blade
<x-alert color="info" title="Heads up">A new version is available.</x-alert>
<x-alert color="success" title="Saved">Your changes were saved.</x-alert>
<x-alert color="warning">Storage is at 80%.</x-alert>
<x-alert color="error" title="Connection failed" dismissible>
    Please retry in a moment.
</x-alert>
```

### Surface match (`base-*`)

`base-100` / `base-200` / `base-300` alerts use a neutral surface bg with the icon carrying the color identity. Drop one into a matching container and it blends in.

```blade
<div class="bg-base-200 p-4">
    <x-alert color="info" appearance="base-200" title="Note">
        Sits flush on a base-200 surface.
    </x-alert>
</div>
```

### Soft vs vivid

Same `bg-{color}/15 + border-{color}/40` shell, different text strategy. `soft` uses `text-base-content` for calm readability, `vivid` uses a saturated `color-mix` hue.

```blade
<x-alert color="success" appearance="soft" title="Soft">Calm body text.</x-alert>
<x-alert color="success" appearance="vivid" title="Vivid">Saturated body text.</x-alert>
```

### Custom icon

```blade
<x-alert color="info" icon="bell" title="Reminder">
    The deploy window opens at 18:00.
</x-alert>
```

## Class composition

Alert composes its class strings **inline** in [`src/resources/views/components/alert.blade.php`](../../src/resources/views/components/alert.blade.php) — it predates the `Composer` pattern used by form components. The variant table is exhaustive (`{appearance}-{color}`) for predictability. Override with `class="..."` (Tailwind merges via the attribute bag).

## Related

- [`<x-notification-system>`](./notification-system.md) — transient toasts dispatched via `$dispatch('notify', …)`.
- [`<x-badge>`](./badge.md) — tiny inline labels with similar color/appearance vocabulary.
- `<x-i>` (from [`sparrowhawk-labs/pinion-icons`](https://github.com/sparrowhawk-labs/pinion-icons)) — provides the auto-icon.

## Notes

- The auto-icon map only branches on `success` / `warning` / `error`; every other color uses `info-circle`. Pass `icon` explicitly if you want something else.
- `dismissible` creates its own Alpine scope (`x-data="{ show: true }"`), so the alert closes locally with no event plumbing required.
- `appearance="bordered-left"` is the project default — calmer than daisyUI's stock `alert-soft` and easier to scan in long forms.
- `tune-border` (the package's per-tune border weight CSS var) is applied on every variant, so border thickness tracks the active `data-tune`.
