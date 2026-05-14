# x-popover

Click-driven (or hover) panel that floats next to a trigger element. Sits between [`<x-dropdown>`](./dropdown.md) (menu-item semantics) and [`<x-tooltip>`](./tooltip.md) (hover-only, text-only) on the overlay spectrum: popover hosts **arbitrary content** — info card, mini form, confirmation prompt, etc.

Positioning is **CSS-based** (no JS computation): pick one of four placements (`top` / `right` / `bottom` / `left`), the panel sits relative to the trigger. Trades layout freedom for predictability — for collision-aware placement, reach for Floating UI / `@alpinejs/anchor` and roll your own.

**Playground page**: [`pinion-ui-playground/resources/views/pages/popover.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/popover.blade.php) — full variant matrix and live demos.

## When to use

- Inline info card next to a control ("?", "ⓘ" icon → details).
- Mini form that should not interrupt page flow (date filter, search options).
- Confirmation panel ("Delete?" → confirm/cancel buttons).
- For a list of action items, use [`<x-dropdown>`](./dropdown.md).
- For a tooltip on hover with short text, use [`<x-tooltip>`](./tooltip.md).
- For a full modal dialog with backdrop, use [`<x-modal>`](./modal.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `placement` | `'top' \| 'right' \| 'bottom' \| 'left'` | `'bottom'` | Panel position relative to the trigger. Centred on the trigger's opposite axis. |
| `width` | `string` | `'w-72'` | Tailwind width utility for the panel. Use `'w-fit'` for content-shrunk popovers, or `'w-96'` etc. for wider. |
| `arrow` | `bool` | `true` | Show a small diamond pointing at the trigger. Disable for borderless / minimal popovers. |
| `trigger` | `'click' \| 'hover'` | `'click'` | Open mechanism. `'click'` toggles open on click, closes on outside click and ESC. `'hover'` opens on mouseenter, closes on mouseleave — careful: hover popovers shouldn't host inputs (you can't move the cursor in without leaving the trigger). |

All other attributes pass through to the outer wrapper.

## Slots

- **triggerSlot** *(named)* — the element that opens the popover. Wrapped in a `<div>` with the appropriate Alpine handlers. Typically a button, badge, or icon.
- **default** — the popover panel content. Any markup — text, form, buttons, mix.

## Examples

### Basic info popover

```blade
<x-popover>
    <x-slot:triggerSlot>
        <x-button appearance="ghost" size="sm">
            <x-i type="question" class="w-4 h-4" /> Help
        </x-button>
    </x-slot:triggerSlot>

    <p class="font-medium mb-1">About this field</p>
    <p class="text-sm text-base-content/70">Enter the email address you used at signup. We'll send a reset link if it matches an account.</p>
</x-popover>
```

### Placement variants

```blade
<x-popover placement="top">…</x-popover>
<x-popover placement="right">…</x-popover>
<x-popover placement="left">…</x-popover>
<x-popover placement="bottom">…</x-popover>
```

### Confirmation prompt

```blade
<x-popover placement="top" width="w-64">
    <x-slot:triggerSlot>
        <x-button color="error" appearance="outline" size="sm">Delete</x-button>
    </x-slot:triggerSlot>

    <p class="font-medium mb-2">Delete this item?</p>
    <p class="text-sm text-base-content/70 mb-3">This action can't be undone.</p>
    <div class="flex justify-end gap-2">
        <x-button size="sm" appearance="ghost" x-on:click="open = false">Cancel</x-button>
        <x-button size="sm" color="error" x-on:click="open = false">Delete</x-button>
    </div>
</x-popover>
```

### Mini form

```blade
<x-popover placement="bottom" width="w-80">
    <x-slot:triggerSlot>
        <x-button appearance="outline">Filter</x-button>
    </x-slot:triggerSlot>

    <p class="font-medium mb-2">Filter results</p>
    <x-input size="sm" label="Keyword" name="q" />
    <x-select size="sm" label="Sort by" name="sort" class="mt-3">
        <option value="new">Newest</option>
        <option value="old">Oldest</option>
    </x-select>
    <div class="flex justify-end gap-2 mt-4">
        <x-button size="sm" appearance="ghost" x-on:click="open = false">Cancel</x-button>
        <x-button size="sm" color="primary" x-on:click="open = false">Apply</x-button>
    </div>
</x-popover>
```

### Hover trigger (info-only, no inputs)

```blade
<x-popover trigger="hover" placement="top" width="w-fit">
    <x-slot:triggerSlot>
        <span class="underline decoration-dotted cursor-help">JST</span>
    </x-slot:triggerSlot>
    <p class="text-sm">Japan Standard Time (UTC+9)</p>
</x-popover>
```

### Borderless / arrowless

```blade
<x-popover :arrow="false" width="w-fit">
    <x-slot:triggerSlot>
        <x-button appearance="ghost"><x-i type="more-horizontal" class="w-5 h-5" /></x-button>
    </x-slot:triggerSlot>
    <ul class="text-sm space-y-1">
        <li><a href="#" class="block hover:bg-base-200 rounded px-2 py-1">Edit</a></li>
        <li><a href="#" class="block hover:bg-base-200 rounded px-2 py-1">Duplicate</a></li>
    </ul>
</x-popover>
```

## Class composition

See [`src/Compose/PopoverComposer.php`](../../src/Compose/PopoverComposer.php). Returns `root` (`relative inline-block`), `panel` (the floating panel — absolute + placement + bg/border/shadow + padding), `arrow` (rotated diamond positioned at the edge facing the trigger), `showArrow` (bool), `placement` (normalised string).

Placement is implemented with four hand-mapped CSS rules:

- **bottom** (default): `top-full left-1/2 -translate-x-1/2 mt-2`
- **top**: `bottom-full left-1/2 -translate-x-1/2 mb-2`
- **right**: `left-full top-1/2 -translate-y-1/2 ml-2`
- **left**: `right-full top-1/2 -translate-y-1/2 mr-2`

Arrow positions mirror the panel's edge facing the trigger.

## Related

- [`<x-dropdown>`](./dropdown.md) — list of menu items with shared chrome; reach for it when content is a click-list, not a panel.
- [`<x-tooltip>`](./tooltip.md) — hover-only, text-only floating bubble.
- [`<x-modal>`](./modal.md) — full-screen dialog with backdrop; for confirmations / forms that must interrupt page flow.

## Notes

- **Closing from inside**: panel content can close the popover with `x-on:click="open = false"` — the Alpine scope is shared with the wrapper, so `open` is in reach.
- **Hover trigger limitation**: `trigger="hover"` opens on mouseenter and closes on mouseleave **of the trigger only**. Moving the cursor into the panel doesn't keep it open — don't use hover for popovers that contain inputs or buttons (use `'click'` for those).
- **No viewport collision detection**: if the popover would clip outside the viewport, it just does — pick a placement that makes sense for where the trigger lives, or wrap the page section in `overflow-visible` etc. For auto-flip, integrate Floating UI.
- **Arrow border**: the diamond's two visible edges are bordered (matching `border-base-300`) so the panel + arrow read as a single shape. With `arrow=false`, the panel still floats but no pointer.
- **z-50**: the panel is at `z-50`. If your layout has higher-stacking siblings (sticky headers, modals), adjust via the wrapper's `class="z-..."` override on the **wrapper** (not the panel) since `relative` on the wrapper creates a new stacking context.
