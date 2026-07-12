# x-pagination

Full pagination control — Prev / first / dots / numbered window / dots / last / Next, plus an optional "showing X – Y of Z" info line. Accepts a Laravel `LengthAwarePaginator` directly via `:paginator`, or raw `current` / `last` / `total` / `perPage` props if you're managing state yourself. URLs are built when `:baseUrl` is set; otherwise items emit `page-change` events on click for client-driven flows (Livewire, Alpine, etc.).

**Playground page**: [`pinion-ui-playground/resources/views/pages/pagination.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/pagination.blade.php) — covers both `<x-pagination>` and [`<x-pagination-simple>`](./pagination-simple.md).

## When to use

- Numbered navigation across many pages where users may want to jump several pages at once.
- Anywhere you'd otherwise use `{{ $items->links() }}` — pass the paginator directly via `:paginator`.
- For cursor-style or "next button only" UX use [`<x-pagination-simple>`](./pagination-simple.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `paginator` | `\Illuminate\Contracts\Pagination\LengthAwarePaginator \| null` | `null` | Laravel paginator instance. When set, `current`, `last`, `total`, `perPage`, and first/last item indices are read from it; the standalone props are ignored. |
| `current` | `int` | `1` | Current page (1-indexed). Used when `paginator` is `null`. |
| `last` | `int` | `1` | Last page number. Used when `paginator` is `null`. |
| `total` | `int` | `0` | Total item count, for the info line. Used when `paginator` is `null`. |
| `perPage` | `int` | `10` | Items per page, for computing first/last item indices when `paginator` is `null`. |
| `baseUrl` | `string \| null` | `null` | When set, items render as `<a href>` links pointing to `{baseUrl}?{pageParam}={n}` (with existing query preserved). When `null`, items are buttons that dispatch a `page-change` event. |
| `pageParam` | `string` | `'page'` | Query-string key used by `baseUrl`. |
| `preserveQuery` | `bool` | `true` | When `true` (and `baseUrl` set), merge the current request's query string into each page URL. |
| `onEachSide` | `int` | `1` | How many page numbers to show on each side of the current page before collapsing into ellipses. e.g. `onEachSide=2` with `current=10` shows `8 9 10 11 12`. Minimum `1`. |
| `showInfo` | `bool` | `true` | Whether to render the "X 件中 Y - Z 件" info line. Hidden automatically when `total=0`. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Item height / padding / text size via `--h-field-*`, `--px-field-*`, `--text-field-*` tune tokens. Also scales the prev/next icon. |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'primary'` | Color applied to the active page item. |
| `appearance` | `'solid' \| 'outline' \| 'soft'` | `'soft'` | Visual style for the active page item: `solid` = filled with `*-content` text; `outline` = white bg with colored border + text; `soft` = `15%` tint bg + colored text + `40%` border. |
| `prevLabel` | `string` | `'前へ'` | `aria-label` for the Previous button (icon-only). |
| `nextLabel` | `string` | `'次へ'` | `aria-label` for the Next button (icon-only). |
| `infoTemplate` | `string` | `'全 :total 件中 :first - :last 件'` | Template for the info line. Placeholders `:total`, `:first`, `:last` are replaced with formatted, bolded numbers. |

All other attributes pass through to the wrapper `<div>`.

## Slots

None.

## Examples

### With a Laravel paginator

```blade
{{-- In your controller: $users = User::paginate(20); --}}
<x-pagination :paginator="$users" :baseUrl="url()->current()" />
```

### Standalone props, client-driven (no `baseUrl`)

Items emit `page-change` events you can listen to with Alpine or Livewire.

```blade
<div @page-change="loadPage($event.detail.page)">
    <x-pagination :current="$page" :last="$lastPage" :total="$total" :perPage="20" />
</div>
```

### Larger window, English info template

```blade
<x-pagination
    :paginator="$rows"
    :baseUrl="url()->current()"
    :onEachSide="2"
    prevLabel="Previous"
    nextLabel="Next"
    infoTemplate="Showing :first – :last of :total"
/>
```

### Solid active color, success

```blade
<x-pagination
    :paginator="$rows"
    appearance="solid"
    color="success"
    size="sm"
/>
```

## Class composition

Class strings come from [`PaginationComposer::compose($props)`](../../src/Compose/PaginationComposer.php) — keys: `wrapper`, `nav` (plain `flex` row), `itemBase` (every item zeroes its own radius via `rounded-none` then restores it on `first:`/`last:` — no daisyUI `join`/`join-item`), `itemActive`, `itemIdle`, `itemDisabled`, `itemStatic`, `infoText`. Border handling is split by state: `itemIdle`/`itemDisabled`/`itemStatic` zero their own left border (`border-l-0`, restored via `first:border-l`) so same-styled neighbors fuse into a single-thickness strip; `itemActive` keeps its own full border on all 4 sides (it has a distinct accent color) and instead overlaps the previous item's right border by one border-width (`-ml-[length:var(--border)] first:ml-0` + `relative z-10`) so the two colors never sit side by side as a doubled line. The window of visible page numbers is computed by `PaginationComposer::windowPages($current, $last, $onEachSide)` and returns `{ pages, showFirst, showLast, showDotsLeft, showDotsRight }`. The composer is **shared** with [`<x-pagination-simple>`](./pagination-simple.md), which uses the `wrapperSimple` key instead of `wrapper` and skips the window logic. URL building (with optional query preservation) lives in `PaginationComposer::buildUrl()`.

## Related

- [`<x-pagination-simple>`](./pagination-simple.md) — Prev / Next only.
- [`<x-table-scroll>`](./table-scroll.md) — wrap tables that pair with pagination so the page itself doesn't flex.

## Notes

- When neither `paginator` nor `baseUrl` is set, every item is a `<button>` that dispatches `page-change` with `{ detail: { page } }`. The wrapper exposes `goToPage(n)` on its Alpine scope.
- When `baseUrl` is set, page items are real `<a href>` — server-rendered navigation works without JS.
- The whole block is wrapped in `@if($hasPages)`; with one page or fewer, nothing renders. `hasPages` comes from the paginator when supplied, or `$last > 1` otherwise.
- The info line is wrapped with `order-2 sm:order-1` and the nav with `order-1 sm:order-2`, so on narrow viewports the nav stacks above the info text.
- Default `prevLabel` / `nextLabel` are Japanese (`前へ` / `次へ`); pass English (or any locale) explicitly for non-JP apps. The visible glyphs are icons — these strings are `aria-label` only.
- `infoTemplate` is HTML-escaped before placeholder substitution, then `:total`/`:first`/`:last` are replaced with already-formatted `<span>`s — safe to render with `{!! !!}`.
