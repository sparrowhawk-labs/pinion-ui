# x-table

Data table — the standard way to render tabular records (admin ledgers, dashboards, listing pages). Owns the whole "table in a card" recipe that used to be re-invented by hand: card shell, header-row styling, cell rhythm, row hover, last-row border removal, right-aligned `tabular-nums` numeric columns, edge-column padding that aligns with `x-card`'s `p-lg`, and an empty-state placeholder. Composes as a parent with nested cell children (like `<x-tabs>` / `<x-accordion>`); body rows stay **plain `<tr>`** so `wire:key` / `@forelse` work with zero wrapper friction.

**Playground page**: [`pinion-ui-playground/resources/views/pages/table.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/table.blade.php) — full variant matrix and live demos.

## When to use

- Any read-mostly tabular listing: admin indexes, reports, ranking/ledger views.
- Editable spreadsheet-like data → [`<x-data-grid>`](./data-grid.md) / [`<x-sheet>`](./sheet.md) instead.
- Wide tables on narrow viewports: the shell already scrolls (`overflow-x-auto`); wrap in [`<x-table-scroll>`](./table-scroll.md) only when you want the fade + button affordance.

## Components

| Component | Renders | Role |
|---|---|---|
| `<x-table>` | shell `<div>` + `<table>` + `<thead>`/`<tbody>` | Parent — owns card face, density, hover, row dividers. |
| `<x-table.th>` | `<th>` | One header cell. `numeric`/`action` right-align it. |
| `<x-table.td>` | `<td>` | One body cell. `numeric` = right + `tabular-nums`; `muted` dims; `action` = right + nowrap. |
| `<x-table.empty>` | `<tr><td colspan>` | Empty-state placeholder row (put it in `@empty`). |

## Props

### `<x-table>`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Row density. `sm` = compact + `text-xs` body, `md` = `py-md` cells / `text-sm`, `lg` = roomy. |
| `card` | `bool` | `true` | `true` = the table owns its card face (`tune-border` + `rounded-[var(--radius-box)]` + `bg-base-100`) — use `<x-table>` directly on the page. `false` = bare table, for embedding inside an existing `<x-card :padding="false">`. |
| `hover` | `bool` | `true` | Row hover highlight (`bg-base-200/60`) + color transition on body rows. |

All other attributes pass through to the shell `<div>` — `class` for margins, and `wire:poll` works here too.

### `<x-table.th>` / `<x-table.td>`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `numeric` | `bool` | `false` | Right-aligns; on `td` also adds `tabular-nums` so digit columns line up. Use for counts, amounts, dates-as-numbers. |
| `action` | `bool` | `false` | Right-aligns; on `td` also adds `whitespace-nowrap`. Use for the trailing buttons/links column (on the matching `th`, usually with an empty slot: `<x-table.th action />`). |
| `align` | `'left' \| 'center' \| 'right' \| null` | `null` | Explicit override; wins over `numeric`/`action`. |
| `muted` (`td` only) | `bool` | `false` | `text-base-content/60` — secondary figures that shouldn't compete with the key column. |

`size` flows from the parent via `@aware` — never pass it on cells.

## Slots

- **`head`** — the header cells (`<x-table.th>` ×N). The parent wraps them in `<thead><tr>`. Omit for headerless tables.
- **default** — body rows: plain `<tr>` elements (divider/hover styling is injected from the parent, incl. `last:border-b-0`). Put `wire:key` on each `<tr>` in Livewire loops as usual.
- `<x-table.empty>`'s default slot — the placeholder content; spans all columns automatically.

## Examples

### Basic (own card face — just drop it on the page)

```blade
<x-table>
    <x-slot:head>
        <x-table.th>名前</x-table.th>
        <x-table.th numeric>件数</x-table.th>
        <x-table.th action />
    </x-slot:head>

    @forelse ($items as $item)
        <tr wire:key="item-{{ $item->id }}">
            <x-table.td>{{ $item->name }}</x-table.td>
            <x-table.td numeric>{{ $item->count }}</x-table.td>
            <x-table.td action>
                <x-button size="xs" appearance="ghost">編集</x-button>
            </x-table.td>
        </tr>
    @empty
        <x-table.empty>まだデータがありません</x-table.empty>
    @endforelse
</x-table>
```

### Real-world ledger (the cadence-v2 track table, verbatim shape)

Mixed columns — link column, plain numerics, a styled fraction, a pill link, a toggle, and a trailing action. `wire:poll` sits on the component root.

```blade
<x-table wire:poll.15s>
    <x-slot:head>
        <x-table.th>トラック</x-table.th>
        <x-table.th numeric>テーマ</x-table.th>
        <x-table.th numeric>ストック</x-table.th>
        <x-table.th numeric>未推敲</x-table.th>
        <x-table.th numeric>1日枠</x-table.th>
        <x-table.th numeric>有効</x-table.th>
        <x-table.th action />
    </x-slot:head>

    @forelse ($tracks as $track)
        <tr wire:key="track-{{ $track->id }}">
            <x-table.td>
                <a href="{{ route('tracks.show', $track) }}"
                   class="font-medium transition-colors hover:text-base-content/70">{{ $track->name }}</a>
            </x-table.td>
            <x-table.td numeric muted>{{ $track->themes_count }}</x-table.td>
            <x-table.td numeric>
                <span class="font-medium">{{ $track->draft_count }}</span>
                <span class="text-base-content/35"> / {{ $track->stock_target }}</span>
            </x-table.td>
            <x-table.td numeric>
                @if ($track->draft_count > 0)
                    <a href="{{ route('tracks.review', $track) }}"
                       class="inline-flex items-center rounded-full bg-base-300/70 px-sm py-3xs text-xs font-medium transition-colors hover:bg-base-300">{{ $track->draft_count }} 件</a>
                @else
                    <span class="text-base-content/30">—</span>
                @endif
            </x-table.td>
            <x-table.td numeric muted>{{ $track->daily_cap }}</x-table.td>
            <x-table.td align="right">
                <x-toggle size="sm" :checked="$track->enabled" wire:click="toggleEnabled({{ $track->id }})" />
            </x-table.td>
            <x-table.td action>
                <button type="button" wire:click="startGeneration({{ $track->id }})"
                        class="text-xs text-base-content/40 transition-colors hover:text-base-content">＋ 生成</button>
            </x-table.td>
        </tr>
    @empty
        <x-table.empty>
            <p class="text-sm text-base-content/55">トラックがまだありません</p>
            <p class="mt-2xs text-xs text-base-content/35">Claude Code から作成できます</p>
        </x-table.empty>
    @endforelse
</x-table>
```

### Inside an existing `<x-card>` (header/footer chrome around the table)

```blade
<x-card :padding="false">
    <x-slot:header>
        <div class="p-lg flex items-baseline justify-between">
            <h2 class="text-sm font-semibold">今月の請求</h2>
            <span class="text-xs text-base-content/40">{{ $rows->count() }} 件</span>
        </div>
    </x-slot:header>

    <x-table :card="false">
        <x-slot:head>…</x-slot:head>
        …
    </x-table>
</x-card>
```

`:card="false"` drops the table's own border/rounding; the first/last-column `pl-lg`/`pr-lg` keep cell text aligned with the card header's `p-lg`.

### Compact / roomy density

```blade
<x-table size="sm">…</x-table>
<x-table size="lg" :hover="false">…</x-table>
```

## Class composition

See [`src/Compose/TableComposer.php`](../../src/Compose/TableComposer.php). Returns `shell`, `table`, `headRow`, `tbody`, `th`, `td`, `empty`. Two mechanics worth knowing:

- **Rows stay plain `<tr>`** because the divider/hover/last-row rules live on `<tbody>` as arbitrary-variant selectors (`[&>tr]:border-b … [&>tr:not(.pn-table-empty):hover]:bg-base-200/60`).
- **Edge-column padding** (`pl-lg`/`pr-lg` on first/last cells) lives on `<table>` as `[&_th:first-child]:pl-lg …` selectors — this is what aligns the table's content edge with `x-card`'s `p-lg` sections, the recipe that used to be hand-written per app.

## Related

- [`<x-table-scroll>`](./table-scroll.md) — fade + button horizontal-scroll affordance for very wide tables.
- [`<x-data-grid>`](./data-grid.md) / [`<x-sheet>`](./sheet.md) — *editable* spreadsheet-style grids; `x-table` is read-mostly display.
- [`<x-pagination>`](./pagination.md) — pair under the table for long listings.
- [`<x-card>`](./card.md) — wrap with `:padding="false"` + `<x-table :card="false">` when you need card header/footer chrome.

## Notes

- The head divider width is tune-reactive (`var(--border)` — thick under `brutal`); body-row dividers are a fixed hairline so dense tables stay light in every tune.
- The empty row carries `.pn-table-empty`, which excludes it from the hover highlight.
- Don't put `wire:model` anywhere on `x-table` — it renders server data; interactive cells (toggles, buttons) carry their own `wire:*` as in the examples.
- Nested `<table>` inside a cell would inherit the edge-padding selectors — don't nest tables (use a second `<x-table>` in a detail row's own container if you must).
