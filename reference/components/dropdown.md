# x-dropdown

Click-to-open menu surface — a trigger (button or arbitrary slot) plus a floating panel of items. Built on a small Alpine `{ open }` state with click-outside and `Escape`-to-close baked in. Four placement options, three trigger sizes, and a configurable panel width. The body slot is freeform — typically a stack of [`<x-menu-item>`](./menu-item.md).

**Playground page**: [`pinion-ui-playground/resources/views/pages/dropdown.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/dropdown.blade.php) — full variant matrix and live demos.

## When to use

- Compact menu of actions or links anchored to a single trigger — account menu, row-action overflow, filter selector.
- When you want hover/click open state managed for you, including outside-click and `Escape` dismissal.
- For longer / searchable lists, prefer a dedicated combobox or modal. For top-of-page navigation prefer [`<x-sidebar>`](./sidebar.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `label` | `string \| null` | `null` | Convenience: render a built-in trigger button with this label and a rotating chevron. Ignored if the `trigger` slot is supplied. |
| `position` | `'bottom-end' \| 'bottom-start' \| 'top-end' \| 'top-start'` | `'bottom-end'` | Panel placement relative to the trigger. `*-end` aligns the panel's right edge to the trigger; `*-start` aligns the left edge. `top-*` opens upward. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Size of the built-in trigger button (height / padding / text size via tune tokens). No effect when using the `trigger` slot. |
| `width` | `string` | `'w-52'` | Tailwind width class applied to the panel. Pass any class string — `w-64`, `w-80`, `min-w-[12rem] w-max`, etc. |

All other attributes pass through to the root `<div>`.

⚠ `position` is a static CSS placement — there is no viewport-collision auto-flip. A `top-*` dropdown near the top of a short/tightly-padded container will render past the container's edge (same for `bottom-*` near a container's bottom). Reserve enough clearance in the container for the panel's actual height, or pick a position that has room.

## Slots

- **trigger** *(optional)* — custom trigger element. When supplied, `label` is ignored and the wrapper handles the `@click` toggle for you. Useful for avatar triggers, icon-only buttons, or custom-styled buttons.
- **default** — panel body. Typically a stack of `<x-menu-item>` rows; any markup is allowed.

## Examples

### Basic, label trigger

```blade
<x-dropdown label="Account">
    <x-menu-item href="/profile">Profile</x-menu-item>
    <x-menu-item href="/settings">Settings</x-menu-item>
    <x-menu-item @click="logout()">Sign out</x-menu-item>
</x-dropdown>
```

### Custom trigger slot

```blade
<x-dropdown position="bottom-start" width="w-64">
    <x-slot:trigger>
        <x-avatar src="/me.jpg" />
    </x-slot:trigger>

    <x-menu-item href="/profile">Profile</x-menu-item>
    <x-menu-item href="/settings">Settings</x-menu-item>
</x-dropdown>
```

### Open upward, end-aligned

```blade
<x-dropdown label="Actions" position="top-end">
    <x-menu-item icon="pencil" @click="edit()">Edit</x-menu-item>
    <x-menu-item icon="trash"  @click="del()" >Delete</x-menu-item>
</x-dropdown>
```

### Wider panel

```blade
<x-dropdown label="Filter" width="w-80">
    <div class="p-lg space-y-2">
        <x-input label="Search" />
        <x-checkbox label="Active only" />
    </div>
</x-dropdown>
```

## Class composition

Class strings come from [`DropdownComposer::compose($props)`](../../src/Compose/DropdownComposer.php) — keys: `root` (relative wrapper), `trigger` (the built-in button), `menu` (the floating panel), `icon` (the chevron). The panel uses `z-40`, `bg-base-100`, `rounded-[var(--radius-box)]`, and `shadow-lg`. Placement is a `match` on `position` returning `top-full`/`bottom-full` + `left-0`/`right-0` pairs.

## Related

- [`<x-menu-item>`](./menu-item.md) — the standard row component for the panel body.
- [`<x-sidebar>`](./sidebar.md) — for fuller off-canvas navigation.
- [`<x-tooltip>`](./tooltip.md) — for hover-only informational popovers.

## Notes

- The dropdown closes automatically on outside click (`@click.outside`) and on `Escape` (`@keydown.escape.window`).
- The panel uses `x-show` + `x-cloak` with a 150 ms scale/opacity transition. The `[x-cloak]{display:none}` rule that makes this effective is bundled in the `pinion-ui.css` preset (v0.7.1+); a duplicate rule in your own CSS is harmless.
- When using the `trigger` slot, the wrapper inserts the slot inside a `<div @click>` — make sure the inner element doesn't `stopPropagation` on click.
- The chevron on the built-in trigger rotates 180° when open via a bound `:class`.
- The panel does **not** trap focus. If you need a true menu role with arrow-key navigation, layer ARIA attributes onto the items yourself.
