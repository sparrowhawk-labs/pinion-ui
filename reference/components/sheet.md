# x-sheet

A spreadsheet-style editable grid built **purely in Alpine.js + Tailwind (pinion classes)** — a *Locality-of-Behavior* counterpart to [`<x-data-grid>`](./data-grid.md). The two **coexist**: pick `<x-data-grid>` when you want Tabulator's full engine behind a thin wrapper; pick `<x-sheet>` when you want the grid's behavior to live in hand-written, co-located code you own (no third-party engine), styled the same way with theme × tune tokens.

Same public API and the **same `wire:model` data contract** as `<x-data-grid>` (a JSON row-array string), so a host can swap one tag for the other once the matching stage lands.

> **Honest scope.** A spreadsheet is genuinely LoB-tractable (pure data-driven render, no contenteditable/IME). Its mutable state machine — 2D selection, the editing-cell pointer, the clipboard matrix — necessarily lives in a registered Alpine factory (Alpine's reactive proxy corrupts that working state, the same reason `<x-data-grid>` keeps the Tabulator instance in a closure), but the factory is short and its methods read like the spreadsheet they implement, and the per-cell wiring (`x-on:click/dblclick/keydown`, `x-bind:class`) is visible in the markup. The document-level copy/paste/fill listeners are the one part with no markup surface.

## Build status (staged)

`<x-sheet>` is being built incrementally; each stage is independently demoable.

| Stage | Adds | State |
|---|---|---|
| **S0** | Composer (full slot set) + static themed render + theme/tune tracking | **✅ shipped** |
| **S1** | `pinionSheet` Alpine factory: single-cell select, keyboard nav (arrows/Tab/Enter/typing), per-type inline editing, `wire:model` JSON-string round-trip, `wire:ignore` + `:key` morphdom strategy | **✅ shipped** |
| **S1.1+** | number ±-steppers (hover, no-shift), select = always-open custom dropdown (pinion `<x-select>` look), **date editor = [`<x-calendar>`](./calendar.md)** popover, editor-overlay (no cell resize) | **✅ shipped** |
| **S2** | single-range selection (drag-rect, Shift-extend, whole row/column), matrix TSV copy/paste, fill-down, Delete-clear, Cmd/Ctrl+A | **✅ shipped** |
| **S3a** | column sort — header caret toggles asc ⇄ desc (idle glyph on header hover, solid primary ▲/▼ when active), `↺` restores the pre-sort order; empties sort last; destructive (reorders `rows`, flushes) | **✅ shipped** |
| **S3b** | row / column reorder — native HTML5 drag (whole-row / whole-column drag image), drop line indicator, overshoot clamps to first/last, fires `grid-rows-reordered` / `grid-columns-reordered` (same shape as `<x-data-grid>`); a manual order supersedes an active sort | **✅ shipped** |
| **S3c** | fill-handle drag — the square at the range's bottom-right; drag down/right to extend along the dominant axis, **tiles** the source block's values into the new cells (copy only, no numeric series); selection grows to cover the result. Gated by `selectableRange` | **✅ shipped** |
| **S3d** | column resize — drag a header's right edge. The table is content-auto until the first resize, which **lazily freezes** the rendered widths and flips to `table-fixed` (per-column widths become authoritative; widening past the container scrolls horizontally). Width is presentation (rides on `cols`, never flushed), undoable, fires `grid-column-resized` | **✅ shipped** |

The behavior props (`editable`, `sync`, `addRow`, `addColumn`, …) are now live. **Install is required from S1** (the `pinionSheet` factory must be registered — see Install below). **S3 is complete** — next is the host cutover (replacing the M5 `<x-data-grid>` table view).

## Range selection & clipboard (S2)

A **single** rectangular range (anchor + active corner; no disjoint multi-range). Interactions:

| Action | Behavior |
|---|---|
| **Drag** a cell | paint a rectangle (mousedown → drag → mouseup) |
| **Shift+click** / **Shift+arrows** | extend the range from the fixed anchor |
| **Row-number click** | select the whole row (Shift = a block of rows) |
| **Header click** | select the whole column (Shift = a block of columns) |
| **Cmd/Ctrl+C** | copy the range to the clipboard as **TSV** (rows `\n`, cells `\t`) |
| **Cmd/Ctrl+V** | paste TSV from the active top-left, **clipped to existing bounds** (a single value fills the whole range; row/column growth stays the host's job) |
| **Cmd/Ctrl+D** | fill-down — copy the range's top row into the rows below |
| **Drag the fill handle** | the square at the range's bottom-right; drag down/right to **tile** the source block's values into the new cells *(S3c)* |
| **Delete / Backspace** | clear every cell in the range (`null`; checkbox → `false`) |
| **Cmd/Ctrl+A** | select the whole grid |
| **Cmd/Ctrl+Z** | undo the last change — steps back one mutation (edit / sort / reorder / fill / paste / clear / add row·column); repeatable to the load state |

**Selection visual.** The range is drawn as **one continuous 2px primary border** around the whole block (an inset box-shadow on the perimeter cells; the just-outside gridline is set transparent so the stroke is never doubled — no layout shift). The interior gets a faint uniform `bg-primary/10` wash (multi-cell only); a single selected cell is border-only. There is **no per-cell "active cell" outline** — the selection reads as one block. The selected row-number(s) and column-header(s) get a **subtle connected indicator** (a faint primary wash + the label in primary), so the block visibly ties back to its row/column labels on every theme. Clipboard requires a secure context (localhost/https) and a user gesture; a denied read is a silent no-op.

## Install

`<x-sheet>` needs **no third-party package** (unlike `<x-data-grid>`'s `tabulator-tables`) — just the opt-in Alpine factory registration. The `.pn-sheet` theme map (`sheet.css`) is bundled by `pinion-ui.css`. The date editor reuses [`<x-calendar>`](./calendar.md), so install that factory too.

```bash
php artisan ui:install --sheet --calendar     # registers pinionSheet + pinionCalendar (no npm dep)
npm run build
```

This adds to `resources/js/app.js` (before `Alpine.start()` / `Livewire.start()`):

```js
import { pinionSheet } from '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/sheet.js';
import { pinionCalendar } from '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/calendar.js';
Alpine.data('pinionSheet', pinionSheet);
Alpine.data('pinionCalendar', pinionCalendar);
```

> A **Livewire-ESM single-Alpine** host (e.g. nonblock) registers these with a **manual** `app.js` edit — its `app.js` is hand-authored and does not run `ui:install`'s injection.

## When to use

- The same tabular-edit use cases as `<x-data-grid>`, when you prefer owning the grid behavior in readable hand-written code over a third-party engine.
- For read-only or richly-laid-out tables, use a plain `<table>` (optionally inside [`<x-table-scroll>`](./table-scroll.md)).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `columns` | `array` | `[]` | Column specs: `['key'=>…, 'title'=>…, 'type'=>…, 'options'=>[…], 'width'=>…, 'sortable'=>true]`. `type` ∈ `text` `number` `date` `select` `checkbox` (default `text`). |
| `rows` | `array` | `[]` | Initial row objects, each keyed by column `key` (+ optional `id`). |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Base text size + cell padding off the tune scale (text-entry `--px-input-*` / `--text-field-*`). |
| `height` | `string \| null` | `null` | Max viewport height (e.g. `'24rem'`) → vertical scroll with a sticky header. `null` = grows with rows. |
| `editable` | `bool` | `true` | Master switch for cell editing. *(S1)* |
| `selectableRange` | `bool` | `true` | Range selection + clipboard + fill. *(S2)* |
| `sortable` | `bool` | `true` | Header-caret sort (asc ⇄ desc, `↺` restores original order). *(S3a)* |
| `rowNumbers` | `bool` | `true` | Frozen row-number gutter. |
| `movableRows` | `bool` | `false` | Drag rows to reorder → fires `grid-rows-reordered`. *(S3b)* |
| `movableColumns` | `bool` | `false` | Drag column headers to reorder → fires `grid-columns-reordered`. *(S3b)* |
| `resizableColumns` | `bool` | `true` | Drag a header's right edge to resize → fires `grid-column-resized`. First resize freezes widths to `table-fixed`. *(S3d)* |
| `toolbar` | `bool` | `true` | Built-in top bar: a **toolbox** of icon-only ops + row count (+ `actions` slot + mobile help). |
| `addRow` | `bool` | `true` | Show the built-in add-row icon button in the toolbox. *(inert at S0; dispatches to host in S1)* |
| `addColumn` | `bool` | `true` | Show the built-in add-column icon button in the toolbox. *(inert at S0; dispatches to host in S1)* |
| `sync` | `string` | `'debounce:400'` | `wire:model` flush cadence: `'change'`, `'debounce:NNN'`, or `'manual'`. *(S1)* |
| `addRowLabel` | `string` | `'行を追加'` | Accessible label / tooltip for the add-row icon button (icon-only — this is the `aria-label` + `title`, and the row of the mobile help modal). |
| `addColumnLabel` | `string` | `'列を追加'` | Same, for the add-column icon button. |

All other attributes pass through to the root `<div>`. From S1, `wire:model` is forwarded to a dedicated hidden `<input>` (string carrier).

## Toolbar (toolbox)

Operations are **icon-only square buttons** grouped in a soft **toolbox** (`role="toolbar"`), kept minimal: the add-row / add-column icons read "+ a row" / "+ a column". On desktop the `title`/`aria-label` give hover tooltips. On a **phone** (where touch has no hover), a **`?` button** (`sm:hidden`) opens a small guide **modal** naming each operation — a pure inline-Alpine `x-data="{ help:false }"` toggle (no `wire`, no JS factory; the sanctioned leaf-UI pattern, cf. `<x-editor>`'s shortcut popover). Consumers must ship the standard `[x-cloak]{display:none}` rule so the modal doesn't flash on load.

## Slots

- **actions** — markup appended **into the toolbox**, after the built-in add buttons (e.g. extra host-owned icon ops). Only rendered when `toolbar` is true.

## Data contract (from S1 — identical to `<x-data-grid>`)

The sheet will flush its rows as a **JSON string** to the hidden `wire:model` carrier — a JSON array of row objects, each keyed by column `key` plus the `id` the host reconciles on. This is **byte-shape identical** to `<x-data-grid>`, so the host code (a `public string` property + `json_decode` + a no-op-guarded reconcile) is unchanged across the two components:

```json
[ { "id": 12, "item": "初期構築", "qty": 1, "category": "開発" },
  { "id": 13, "item": "月額保守", "qty": 12, "category": "保守" } ]
```

**Bind it to a `string` property and `json_decode` it** — initial data goes IN via the `:rows` prop (a real PHP array): array in, JSON-string out. **Structural changes (add/remove rows/columns) are the host's job** (the host persists, then re-seeds by bumping the Livewire `:key`); the component never invents ids or schema. Drag-reorder dispatches bubbling `grid-rows-reordered` / `grid-columns-reordered` `CustomEvent`s carrying the new order — same names and `detail.order` shape as `<x-data-grid>`. Column resize dispatches `grid-column-resized` with `detail.{key, width}` (width is presentation-only — it is never written to the carrier, so a Livewire host can ignore it or persist it as it likes).

## Examples

### Static render (S0 — what ships today)

```blade
<x-sheet
    :columns="[
        ['key' => 'item', 'title' => '品目', 'type' => 'text'],
        ['key' => 'qty', 'title' => '数量', 'type' => 'number'],
        ['key' => 'done', 'title' => '完了', 'type' => 'checkbox'],
    ]"
    :rows="[
        ['id'=>1,'item'=>'初期構築','qty'=>1,'done'=>true],
        ['id'=>2,'item'=>'月額保守','qty'=>12,'done'=>false],
    ]"
/>
```

### Livewire host (from S1 — drop-in with `<x-data-grid>`)

```blade
<x-sheet :columns="$gridColumns" :rows="$this->rowsData()" :toolbar="false" wire:model.live="rowsJson" />
```

## Class composition

Chrome and every cell/header/gutter/handle slot → [`SheetComposer`](../../src/Compose/SheetComposer.php) (a pure flat class-string dict; the full slot set — `cellSelected` / `cellEditing` / `cellInRange` / `resizeHandle` / `fillHandle` / `numStepper` / `checkCell` … — is enumerated from S0 so the contract and the Compose fixture are complete before the behavior that uses each slot lands). Descendant rules it can't express as utilities — grid lines, the sticky header, the composed checkbox glyph — live in [`sheet.css`](../../src/resources/css/sheet.css) under a clean `@layer components` (no engine CSS to out-specify, unlike `data-grid.css`). Never daisyUI component classes; cells use the `--px-input-*` text-entry padding scale, not `--px-field-*`.

## Related

- [`<x-data-grid>`](./data-grid.md) — the Tabulator-backed grid `<x-sheet>` coexists with (same data contract).
- [`<x-editor>`](./editor.md) / `<x-doc>` — the rich-text pair (engine-backed vs hand-maintained), the same coexistence split.
- [`<x-table-scroll>`](./table-scroll.md) — overflow wrapper for static, non-editable tables.
