# x-menu-item

Single navigation row used inside menus, dropdowns, sidebars, and command surfaces. Renders `<button>` by default; pass `href` to render as `<a>`. Supports `active` (highlighted) and `disabled` states plus an optional left icon. Fills the parent width (`w-full`) so it stacks cleanly inside a vertical list.

**Playground page**: shown inside other component demos — see [`pinion-ui-playground/resources/views/pages/dropdown.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/dropdown.blade.php) and [`sidebar.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/sidebar.blade.php).

## When to use

- As a row inside [`<x-dropdown>`](./dropdown.md), [`<x-sidebar>`](./sidebar.md), or any custom menu container.
- When you want consistent hover/active styling for a clickable row that may be either a link (`<a href>`) or an action (`<button>`).
- For full-width inline navigation lists (file trees, settings nav).
- For toolbars / call-to-actions use [`<x-button>`](./button.md) instead — `<x-menu-item>` is intentionally less prominent.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `href` | `string \| null` | `null` | If set, the element renders as `<a href>` instead of `<button>`. |
| `active` | `bool` | `false` | Highlights the row with `bg-primary/10 text-primary font-medium` to indicate the current page or selected item. |
| `disabled` | `bool` | `false` | Greys the row and removes pointer interaction (`opacity-50 cursor-not-allowed`). When rendered as `<button>` the native `disabled` attribute is set. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Row height / horizontal padding / text size via the `--h-field-*`, `--px-field-*`, `--text-field-*` tune tokens. |
| `icon` | `string \| null` | `null` | Pinion-icons name rendered left of the slot as a 16×16 inline icon. |

All other attributes pass through to the root element (`wire:click`, `@click`, `target`, etc.).

## Slots

- **default** — label / content for the row, placed right of the optional icon.

## Examples

### Basic, as button

```blade
<x-menu-item @click="doThing()">Do thing</x-menu-item>
<x-menu-item icon="trash" disabled>Delete</x-menu-item>
```

### As link with active state

```blade
<x-menu-item href="/settings" :active="request()->is('settings')">
    Settings
</x-menu-item>
```

### Inside a dropdown

```blade
<x-dropdown label="Account">
    <x-menu-item icon="user"     href="/profile">Profile</x-menu-item>
    <x-menu-item icon="settings" href="/settings">Settings</x-menu-item>
    <x-menu-item icon="logout"   @click="logout()">Sign out</x-menu-item>
</x-dropdown>
```

### Inside a sidebar nav

```blade
<x-sidebar side="left">
    <x-slot:trigger><x-button>Open</x-button></x-slot:trigger>

    <nav class="flex flex-col gap-0.5">
        <x-menu-item icon="home"     href="/"        active>Dashboard</x-menu-item>
        <x-menu-item icon="folder"   href="/projects">Projects</x-menu-item>
        <x-menu-item icon="users"    href="/team"     >Team</x-menu-item>
    </nav>
</x-sidebar>
```

## Class composition

`<x-menu-item>` composes its classes **inline** in [`src/resources/views/components/menu-item.blade.php`](../../src/resources/views/components/menu-item.blade.php) — it predates the Composer pattern and is small enough not to need one. Size and state classes are computed by a `match` expression in the Blade `@php` block; the result is merged onto the root via `$attributes->merge(['class' => ...])` so user `class="..."` extends it naturally.

## Related

- [`<x-dropdown>`](./dropdown.md) — the most common host for menu items.
- [`<x-sidebar>`](./sidebar.md) — vertical navigation drawer.
- [`<x-button>`](./button.md) — for visually prominent actions.

## Notes

- `active` and `disabled` are mutually exclusive in styling — `active` wins if both are passed.
- When `href` is set the row is always an `<a>`, even with `disabled=true` (no native disabled attribute on anchors — guard against navigation in your handler if needed).
- Uses `min-h-[var(--h-field-*)]` rather than fixed `height`, so multi-line labels expand naturally.
- The icon slot expects an icon name from [`sparrowhawk-labs/pinion-icons`](https://github.com/sparrowhawk-labs/pinion-icons), rendered as `<x-i :type="$icon" class="w-4 h-4 shrink-0" />`.
