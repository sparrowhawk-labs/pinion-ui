# x-notification-system

Page-wide toast container. Mount once in your layout, then dispatch a browser event from anywhere — Alpine, Livewire, vanilla JS, or a Laravel session flash — and a stack of toasts animates in at the configured position. Four semantic `type`s (`info` / `success` / `warning` / `error`), four shared `appearance`s, three sizes, and customizable duration + event name.

**Playground page**: [`pinion-ui-playground/resources/views/pages/notification-system.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/notification-system.blade.php) — multi-instance demo with stacking and Laravel flash integration.

## When to use

- Transient feedback for an action — "Saved", "Copy failed", "3 items deleted (undo)".
- For persistent inline status that stays with the related content use [`<x-alert>`](./alert.md) instead.
- For long-running progress communication use [`<x-progress>`](./progress.md) + a status `<x-alert>`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `position` | `'top-right' \| 'top-left' \| 'top-center' \| 'bottom-right' \| 'bottom-left' \| 'bottom-center'` | `'bottom-right'` | Where the toast stack anchors. |
| `appearance` | `'solid' \| 'outline' \| 'soft' \| 'bordered-left'` | `'bordered-left'` | Visual style applied per toast. `bordered-left` (default) is a surface bg with a thick left accent bar — calm and theme-friendly. `solid` is the most attention-grabbing. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Field-token sizing — uses `--h-field-*` / `--px-field-*` / `--text-field-*` so toasts match `<x-input>` / `<x-button>` at the same size. |
| `duration` | `int` (ms) | `3000` | Auto-dismiss timeout. Each toast starts its own timer when added. |
| `eventName` | `string` | `'notify'` | The window event this instance listens for. Use distinct names if you mount multiple notification systems on the same page (e.g. one top-right for success, one bottom-center for errors). |
| `closeLabel` | `string` | `'閉じる'` | `aria-label` for the × button. |

All other attributes pass through to the wrapper `<div>` (which carries `role="status"` and `aria-live="polite"`).

## Slots

None — toasts are entirely JS-driven. The content of each toast comes from the `content` field of the dispatched event detail.

## JavaScript / Alpine API

### Dispatching a toast

Fire a `CustomEvent` on `window` with the configured event name. Each toast detail must carry `type` and `content`:

```alpine
<button x-on:click="$dispatch('notify', { type: 'success', content: 'Saved.' })">
    Save
</button>
```

From vanilla JS:

```js
window.dispatchEvent(new CustomEvent('notify', {
    detail: { type: 'error', content: 'Connection failed.' },
}));
```

From Livewire:

```php
$this->dispatch('notify', type: 'warning', content: 'Session expires soon.');
```

### Laravel session flash

If you flash `notify` from a controller, the notification system replays it on next render — no event dispatch needed:

```php
return redirect()->back()->with('notify', [
    'type'    => 'success',
    'content' => 'Saved.',
]);
```

The component's `init()` reads the flashed `session('notify')` and adds it as a toast on mount.

### Stacking

Multiple dispatches stack vertically (`flex flex-col gap-3`). Toasts share a single Alpine scope — order, fade-in, and dismiss are handled internally; you only fire events.

## Examples

### Mount + dispatch

```blade
{{-- 1. In your layout, mount once: --}}
<x-notification-system />

{{-- 2. Anywhere on the page: --}}
<x-button x-on:click="$dispatch('notify', { type: 'info', content: 'Build started.' })">
    Build
</x-button>
```

### Multiple positions on one page

Mount more than one instance with distinct `event-name`s:

```blade
<x-notification-system />
<x-notification-system event-name="notify-top" position="top-center" appearance="solid" size="sm" />

{{-- bottom-right (default) --}}
<button x-on:click="$dispatch('notify', { type: 'success', content: 'Saved.' })">Save</button>

{{-- top-center solid --}}
<button x-on:click="$dispatch('notify-top', { type: 'error', content: 'Permission denied.' })">Trigger</button>
```

### Custom duration

```blade
<x-notification-system :duration="6000" />
```

### Laravel flash on redirect

```blade
{{-- in layout --}}
<x-notification-system />
```

```php
// in controller
return redirect()->route('items.index')
    ->with('notify', ['type' => 'success', 'content' => 'Item created.']);
```

## Class composition

Class strings come from [`SparrowhawkLabs\PinionUi\Compose\NotificationSystemComposer`](../../src/Compose/NotificationSystemComposer.php). Composer returns the wrapper (positioning), per-toast item shell, icon wrap / size, content, close button, and four `variant{Type}` / `iconColor{Type}` strings — one per `info` / `success` / `warning` / `error`. The blade serializes the per-type maps into Alpine data so the right variant is bound when `n.type` resolves at render time.

## Related

- [`<x-alert>`](./alert.md) — persistent inline message with the same color / appearance vocabulary.
- [`<x-button>`](./button.md) — common trigger source for `$dispatch('notify', …)`.

## Notes

- The wrapper is `pointer-events-none` and each toast is `pointer-events-auto`, so the rest of the page stays interactive around the stack.
- Toasts auto-dismiss after `duration` ms; the user can also dismiss early via the × button (which triggers the same fade-out).
- `eventName` is the *only* way to scope multiple instances — events still go through `window`, so mounting two instances with the same name will show duplicate toasts in both stacks.
- Default `closeLabel` is Japanese (`'閉じる'`) reflecting the package's primary user base; override per-instance for other locales.
- The icon mapping is fixed (`info` → info-circle, `success` → check-circle, `warning` → danger-triangle, `error` → close-circle). Unknown `type` values fall through to the `info` variant.
