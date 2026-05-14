# x-table-scroll

Overflow-x wrapper for tables (or any wide content) that keeps the page from flexing on narrow viewports. Renders fade gradients on the overflow edges and floating left/right scroll buttons that appear only when there's content to scroll toward. Driven by Alpine with a `ResizeObserver` so buttons stay accurate as content reflows.

**Playground page**: [`pinion-ui-playground/resources/views/pages/table-scroll.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/table-scroll.blade.php) — full variant matrix and live demos.

## When to use

- Wide data tables on responsive layouts.
- Horizontally scrolling rows of cards / chips.
- For paginated tables prefer `<x-pagination>` over horizontal scroll once column count is fixed.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `fadeColor` | `string` | `'base-100'` | Tailwind/daisyUI color token used for the edge fade gradient. Must match the wrapper's actual background — pass `'base-200'` if you've placed the component on a `bg-base-200` surface. |
| `buttonStyle` | `'circle' \| 'flat'` | `'circle'` | Floating button shape. `circle` = rounded with shadow + hairline border (on `bg-base-100`). `flat` = `rounded-[var(--radius-field)]` on `bg-base-200`. |
| `scrollAmount` | `float` | `0.6` | Fraction of the visible container width to scroll per button click (e.g. `0.6` = 60%). |
| `showButtons` | `bool` | `true` | Toggle the scroll buttons + fade gradients off entirely; the container still scrolls natively. |
| `prevLabel` | `string` | `'前へスクロール'` | `aria-label` for the left scroll button. |
| `nextLabel` | `string` | `'次へスクロール'` | `aria-label` for the right scroll button. |

All other attributes pass through to the outer wrapper `<div>`.

## Slots

- **default** — the wide content. Typically `<table>...</table>`, but any flex/grid/wide block works.

## Examples

### Basic

```blade
<x-table-scroll>
    <table class="min-w-[800px] w-full">
        <thead>...</thead>
        <tbody>...</tbody>
    </table>
</x-table-scroll>
```

### Match a colored surface

```blade
<div class="bg-base-200 p-4">
    <x-table-scroll fadeColor="base-200">
        <table class="min-w-[800px] w-full">...</table>
    </x-table-scroll>
</div>
```

### Flat buttons, smaller scroll step

```blade
<x-table-scroll buttonStyle="flat" :scrollAmount="0.3">
    <div class="flex gap-4 min-w-max">
        <x-card class="w-72">...</x-card>
        <x-card class="w-72">...</x-card>
        <x-card class="w-72">...</x-card>
    </div>
</x-table-scroll>
```

### English aria labels

```blade
<x-table-scroll prevLabel="Scroll left" nextLabel="Scroll right">
    <table>...</table>
</x-table-scroll>
```

### No buttons (fade gradients also hidden)

```blade
<x-table-scroll :showButtons="false">
    <table>...</table>
</x-table-scroll>
```

## Class composition

See [`src/Compose/TableScrollComposer.php`](../../src/Compose/TableScrollComposer.php). Returns `wrapper`, `scrollContainer`, `leftFade`, `rightFade`, `buttonOuterLeft`, `buttonOuterRight`, `buttonInner`, `iconSize`. The fade gradients are built from `bg-gradient-to-{r,l} from-{fadeColor} via-{fadeColor}/90 to-transparent` — so `fadeColor` is concatenated into a Tailwind class at compile time and must be a token the Tailwind safelist knows about (`base-100` / `base-200` / `base-300` are safe; arbitrary hex values won't survive purging).

## Related

- [`<x-pagination>`](./pagination.md) — page-by-page navigation for long tables once layout settles.
- [`<x-card>`](./card.md) — common wide-row child of table-scroll for horizontal card carousels.

## Notes

- The buttons / fades are pure CSS overlays; the underlying `overflow-x-auto` container scrolls natively on touch devices and trackpads. The Alpine logic only manages `canScrollLeft` / `canScrollRight` visibility.
- `prevLabel` / `nextLabel` default to Japanese strings — override them in international UIs.
- `fadeColor` is interpolated into a Tailwind class string at render time. If you use a custom color, ensure it appears in your Tailwind safelist (or pass a static daisyUI token).
- A `ResizeObserver` watches both the container and every direct child, so dynamically inserted rows correctly re-evaluate the button visibility.
