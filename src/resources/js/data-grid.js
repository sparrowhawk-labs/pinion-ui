// ─────────────────────────────────────────────────────────────────────────────
// pinion-ui · <x-data-grid> behavior module  (Tabulator / spreadsheet grid)
// ─────────────────────────────────────────────────────────────────────────────
//
// pinion-ui's SECOND JS-behavior component (after <x-editor>). OPT-IN: a consumer
// imports it (or lets `php artisan ui:install --data-grid` wire it) into their
// resources/js/app.js. Non-grid apps never import it and pay ZERO JS bundle cost.
// The Blade library itself ships no bundled JS.
//
// Consumer wiring (what ui:install --data-grid injects):
//
//     import Alpine from 'alpinejs';
//     import 'tabulator-tables/dist/css/tabulator.min.css';
//     import { pinionDataGrid } from
//       '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/data-grid.js';
//     Alpine.data('pinionDataGrid', pinionDataGrid);
//
// Requires this npm dep in the consumer (added by ui:install --data-grid):
//   tabulator-tables  (^6 — MIT; TabulatorFull bundles edit/format/sort/filter/
//   clipboard/selectRange modules, i.e. the full spreadsheet feature set, no paid
//   tier). Theming overrides Tabulator's own selectors with daisyUI/tune tokens in
//   data-grid.css (v6 ships literal-hex CSS, no runtime variables) — see that file.
//
// ── Data contract ────────────────────────────────────────────────────────────
// The wire:model value is a JSON array of row objects, each keyed by column
// `field` (= the consumer's stable cell key), plus an optional `id` the consumer
// uses to reconcile against its own store:
//
//     [ { "id": 12, "item": "初期構築", "qty": 1, "category": "開発" }, … ]
//
// The grid is the edit surface for the rows it is GIVEN: cell edit, range paste,
// and fill flush the full current array back through the hidden input (debounced).
// Structural changes (add/remove ROWS or COLUMNS) are the consumer's job — it
// persists, then re-seeds the grid (bump the Livewire :key) so new ids/columns
// flow in. This keeps the component generic (it never invents ids or schema).
// See reference/components/data-grid.md §Data contract.
//
// ── Spike finding, load-bearing (same lesson as editor.js) ───────────────────
// The Tabulator instance MUST NOT live in Alpine's reactive data — Alpine proxies
// every property and a proxied grid instance breaks its internal bookkeeping.
// Keep it in this closure; expose only plain serializable state (rowCount) to
// Alpine. The grid host MUST carry `wire:ignore` (the Blade sets it) so Livewire's
// morphdom never reconciles Tabulator's generated DOM against the empty server div.

import { TabulatorFull as Tabulator } from 'tabulator-tables';

const truthy = (v) => v === true || v === 1 || v === '1' || v === 'true';

// pinion-styled checkbox cell (Tabulator's default tickCross glyph is plain). Renders
// a box themed in data-grid.css (.pn-grid-check) matching <x-checkbox> / the editor's
// task-list checkbox. Toggled via a cellClick handler (not an editor box).
function pnCheckFormatter(cell) {
  const on = truthy(cell.getValue());
  return `<span class="pn-grid-check${on ? ' is-checked' : ''}" role="checkbox" aria-checked="${on}"></span>`;
}

// pinion-styled number cell: the value flanked by transparent minimal − / + steppers
// (data-grid.css .pn-grid-num). The native <input type=number> spinners are tiny and,
// in range mode, a click on them lands as a range-select; these buttons stop that
// propagation and step via setValue (→ cellEdited → flush). Double-click still edits.
function pnNumberFormatter(cell) {
  const wrap = document.createElement('div');
  wrap.className = 'pn-grid-num';

  const stepper = (label, delta) => {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'pn-grid-num-btn';
    b.textContent = label;
    b.tabIndex = -1;
    // mousedown.stop keeps the range-selection from starting on the cell; click steps.
    b.addEventListener('mousedown', (e) => e.stopPropagation());
    b.addEventListener('click', (e) => {
      e.stopPropagation();
      const cur = Number(cell.getValue());
      cell.setValue((Number.isFinite(cur) ? cur : 0) + delta);
    });
    return b;
  };

  const v = cell.getValue();
  const val = document.createElement('span');
  val.className = 'pn-grid-num-val';
  val.textContent = (v === null || v === undefined || v === '') ? '' : v;

  wrap.append(stepper('−', -1), val, stepper('+', 1));
  return wrap;
}

