# x-data-grid

A spreadsheet-style data grid backed by [Tabulator](https://tabulator.info) (MIT). Inline cell editing per column type, **range selection · clipboard copy/paste · fill handle**, sortable + resizable + reorderable columns — styled purely with theme × tune tokens. Emits a JSON row-array for `wire:model`.

**Opt-in JS** (like `<x-editor>`): the Tabulator engine is wired only by `php artisan ui:install --data-grid`. Non-grid apps pay zero JS/CSS engine cost. The small theme map (`data-grid.css`) is bundled by the preset and is inert until the grid is used.

## Install

```bash
php artisan ui:install --data-grid     # adds tabulator-tables npm dep + base CSS + app.js registration
npm install && npm run build
```

This adds to `resources/js/app.js` (idempotent; works with both a vanilla-Alpine and a Livewire-ESM single-Alpine bundle — the registration is placed before `Alpine.start()` / `Livewire.start()`):

```js
import 'tabulator-tables/dist/css/tabulator.min.css';
import { pinionDataGrid } from '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/data-grid.js';
Alpine.data('pinionDataGrid', pinionDataGrid);
```

## When to use

- Tabular data the user edits in place — a spreadsheet-like surface (range select, copy/paste, fill).
- When columns map to a fixed set of typed cells (text / number / date / select / checkbox).
- For read-only or richly-laid-out tables, use a plain `<table>` (optionally inside [`<x-table-scroll>`](./table-scroll.md)) — the grid engine is overkill there.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `columns` | `array` | `[]` | Column specs: `['key'=>…, 'title'=>…, 'type'=>…, 'options'=>[…], 'width'=>…, 'editable'=>true]`. `type` ∈ `text` `number` `date` `select` `checkbox` (default `text`). `options` is the value list for `select`. |
| `rows` | `array` | `[]` | Initial row objects, each keyed by column `key` (+ optional `id`). |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Base text size off the tune scale. |
| `height` | `string \| null` | `null` | Grid viewport height (e.g. `'24rem'`) for virtual scroll. `null` = auto-height (grows with rows). |
| `layout` | `string` | `'fitColumns'` | Tabulator layout mode (`fitColumns`, `fitData`, `fitDataStretch`, …). |
| `editable` | `bool` | `true` | Master switch for cell editing. |
| `selectableRange` | `bool` | `true` | Spreadsheet range selection + clipboard copy/paste + fill handle. Off → simple per-cell editing with header sort. |
| `rowNumbers` | `bool` | `true` | Frozen row-number gutter (also anchors the range fill handle, and the drag handle for `movableRows`). |
| `movableRows` | `bool` | `false` | Drag rows to reorder — by the gutter handle (in range mode a cell drag selects, so row-move needs the handle). Fires `grid-rows-reordered`. |
| `movableColumns` | `bool` | `false` | Drag column headers to reorder. Fires `grid-columns-reordered`. |
| `toolbar` | `bool` | `true` | Show the built-in top bar (add-row button + row count + `actions` slot). |
| `sync` | `string` | `'debounce:400'` | `wire:model` flush cadence: `'change'` (flush once per edit), `'debounce:NNN'` (flush NNN ms after the last edit), or `'manual'` (never auto-flush — the host calls the exposed `flush()` Alpine method itself). |
| `addRowLabel` | `string` | `'＋ 行を追加'` | Label for the built-in add-row button. |

All other attributes pass through to the root `<div>`. `wire:model` is forwarded to a dedicated hidden `<input>` (see Data contract).

## Slots

- **actions** — markup placed at the right end of the toolbar (e.g. a host-owned "add column" button). Only rendered when `toolbar` is true.

## Events (drag-reorder)

When `movableRows` / `movableColumns` are on, a successful drag dispatches a bubbling `CustomEvent` on the root carrying the **new order**. The host persists `position` from it (the component never persists order itself — same generic split as add/remove).

| Event | `detail.order` |
|---|---|
| `grid-rows-reordered` | array of row `id`s in the new top-to-bottom order |
| `grid-columns-reordered` | array of column `key`s in the new left-to-right order |

```blade
<x-data-grid
    :columns="$this->columns()" :rows="$this->rowsData()"
    :movable-rows="true" :movable-columns="true"
    wire:model.live="rowsJson"
    x-on:grid-rows-reordered="$wire.reorderRows($event.detail.order)"
    x-on:grid-columns-reordered="$wire.reorderColumns($event.detail.order)"
/>
```

```php
public function reorderRows(array $order): void   // ids, new order
{
    $valid = $this->records()->pluck('id')->all();
    $pos = 1;
    foreach ($order as $id) {
        if (in_array($id, $valid, true)) Record::where('id', $id)->update(['position' => $pos++]);
    }
}
// reorderColumns is the same shape over field keys → fields.position.
```

> Use the full Alpine prefix `x-on:` (not `@`) on the `<x-…>` element — Blade intercepts `@`/`:`. `$wire` / `$event` are Alpine magics, evaluated at runtime.

**Checkbox cells** render a pinion-styled box (`.pn-grid-check`, themed in `data-grid.css` to match `<x-checkbox>`), not Tabulator's default tick glyph, and toggle on a single click.

## Data contract

The grid flushes its rows as a **JSON string** to the hidden `wire:model` carrier (the same hidden-input mechanism `<x-editor>` uses — a hidden input always transmits a string). The string is a JSON array of row objects, each keyed by column `key`, plus any `id` the host uses to reconcile:

```json
[ { "id": 12, "item": "初期構築", "qty": 1, "category": "開発" },
  { "id": 13, "item": "月額保守", "qty": 12, "category": "保守" } ]
```

**Bind it to a `string` property and `json_decode` it** — do NOT type the property `array` (Livewire throws *"Cannot assign string to property of type array"*). Initial data goes IN via the `:rows` prop (a real PHP array), so the two directions use different shapes: array in, JSON-string out.

The grid is the edit surface for the rows it is **given**. Cell edits, range paste, and fill flush the full current array back through the hidden input (per `sync`). **Structural changes (add/remove ROWS or COLUMNS) are the host's job** — the host persists, then re-seeds the grid by bumping the Livewire `:key`, so new ids and columns flow in on a fresh mount. The component never invents ids or schema, which keeps it generic across stores.

The grid host `<div>` carries `wire:ignore` so Livewire's morphdom never reconciles Tabulator's generated DOM against the empty server `<div>`. A `:key` change still forces a fresh mount (re-seed) — `wire:ignore` only blocks morphing of a *persisted* element.

**Initial seed flush (make your handler idempotent).** On mount (and on every `:key` re-seed) the grid flushes the seeded rows **once**, unconditionally, regardless of `sync` — this populates the carrier so steady-state edits compare correctly. With `wire:model.live` that means your `updated…()` runs on every mount with data identical to the store. Guard it with a no-op check (skip the write when the decoded row equals the stored row) so a page load / re-seed doesn't re-write every row. The example below does this. On re-seed teardown the grid also flushes any pending debounced edit so it isn't lost.

## Examples

### Livewire (host owns persistence, add/remove)

```blade
{{-- your own Livewire host component (e.g. ⚡table-view) that wraps <x-data-grid>.
     The :key re-seeds the grid after a structural change (add/remove row/column). --}}
<livewire:your-table-view :app-id="$app->id" :key="'grid-'.$app->id.'-'.$gridKey" />
```

```blade
{{-- inside your host component's Blade. :rows is a PHP array (IN); wire:model.live
     binds the JSON-string carrier (OUT). Use .live so each flush auto-saves. --}}
<x-data-grid
    :columns="$gridColumns"
    :rows="$this->rowsData()"
    :toolbar="false"
    wire:model.live="rowsJson"
/>
```

```php
// host Livewire component: the carrier is a JSON STRING — decode, then reconcile by id.
// Guard each write (no-op when unchanged) so the initial seed flush / re-seed doesn't
// re-write every row on mount.
public string $rowsJson = '';

public function updatedRowsJson(): void
{
    $rows = json_decode($this->rowsJson, true);
    if (! is_array($rows)) return;
    foreach ($rows as $row) {
        if (! isset($row['id'])) continue;            // new rows handled server-side
        $next = collect($row)->except('id')->all();
        $record = Record::find($row['id']);
        if ($record && $record->data !== $next) {     // !== (not !=) so 0/null/''/false stay distinct
            $record->update(['data' => $next]);
        }
    }
}
```

### Standalone (built-in toolbar, no Livewire)

```blade
<x-data-grid
    :columns="[
        ['key' => 'item', 'title' => '品目', 'type' => 'text'],
        ['key' => 'qty', 'title' => '数量', 'type' => 'number'],
        ['key' => 'category', 'title' => '区分', 'type' => 'select', 'options' => ['開発','保守','デザイン']],
    ]"
    :rows="[['id'=>1,'item'=>'初期構築','qty'=>1,'category'=>'開発']]"
/>
```

## Class composition

Chrome (shell / toolbar / button / count / grid host) → [`DataGridComposer`](../../src/Compose/DataGridComposer.php). Tabulator's *internals* are recoloured by overriding its own selectors (`.tabulator-header`, `.tabulator-row`, `.tabulator-range`, the row-number gutter, edit widgets, …) with daisyUI color tokens + tune tokens in [`data-grid.css`](../../src/resources/css/data-grid.css), so the grid tracks data-theme × data-tune. (Tabulator v6 ships compiled literal-hex CSS with no runtime variables, so direct selector overrides — not a `--tabulator-*` var map — are how theming works.) Never daisyUI component classes.

## Notes

- **Closure-held instance**: the Tabulator object lives in the JS module's closure, never in Alpine reactive data (Alpine's proxy breaks the grid's bookkeeping) — same lesson as `<x-editor>`.
- **Extending the type set**: add a `case` to `toTabulatorColumn()` in `data-grid.js` (its own editor/formatter/sorter) — a few lines, no fork. The Blade/Composer/contract are unchanged.
- **Dates** use the native date editor and sort as ISO strings (lexical = chronological) — no luxon dependency.
- **`reactiveData: false`** is deliberate: the host, not Tabulator, owns the source of truth via `wire:model`.

## Related

- [`<x-table-scroll>`](./table-scroll.md) — overflow wrapper for static, non-editable tables.
- [`<x-editor>`](./editor.md) — the other opt-in JS-behavior component (Tiptap rich text).
