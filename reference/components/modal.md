# x-modal

Alpine-driven dialog with backdrop, focus trap, scroll lock, and ESC + click-outside dismiss. Open via a `trigger` slot (in-place) or a `$dispatch('open-modal-{id}')` event (from anywhere on the page). Five sizes, optional title, optional close button, optional `actions` footer slot.

**Playground page**: [`pinion-ui-playground/resources/views/pages/modal.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/modal.blade.php) — size / title / dismiss / dispatch demos with confirmation and form patterns.

## When to use

- Confirm a destructive action ("Delete this item?") — pair with `:closeOnBackdrop="false"`.
- Display a focused form that would interrupt page flow if inlined.
- Show a lightbox / detail view that needs an explicit close.
- For lightweight tooltips (no focus, no backdrop) use [`<x-tooltip>`](./tooltip.md).
- For off-canvas navigation panels use [`<x-sidebar>`](./sidebar.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `id` | `string \| null` | auto (`'modal_' . uniqid()`) | Unique identifier. Used to build the open/close event names: `open-modal-{id}` and `close-modal-{id}`. Set explicitly when you want to open the modal from another component. |
| `title` | `string \| null` | `null` | Optional heading. When set, renders a header row with the title on the left and (if `showClose`) a × on the right. When omitted and `showClose=true`, the × floats absolutely at the panel's top-right corner so body content starts at the top padding (no empty header row). |
| `size` | `'sm' \| 'md' \| 'lg' \| 'xl' \| 'full'` | `'md'` | Max-width class — `max-w-sm` / `max-w-lg` / `max-w-2xl` / `max-w-4xl` / `max-w-full mx-4`. |
| `showClose` | `bool` | `true` | Whether to render the × close button. Set to `false` for dialogs that must be confirmed via the `actions` slot. |
| `closeOnBackdrop` | `bool` | `true` | Whether clicking the dimmed backdrop dismisses the modal. Set to `false` for destructive confirmations or forms in progress. |

All other attributes pass through to the outer Alpine scope `<div>`.

## Slots

- **default** — modal body content. Rendered directly inside the panel below the (optional) header.
- **trigger** — wrapping element clicked to open the modal in place. Optional — omit it if you only open via `$dispatch('open-modal-{id}')`.
- **actions** — footer slot for action buttons. Rendered with `flex items-center justify-end gap-xs mt-lg`. Each button typically uses `@click="open = false"` to close after handling its action.

## Examples

### Basic with trigger slot

```blade
<x-modal title="Confirm">
    <x-slot:trigger>
        <x-button>Open</x-button>
    </x-slot:trigger>
    <p>Are you sure you want to continue?</p>
</x-modal>
```

### Destructive confirmation (closeOnBackdrop off)

```blade
<x-modal title="Delete this item?" size="sm" :closeOnBackdrop="false">
    <x-slot:trigger>
        <x-button color="error">Delete…</x-button>
    </x-slot:trigger>
    <p>This action cannot be undone.</p>
    <x-slot:actions>
        <x-button appearance="ghost" @click="open = false">Cancel</x-button>
        <x-button color="error" @click="open = false">Delete</x-button>
    </x-slot:actions>
</x-modal>
```

### Titleless modal (floating × button)

When `title` is omitted, the × button is anchored absolutely to the panel's top-right corner — body content starts at the top padding without leaving an empty header row. Useful for lightbox / gallery / image preview patterns.

```blade
<x-modal>
    <x-slot:trigger>
        <x-button appearance="soft">Open preview</x-button>
    </x-slot:trigger>
    <img src="/preview.jpg" alt="Preview" class="w-full" />
</x-modal>
```

Internally this swaps the in-header `closeBtn` for the `closeBtnFloat` variant (sized slightly smaller — `w-4 h-4` icon, `p-0.5` button — to feel quieter without a neighboring title to balance against).

### Opening from anywhere via `id`

Set an explicit `id`, omit the `trigger` slot, and dispatch from any element on the page:

```blade
<x-modal id="welcome" title="Welcome">
    <p>Hello there.</p>
    <x-slot:actions>
        <x-button @click="open = false">Got it</x-button>
    </x-slot:actions>
</x-modal>

<x-button x-on:click="$dispatch('open-modal-welcome')">Open welcome</x-button>
{{-- close from elsewhere: --}}
<button x-on:click="$dispatch('close-modal-welcome')">Close</button>
```

### Form modal

```blade
<x-modal title="New project" size="md" :closeOnBackdrop="false">
    <x-slot:trigger>
        <x-button color="primary">+ Create</x-button>
    </x-slot:trigger>
    <div class="space-y-3">
        <x-input name="name" label="Name" />
        <x-textarea name="description" label="Description" />
    </div>
    <x-slot:actions>
        <x-button appearance="ghost" @click="open = false">Cancel</x-button>
        <x-button color="primary" @click="open = false">Create</x-button>
    </x-slot:actions>
</x-modal>
```

## Class composition

Class strings come from [`SparrowhawkLabs\PinionUi\Compose\ModalComposer`](../../src/Compose/ModalComposer.php). Composer returns `overlay`, `backdrop`, `panel` (with embedded `sizeClass`), `header`, `title`, `closeBtn` / `closeIcon` (in-header), `closeBtnFloat` / `closeIconFloat` (titleless), and `actions`. The blade picks between the two close-button styles based on whether `title` is set.

## Related

- [`<x-sidebar>`](./sidebar.md) — off-canvas drawer for navigation / filters.
- [`<x-tooltip>`](./tooltip.md) — lightweight hover bubble (no focus trap).
- [`<x-button>`](./button.md) — standard trigger / action element.

## Notes

- The panel is rendered via `x-teleport="body"` so it escapes any clipping ancestor (overflow-hidden cards, transformed parents).
- `x-trap.inert.noscroll` traps focus while the modal is open and locks page scroll. The `.inert` modifier marks outside DOM with the `inert` attribute so SR / tab focus skips it.
- `aria-labelledby` is auto-wired to `{id}_title` when `title` is set; consider adding `aria-describedby` manually on the panel for long-form copy.
- Action buttons should close via `@click="open = false"` (the Alpine scope is the outermost `<div>`). The `actions` slot is just a styled flex row — no automatic close.
- The titleless close button is intentionally **smaller** than the in-header one (`p-0.5` + `w-4 h-4` vs `p-1` + `w-5 h-5`). Without a title to anchor visual weight, the larger × feels stamp-pasted on the corner — this is documented in the Composer source.
