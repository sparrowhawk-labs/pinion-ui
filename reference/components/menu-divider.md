# x-menu-divider

Full-bleed separator row for menu panels — a `role="separator"` `<div>` with `my-1 border-t border-base-content/10`. Sits between groups of [`<x-menu-item>`](./menu-item.md) inside [`<x-dropdown>`](./dropdown.md) or a sidebar nav. Deliberately lighter than [`<x-divider>`](./divider.md): no label, no direction, no flex wrapper.

## When to use

- Grouping rows inside a dropdown / menu panel (account · settings | sign out).
- Any vertical list of `<x-menu-item>` that needs a visual break.
- For a labelled rule ("OR") or a vertical bar in a flex row use [`<x-divider>`](./divider.md).

## Props

None. All attributes pass through to the root `<div>`; `class` merges (`$attributes->merge`) — e.g. `class="my-2"` for more breathing room.

## Slots

None.

## Examples

```blade
<x-dropdown label="Account">
    <x-menu-item href="/profile">Profile</x-menu-item>
    <x-menu-item href="/settings">Settings</x-menu-item>
    <x-menu-divider />
    <x-menu-item @click="logout()">Sign out</x-menu-item>
</x-dropdown>
```

## Class composition

Inline in [`src/resources/views/components/menu-divider.blade.php`](../../src/resources/views/components/menu-divider.blade.php) — one class string, no Composer (same as `<x-menu-item>`).

## Related

- [`<x-dropdown>`](./dropdown.md) — the usual host.
- [`<x-menu-item>`](./menu-item.md) — the rows it separates.
- [`<x-divider>`](./divider.md) — labelled / vertical separator for page content.

## Notes

- Reaches both panel edges only because the dropdown panel has **no horizontal padding** (`py-1` only; rows carry their own `px`). If you hand-roll a panel, keep `px-*` off it — otherwise the line stops short of the edges.
