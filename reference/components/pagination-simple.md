# x-pagination-simple

Compact Prev / Next pagination with an inline "current / last" indicator between the arrows, plus an optional info line below. Same `:paginator` and `:baseUrl` API as the full [`<x-pagination>`](./pagination.md) — drop-in for cursor-style listings, infinite-feed fallbacks, or anywhere a numbered page window is overkill.

**Playground page**: [`pinion-ui-playground/resources/views/pages/pagination.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/pagination.blade.php) — covers both [`<x-pagination>`](./pagination.md) and `<x-pagination-simple>`.

## When to use

- Cursor-style pagination where the total page count may be expensive or meaningless.
- Mobile / narrow UIs where the full numbered window would wrap awkwardly.
- Lightweight lists where users rarely jump multiple pages at once.
- For numbered jumps, ellipses, and first/last shortcuts use [`<x-pagination>`](./pagination.md) instead.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `paginator` | `\Illuminate\Contracts\Pagination\LengthAwarePaginator \| null` | `null` | Laravel paginator instance. When set, `current`, `last`, `total`, `perPage`, and first/last item indices are read from it. |
| `current` | `int` | `1` | Current page (1-indexed). Used when `paginator` is `null`. |
| `last` | `int` | `1` | Last page number. Used when `paginator` is `null`. |
| `total` | `int` | `0` | Total item count, for the info line. Used when `paginator` is `null`. |
| `perPage` | `int` | `10` | Items per page, for computing first/last item indices when `paginator` is `null`. |
| `baseUrl` | `string \| null` | `null` | When set, Prev/Next render as `<a href>` links to `{baseUrl}?{pageParam}={n}`. When `null`, they're buttons that dispatch a `page-change` event. |
| `pageParam` | `string` | `'page'` | Query-string key used by `baseUrl`. |
| `preserveQuery` | `bool` | `true` | Merge the current request's query string into each page URL when `baseUrl` is set. |
| `showInfo` | `bool` | `true` | Whether to render the "X 件中 Y - Z 件" info line below the buttons. Hidden when `total=0`. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Item height / padding / text size via tune tokens. Also scales the prev/next icon. |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'primary'` | Color used by the composer for the "active" style — passed through for API parity with `<x-pagination>`. The simple variant has no active page item, so this only affects focus-ring styling. |
| `appearance` | `'solid' \| 'outline' \| 'soft'` | `'soft'` | Same parity-only role as `color`. |
| `prevLabel` | `string` | `'前へ'` | `aria-label` for the Previous button (icon-only). |
| `nextLabel` | `string` | `'次へ'` | `aria-label` for the Next button (icon-only). |
| `infoTemplate` | `string` | `'全 :total 件中 :first - :last 件'` | Template for the info line. Placeholders `:total`, `:first`, `:last` are replaced with bolded numbers. |

All other attributes pass through to the wrapper `<div>`.

## Slots

None.

## Examples

### With a Laravel paginator

```blade
<x-pagination-simple :paginator="$posts" :baseUrl="url()->current()" />
```

### Cursor-style, client-driven

```blade
<div @page-change="loadPage($event.detail.page)">
    <x-pagination-simple :current="$page" :last="$lastPage" :total="$total" />
</div>
```

### English labels, compact

```blade
<x-pagination-simple
    :paginator="$posts"
    :baseUrl="url()->current()"
    size="sm"
    prevLabel="Previous"
    nextLabel="Next"
    infoTemplate="Showing :first – :last of :total"
/>
```

### Hide the info line

```blade
<x-pagination-simple :paginator="$posts" :showInfo="false" />
```

## Class composition

Class strings come from [`PaginationComposer::compose($props)`](../../src/Compose/PaginationComposer.php) — the **same composer** as [`<x-pagination>`](./pagination.md). The simple variant uses the `wrapperSimple` key (centered column with `gap-3`) instead of `wrapper`, and emits only three nav items: Prev button, a static `current / last` indicator (`itemStatic`), and Next button. The numbered window logic in `windowPages()` is skipped entirely.

## Related

- [`<x-pagination>`](./pagination.md) — full numbered pagination with ellipsis and first/last shortcuts.
- [`<x-table-scroll>`](./table-scroll.md) — wrap tables paired with pagination so the page itself doesn't flex.

## Notes

- When neither `paginator` nor `baseUrl` is set, Prev / Next are `<button>` elements that dispatch `page-change` with `{ detail: { page } }`. The wrapper exposes `goToPage(n)` on its Alpine scope.
- When `baseUrl` is set, Prev / Next render as real `<a href>` — server-rendered navigation works without JS.
- The middle indicator (`<current> / <last>`) is non-interactive — to let users jump directly to a page, use [`<x-pagination>`](./pagination.md).
- The whole block is wrapped in `@if($hasPages)`; with one page or fewer, nothing renders.
- `color` and `appearance` are accepted for API parity with `<x-pagination>` but have no visible effect in the simple variant beyond focus-ring color (there's no "active page" item to tint).
- Default `prevLabel` / `nextLabel` are Japanese (`前へ` / `次へ`); the visible glyphs are icons — these strings are `aria-label` only.