// Map a generic pinion column spec → a Tabulator column definition.
// Generic spec: { key, title, type, options?, width?, editable? }
//   type ∈ text | number | date | select | checkbox  (default: text)
function toTabulatorColumn(col, editable, sortable) {
  const def = {
    title: col.title ?? col.key,
    field: col.key,
    editable: editable && col.editable !== false,
    resizable: 'header',
    // Sorting is triggered by the small sort-arrow icon only (headerSortClickElement
    // below), so a header-body click can still select the column in range mode.
    headerSort: sortable,
  };
  if (col.width) def.width = col.width;

  switch (col.type) {
    case 'number':
      def.editor = 'number';            // dblclick → clean typed input (spinners hidden in CSS)
      def.formatter = pnNumberFormatter; // display: value flanked by − / + steppers
      def.sorter = 'number';
      def.hozAlign = 'right';
      break;
    case 'date':
      def.editor = 'date';          // native <input type=date> (no luxon dep)
      def.sorter = 'string';        // ISO dates sort lexically — correct + dep-free
      break;
    case 'select':
      def.editor = 'list';
      def.editorParams = {
        values: Array.isArray(col.options) ? col.options : [],
        autocomplete: true,
        listOnEmpty: true,
        clearable: true,
      };
      break;
    case 'checkbox':
      def.formatter = pnCheckFormatter;   // pinion-styled box (see data-grid.css)
      def.editor = false;                 // toggled via cellClick, not an editor box
      def.hozAlign = 'center';
      break;
    default:
      def.editor = 'input';
      def.sorter = 'string';
  }
  return def;
}

/**
 * Alpine component factory.
 *
 * @param {object}   opts
 * @param {Array}    [opts.columns]          generic column specs (see toTabulatorColumn)
 * @param {Array}    [opts.rows]             initial row objects
 * @param {string}   [opts.height]           grid viewport height (e.g. '24rem'); null = auto
 * @param {string}   [opts.layout]           Tabulator layout ('fitColumns' default)
 * @param {boolean}  [opts.editable]         default true
 * @param {boolean}  [opts.selectableRange]  spreadsheet range select / clipboard / fill (default true)
 * @param {boolean}  [opts.rowNumbers]       show a frozen row-number gutter (default true)
 * @param {boolean}  [opts.movableRows]      drag rows to reorder (by the gutter handle); fires 'grid-rows-reordered'
 * @param {boolean}  [opts.movableColumns]   drag column headers to reorder; fires 'grid-columns-reordered'
 * @param {string}   [opts.sync]             'change' | 'debounce:NNN' | 'manual'  (default 'debounce:400')
 *
 * Reorder events (dispatched on the root, bubbling) carry { order: [...] } — the new
 * id order (rows) / field-key order (columns). The host persists position from them.
 */
