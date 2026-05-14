# x-sidebar

Off-canvas drawer panel that slides in from the left or right. The panel is teleported to `<body>` so it always sits above page content regardless of where you mount the component. Includes a focus trap, `Escape`-to-close, optional backdrop, and a floating close button pinned to the top-right of the panel — independent of the `side` prop.

**Playground page**: [`pinion-ui-playground/resources/views/pages/sidebar.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/sidebar.blade.php) — embeds four iframe demos (`/demo/sidebar-left`, `/demo/sidebar-right`, `/demo/sidebar-with-content`, `/demo/sidebar-no-backdrop`).

## When to use

- Primary site navigation on mobile / narrow viewports.
- Secondary side panels for filters, inspectors, settings, or contextual detail that should overlay the page rather than replace it.
- For lightweight floating menus anchored to a trigger use [`<x-dropdown>`](./dropdown.md).
- For full-screen blocking dialogs use [`<x-modal>`](./modal.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `id` | `string \| null` | `null` | Unique id used for the open/close events (`open-sidebar-{id}` / `close-sidebar-{id}`). When `null`, a random `sidebar_<uniqid>` is generated — supply your own if you need to dispatch events from elsewhere. |
| `side` | `'left' \| 'right'` | `'left'` | Which edge the panel slides in from. The close button stays in the panel's top-right regardless. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Panel width — `sm`=`w-64`, `md`=`w-80`, `lg`=`w-96`. |
| `backdrop` | `bool` | `true` | When `true`, dims the page with `bg-black/50` and captures pointer events. When `false`, the overlay becomes `pointer-events-none` so the page stays interactive while the drawer is open (useful for inspector panels). |
| `closeOnBackdrop` | `bool` | `true` | When `true` (and `backdrop=true`), clicking the dim layer closes the drawer. |
| `escape` | `bool` | `true` | When `true`, pressing `Escape` closes the drawer. |

All other attributes pass through to the root `<div>` (which holds the Alpine state and the trigger slot — **not** the teleported panel).

## Slots

- **trigger** *(optional)* — element that opens the drawer on click. When omitted, the only way to open is to dispatch `open-sidebar-{id}` on `window`.
- **default** — drawer body. Place a nav, form, or any content here. Wrap in `<div class="flex flex-col h-full">` if you want a footer that sticks to the bottom (see "Sidebar with footer" example).

## Examples

### Basic, slide from left

```blade
<x-sidebar>
    <x-slot:trigger>
        <x-button>Open menu</x-button>
    </x-slot:trigger>

    <h2 class="text-lg font-semibold mb-4">Menu</h2>
    <nav class="flex flex-col gap-0.5">
        <x-menu-item href="/" active>Dashboard</x-menu-item>
        <x-menu-item href="/projects">Projects</x-menu-item>
        <x-menu-item href="/settings">Settings</x-menu-item>
    </nav>
</x-sidebar>
```

### Right-side inspector, no backdrop

The page underneath stays fully interactive — useful for "click row → inspector slides in" patterns.

```blade
<x-sidebar side="right" :backdrop="false">
    <x-slot:trigger>
        <x-button appearance="outline">Inspect</x-button>
    </x-slot:trigger>

    <h2 class="text-lg font-semibold mb-4">Details</h2>
    <p>Click anywhere on the page — the drawer stays open.</p>
</x-sidebar>
```

### Sidebar with footer

The hairline border-t spans the full panel width by canceling the panel's `p-element` horizontal padding with `-mx-[var(--space-element)] px-[var(--space-element)]`. The body uses `flex flex-col h-full` so `mt-auto` pushes the footer to the bottom.

```blade
<x-sidebar side="left" size="md">
    <x-slot:trigger><x-button>Open</x-button></x-slot:trigger>

    <div class="flex flex-col h-full">
        <nav class="flex flex-col gap-0.5">
            <x-menu-item href="/" active>Dashboard</x-menu-item>
            <x-menu-item href="/projects">Projects</x-menu-item>
            <x-menu-item href="/team">Team</x-menu-item>
        </nav>

        <div class="mt-auto -mx-[var(--space-element)] px-[var(--space-element)] pt-5 border-t border-base-300">
            <div class="flex items-center gap-3">
                <x-avatar size="sm" :initials="'AT'" />
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium truncate">Akihiko Takai</div>
                    <div class="text-xs text-base-content/50 truncate">akihiko@example.com</div>
                </div>
            </div>
        </div>
    </div>
</x-sidebar>
```

### Open from outside the trigger slot

Give the sidebar a stable `id` and dispatch events from anywhere — a top-bar button, a keyboard shortcut, a Livewire action.

```blade
<x-sidebar id="main-nav" />

<button @click="$dispatch('open-sidebar-main-nav')">Open</button>
<button @click="$dispatch('close-sidebar-main-nav')">Close</button>
```

## Class composition

Class strings come from [`SidebarComposer::compose($props)`](../../src/Compose/SidebarComposer.php) — keys: `overlay`, `backdrop`, `panel`, `sizeWidth`, `sideAnchor`, `enterFrom`, `enterTo`, `closeBtnFloat`, `closeIcon`. The panel uses `fixed inset-0 z-50` for the overlay, `absolute top-0 h-full` for the panel, and `translate-x-[±100%]` for the slide transition. When `backdrop=false`, the overlay gets `pointer-events-none` and the panel adds `pointer-events-auto` so only the drawer itself receives clicks.

## Related

- [`<x-modal>`](./modal.md) — blocking dialog with backdrop and centered layout.
- [`<x-dropdown>`](./dropdown.md) — anchored, smaller floating menu.
- [`<x-menu-item>`](./menu-item.md) — standard row for the nav body.

## Notes

- The panel is teleported to `<body>` (`<template x-teleport="body">`), so it escapes overflow / transform contexts of ancestors. Don't rely on its position in the DOM tree for selectors.
- `x-trap.inert.noscroll` traps Tab inside the panel and locks page scroll while the drawer is open.
- The floating close button is always pinned to the panel's **top-right** corner (`top-[var(--space-element)] right-[var(--space-element)]`) regardless of `side`. Content headings typically sit top-left (LTR), so this avoids overlap. Override with `class="..."` on a custom close button inside the slot if you need a different position.
- Open/close events are name-spaced by `id`: `open-sidebar-{id}` and `close-sidebar-{id}`. Always set an explicit `id` if you'll dispatch events from outside the trigger slot.
- `closeOnBackdrop` and `escape` are both `true` by default — set to `false` for modal-style "must use action button to close" flows.
