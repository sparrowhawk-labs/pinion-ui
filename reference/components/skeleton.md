# x-skeleton

Animated placeholder for content that hasn't loaded yet. Three shapes — `rect` (default), `circle` (avatars), `text` (typographic lines, optionally multiple). Width and height are passed as Tailwind class names (e.g. `width="w-32"`, `height="h-4"`) so they compose with the rest of the design system.

**Playground page**: [`pinion-ui-playground/resources/views/pages/skeleton.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/skeleton.blade.php) — shape / radius / multi-line / static demos.

## When to use

- Reserve layout space while data is loading — avatar lists, card grids, paragraph placeholders.
- For an inline spinner (button loading state) use [`<x-spinner>`](./spinner.md).
- For an indeterminate "in-flight" bar use [`<x-progress>`](./progress.md) without `value`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `shape` | `'rect' \| 'circle' \| 'text'` | `'rect'` | `rect` general block; `circle` forces `rounded-full` and defaults to `w-12 h-12`; `text` defaults to `w-full h-4` and supports multi-line via `lines`. |
| `width` | `string \| null` | shape-dependent | Tailwind width class — `'w-32'`, `'w-1/2'`, `'w-full'`. Defaults: `circle` → `w-12`, others → `w-full`. |
| `height` | `string \| null` | shape-dependent | Tailwind height class — `'h-4'`, `'h-24'`. Defaults: `circle` → `h-12`, others → `h-4`. Ignored entirely for a multi-line `text` block (every line is forced to `h-4`; the last line is also shortened to `w-2/3`). |
| `lines` | `int` | `1` | Only honored when `shape="text"`. With `lines > 1` renders a stack of bars (vertical `space-y-2`); the last line is shortened to `w-2/3` for typographic realism. |
| `radius` | `'default' \| 'sm' \| 'md' \| 'lg' \| 'xl' \| 'full'` | `'default'` | Corner radius (Tailwind `rounded-*`). Ignored for `shape="circle"` (always `rounded-full`). |
| `animated` | `bool` | `true` | When `true` uses daisyUI's `skeleton` class (shimmer animation); when `false` falls back to a static `bg-base-300` block — useful in tests / motion-sensitive contexts. |

All other attributes pass through to the wrapper `<div>` (`aria-hidden="true"` is set automatically).

## Slots

None — skeleton is a pure visual placeholder.

## Examples

### Basic

```blade
<x-skeleton />
<x-skeleton width="w-32" height="h-8" />
<x-skeleton shape="circle" />
<x-skeleton shape="circle" width="w-16" height="h-16" />
```

### Multi-line text

```blade
<x-skeleton shape="text" :lines="3" />
<x-skeleton shape="text" :lines="5" width="w-2/3" />
```

### Composite card placeholder

```blade
<div class="flex items-start gap-4 p-4">
    <x-skeleton shape="circle" />
    <div class="flex-1 space-y-2">
        <x-skeleton width="w-1/3" height="h-4" />
        <x-skeleton shape="text" :lines="2" />
    </div>
</div>
```

### Static (no animation)

```blade
<x-skeleton :animated="false" width="w-24" height="h-6" />
```

## Class composition

Class strings come from [`SparrowhawkLabs\PinionUi\Compose\SkeletonComposer`](../../src/Compose/SkeletonComposer.php). Composer returns `root`, `item`, and `itemLast` — single-shape skeletons collapse all three to the same string, multi-line text uses a wrapper (`root`) plus per-line item classes with `itemLast` shortened to `w-2/3`.

## Related

- [`<x-spinner>`](./spinner.md) — inline loading indicator.
- [`<x-progress>`](./progress.md) — determinate or indeterminate progress bar.

## Notes

- `width` / `height` accept **any Tailwind class**, not raw CSS — so `width="w-[120px]"` works for arbitrary values without leaving the design system.
- `aria-hidden="true"` is fixed; skeletons are decorative and should not be announced. Pair them with a live region elsewhere if you need an SR announcement.
- The `animated="false"` fallback (`bg-base-300`) matches the skeleton's resting color, so the visual weight is unchanged when motion is disabled.
