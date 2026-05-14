# x-button

Primary interactive control for actions. Renders `<button>` by default; pass `href` to render as `<a>`. Eight semantic colors × seven appearances × four sizes, plus loading/disabled states and optional left/right icons.

**Playground page**: [`pinion-ui-playground/resources/views/pages/button.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/button.blade.php) — full variant matrix and live demos.

## When to use

- Triggering an action — submit, save, dismiss, open a modal.
- Navigating *as a button* — set `href` to render `<a>` while keeping button styling.
- For inline text-style navigation prefer plain `<a>` or `<x-menu-item>`; for breadcrumbs use `<x-breadcrumb>`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'primary'` | daisyUI semantic color. Drives bg/border/text/focus-ring color according to `appearance`. |
| `appearance` | `'solid' \| 'outline' \| 'soft' \| 'ghost' \| 'link' \| 'base-100' \| 'base-200' \| 'base-300'` | `'solid'` | Visual style. `solid` filled; `outline` border-only with invert-on-hover; `soft` tinted bg, no border; `ghost` transparent + base-200 hover; `link` underline-on-hover; `base-*` surface buttons that sit on matching surface containers. |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg'` | `'md'` | Controls height / horizontal padding / text size via `--h-field-*`, `--px-field-*`, `--text-field-*` tune tokens. |
| `loading` | `bool` | `false` | Shows spinner left of the slot and disables interaction. Replaces `icon` while active. |
| `disabled` | `bool` | `false` | Disables interaction (visual + native `disabled` attr when rendered as `<button>`). |
| `as` | `string` | `'button'` | HTML tag when `href` is not set. e.g. `as="div"` for non-form contexts. |
| `href` | `string \| null` | `null` | If set, the element renders as `<a href>` regardless of `as`. |
| `icon` | `string \| null` | `null` | Pinion-icons name rendered left of the slot. Hidden while `loading`. |
| `iconRight` | `string \| null` | `null` | Pinion-icons name rendered right of the slot. Hidden while `loading`. |

All other attributes pass through to the root element (`type`, `wire:click`, `@click`, etc.).

## Slots

- **default** — button label / content, placed between the optional left and right icons.

## Examples

### Basic

```blade
<x-button>Save</x-button>
<x-button color="success">Confirm</x-button>
<x-button appearance="outline" color="error">Delete</x-button>
```

### As link

```blade
<x-button href="/docs" appearance="link">Read docs</x-button>
```

### With icons (requires `sparrowhawk-labs/pinion-icons`)

```blade
<x-button icon="check" color="success">Confirm</x-button>
<x-button iconRight="arrow-right">Next</x-button>
```

### Loading state

```blade
<x-button loading>Saving…</x-button>
```

### Surface-on-surface (`base-*` appearances)

`base-100` / `base-200` / `base-300` buttons keep the parent surface tone and use a colored text/focus-ring. Useful inside cards or sidebars to avoid heavy filled buttons.

```blade
<div class="bg-base-100 p-4">
    <x-button appearance="base-100" color="primary">Action</x-button>
</div>
```

## Class composition

Button composes classes **inline** in [`src/resources/views/components/button.blade.php`](../../src/resources/views/components/button.blade.php) — it predates the Composer pattern used by form components. Class strings are stable across patch versions. Override with `class="..."` (Tailwind classes merge naturally via the attribute bag).

## Related

- [`<x-button-group>`](./button-group.md) — joined group of buttons sharing borders.
- [`<x-kbd>`](./kbd.md) — keyboard key display, often used inside button slots for shortcuts.
- `<x-i>` (from [`sparrowhawk-labs/pinion-icons`](https://github.com/sparrowhawk-labs/pinion-icons)) — the icon component used by `icon` / `iconRight`.

## Notes

- `loading` and `disabled` both add `opacity-50 cursor-not-allowed pointer-events-none`. `disabled` also sets the native `disabled` attribute when the tag is `<button>`.
- Focus ring color tracks `color` — `focus-visible:ring-{color}`.
- All `ghost-*` variants collapse to the same `text-base-content` + `hover:bg-base-200` regardless of `color`; only the focus ring varies.
