# x-spinner

Inline loading indicator built on daisyUI 5's `loading` family. Six variants (`spinner` default + `dots` / `ring` / `bars` / `ball` / `infinity`), five sizes (`xs`–`xl`), and seven optional semantic colors.

**Playground page**: [`pinion-ui-playground/resources/views/pages/spinner.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/spinner.blade.php) — variant × size × color matrix and in-context examples.

## When to use

- Indicate an in-flight async action — button submitting, panel reloading, lazy section.
- `<x-button loading>` already embeds a spinner — use this component for standalone indicators (page, card, inline beside text).
- For unknown-duration progress bars use [`<x-progress>`](./progress.md) without `value`; for placeholder content use [`<x-skeleton>`](./skeleton.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `variant` | `'spinner' \| 'dots' \| 'ring' \| 'bars' \| 'ball' \| 'infinity'` | `'spinner'` | daisyUI loading style (`loading-spinner` / `loading-dots` / `loading-ring` / `loading-bars` / `loading-ball` / `loading-infinity`). |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg' \| 'xl'` | `'md'` | daisyUI size modifier (`loading-{size}`). |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'info' \| 'success' \| 'warning' \| 'error' \| null` | `null` | Tints via `text-{color}` (daisyUI uses `currentColor`). `null` leaves it inheriting from the parent. |

All other attributes pass through to the root `<span>` (which carries `aria-label="Loading..."`).

## Slots

None — spinner is a self-contained indicator.

## Examples

### Basic

```blade
<x-spinner />
<x-spinner variant="dots" />
<x-spinner variant="ring" color="primary" />
```

### Inline with text

```blade
<p class="flex items-center gap-2 text-base-content/70">
    <x-spinner variant="dots" size="sm" /> Syncing…
</p>
```

### In a button (manual)

```blade
<x-button>
    <x-spinner size="sm" /> Saving…
</x-button>
```

For the common "button shows spinner while loading" case, prefer `<x-button loading>` — it handles icon hiding and pointer-events automatically.

### Size + color matrix

```blade
<x-spinner size="xs" />
<x-spinner size="sm" color="info" />
<x-spinner size="md" color="success" />
<x-spinner size="lg" color="warning" variant="bars" />
<x-spinner size="xl" color="error" variant="infinity" />
```

## Class composition

Class strings come from [`SparrowhawkLabs\PinionUi\Compose\SpinnerComposer`](../../src/Compose/SpinnerComposer.php) — `root` is the only key, combining `loading` + variant + size + optional color.

## Related

- [`<x-button>`](./button.md) — the `loading` prop embeds a spinner with proper icon swap and disabled semantics.
- [`<x-progress>`](./progress.md) — for measured progress or indeterminate stripes.
- [`<x-skeleton>`](./skeleton.md) — for placeholder content blocks.

## Notes

- daisyUI's `loading-*` classes color via `currentColor`, so `text-{color}` from `color` is enough — there's no `loading-primary` etc.
- The root is a `<span>` so the spinner flows inline by default; wrap it in a `<div class="flex justify-center">` (or similar) to center it in a card.
- `aria-label="Loading..."` is fixed in English. Localized labels can be added via a pass-through `aria-label` on the component call.