export function pinionDataGrid(opts = {}) {
  // NON-reactive closure state (see load-bearing note above).
  let table = null;
  let flushTimer = null;
  let flushPending = false;   // microtask-dedupe guard for 'change' mode

  const sync = String(opts.sync ?? 'debounce:400');
  const syncMode = sync.split(':')[0]; // change | debounce | manual
  const debounceMs = sync.startsWith('debounce')
    ? (parseInt(sync.split(':')[1], 10) || 400)
    : 400;

  return {
    rowCount: 0,

    init() {
      const range = opts.selectableRange !== false;
      const sortable = opts.sortable !== false;
      // Deep-clone props out of Alpine's reactive proxy before handing them to
      // Tabulator (it mutates column/row objects internally).
      const specs = JSON.parse(JSON.stringify(opts.columns ?? []));
      const cols = specs.map((c) => toTabulatorColumn(c, opts.editable !== false, sortable));
      const rows = JSON.parse(JSON.stringify(opts.rows ?? []));
      // field → pinion type, read by the checkbox click-toggle below.
      const colTypes = Object.fromEntries(specs.map((c) => [c.key, c.type]));

      const config = {
        data: rows,
        columns: cols,
        index: 'id',
        reactiveData: false,
        layout: opts.layout ?? 'fitColumns',
        height: opts.height || undefined,
        movableColumns: !!opts.movableColumns,
        movableRows: !!opts.movableRows,
        columnDefaults: { headerSort: sortable, resizable: 'header' },
        // Sort only when the small sort-arrow icon is clicked, so a header-body
        // click still selects the column in range mode (minimal sort buttons).
        headerSortClickElement: 'icon',
      };

      if (opts.rowNumbers !== false) {
        // Frozen row-number gutter — also the corner the range fill handle anchors
        // against, and (when movableRows) the drag handle for reordering rows: in
        // range mode dragging a CELL selects a range, so row-move needs an explicit
        // handle rather than a free row drag.
        config.rowHeader = {
          formatter: 'rownum', hozAlign: 'center', headerSort: false,
          resizable: false, frozen: true, width: 44, editor: false,
          rowHandle: !!opts.movableRows,
        };
      }

      if (range) {
        Object.assign(config, {
          // 1 (not true): a single range at a time, so each new plain selection
          // (cell / column / row) REPLACES the previous — clears the prior cell
          // selection. Drag-rectangle and Shift-extend still work within the one
          // range; only Cmd-to-add-a-second-range is disabled.
          selectableRange: 1,
          selectableRangeColumns: true,
          selectableRangeRows: true,
          editTriggerEvent: 'dblclick',   // single-click selects, dbl-click edits
          clipboard: true,
          clipboardCopyStyled: false,
          clipboardCopyRowRange: 'range',
          clipboardPasteParser: 'range',
          clipboardPasteAction: 'range',
        });
      }

      table = new Tabulator(this.$refs.grid, config);

      table.on('tableBuilt', () => {
        this.rowCount = table.getDataCount();
        this.flush();                 // seed wire:model with the initial array
      });
      // cellEdited covers typed edits; dataChanged covers range paste / fill / API.
      table.on('cellEdited', () => this.schedule());
      table.on('dataChanged', () => { this.rowCount = table.getDataCount(); this.schedule(); });

      // Checkbox cells toggle on a single click (no editor box). setValue fires
      // cellEdited → flush → persist. editTriggerEvent:'dblclick' keeps OTHER cells
      // select-on-click / edit-on-dblclick.
      if (opts.editable !== false) {
        table.on('cellClick', (e, cell) => {
          if (colTypes[cell.getColumn().getField()] === 'checkbox') {
            cell.setValue(!truthy(cell.getValue()));
          }
        });
      }

      // Drag-reorder → tell the host the new order so it can persist position.
      // $dispatch bubbles a CustomEvent the consumer wires (x-on:grid-…-reordered).
      if (opts.movableRows) {
        table.on('rowMoved', () => {
          this.$dispatch('grid-rows-reordered', {
            order: table.getData().map((r) => r.id).filter((id) => id != null),
          });
        });
      }
      if (opts.movableColumns) {
        table.on('columnMoved', () => {
          this.$dispatch('grid-columns-reordered', {
            order: table.getColumns().map((c) => c.getField()).filter(Boolean),
          });
        });
      }
    },

    // Append a blank row locally (generic convenience for the built-in toolbar).
    // Livewire consumers usually disable the toolbar and add rows server-side.
    addRow() {
      table?.addRow({}).then(() => { this.rowCount = table.getDataCount(); this.schedule(); });
    },

    schedule() {
      // 'manual': the host drives persistence by calling flush() itself.
      if (syncMode === 'manual') return;

      if (syncMode === 'debounce') {
        clearTimeout(flushTimer);
        flushTimer = setTimeout(() => this.flush(), debounceMs);
        return;
      }

      // 'change': a single cell edit raises BOTH cellEdited and dataChanged, so
      // dedupe to one flush per tick (microtask) — while range paste / fill, which
      // raise only dataChanged, still flush.
      if (flushPending) return;
      flushPending = true;
      queueMicrotask(() => { flushPending = false; this.flush(); });
    },

    // Write the current row array to the hidden wire:model input and notify Livewire.
    flush() {
      const input = this.$refs.model;
      if (!input || !table) return;
      input.value = JSON.stringify(table.getData());
      input.dispatchEvent(new Event('input', { bubbles: true }));
    },

    get table() { return table; },

    destroy() {
      // Flush any buffered edit BEFORE teardown so a debounced change isn't lost
      // when the host re-seeds (a :key bump tears this component down). Mirrors
      // editor.js flushing on blur/destroy.
      if (syncMode !== 'manual') this.flush();
      clearTimeout(flushTimer);
      table?.destroy();
      table = null;
    },
  };
}

export default pinionDataGrid;
