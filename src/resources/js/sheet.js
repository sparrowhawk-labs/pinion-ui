// ─────────────────────────────────────────────────────────────────────────────
// pinion-ui · <x-sheet> behavior module  (Locality-of-Behavior spreadsheet)
// ─────────────────────────────────────────────────────────────────────────────
//
// The hand-written counterpart to <x-data-grid>/Tabulator: NO third-party engine.
// OPT-IN — a consumer imports it (or `php artisan ui:install --sheet` wires it) into
// resources/js/app.js. Non-sheet apps pay ZERO JS. The Blade library ships no bundled JS.
//
// Consumer wiring (what ui:install --sheet injects — note: NO npm dependency, pure Alpine):
//
//     import { pinionSheet } from
//       '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/sheet.js';
//     Alpine.data('pinionSheet', pinionSheet);
//
// ── Data contract (IDENTICAL to <x-data-grid> — a sheet is a drop-in) ─────────
// The wire:model value is a JSON STRING: an array of row objects, each keyed by
// column `key`, plus the `id` the host reconciles against its store:
//
//     [ { "id": 12, "item": "初期構築", "qty": 1, "category": "開発" }, … ]
//
// array IN via :rows (a PHP array) → JSON-string OUT via the hidden carrier. Number
// cells flush as JSON numbers (not strings) so the host's strict (!==) no-op guard
// behaves identically to data-grid's. Structural changes (add/remove ROWS/COLUMNS) for
// a Livewire host are the HOST's job (it persists, then re-seeds by bumping the
// Livewire :key); the built-in toolbar add-row/add-col are a STANDALONE convenience
// (Livewire hosts pass :toolbar="false"). See reference/components/sheet.md.
//
// ── Design note (differs from data-grid on purpose) ──────────────────────────
// data-grid keeps its rows in a CLOSURE because Tabulator (a third-party engine)
// mutates them and Alpine's proxy corrupts its bookkeeping. <x-sheet> has NO engine,
// so `rows`/`cols` ARE Alpine-reactive — the table is a declarative x-for in the Blade
// (Locality of Behavior: the render + edit rules are legible in the markup). We still
// DEEP-CLONE out of the `opts` proxy at init (opts arrives Proxy-wrapped from
// `x-data="pinionSheet({{ Js::from($config) }})"`), then let Alpine re-proxy our own
// copy. The grid host carries `wire:ignore` so Livewire's morphdom never reconciles
// Alpine's x-for DOM; a Livewire host re-seeds by bumping its :key (a key change
// replaces the element — wire:ignore only blocks morphing of a PERSISTED element).
//
// S1 scope: single-cell selection, keyboard nav (arrows/Tab/Enter/typing), per-type
// inline editing (text/number/date/select + checkbox toggle), flush round-trip.
// Range select / clipboard / fill = S2; sort / resize / reorder = S3.

const truthy = (v) => v === true || v === 1 || v === '1' || v === 'true';

/**
 * Alpine component factory for <x-sheet>.
 * @param {object}  opts
 * @param {Array}   [opts.columns]   generic column specs { key, title, type, options?, editable? }
 * @param {Array}   [opts.rows]      initial row objects (id-keyed)
 * @param {boolean} [opts.editable]  master edit switch (default true)
 * @param {string}  [opts.sync]      'change' | 'debounce:NNN' | 'manual'  (default 'debounce:400')
 */
export function pinionSheet(opts = {}) {
  let flushTimer = null;
  let flushPending = false;
  // The hidden wire:model carrier element, captured in init() (where `this.$refs` is
  // available). flush() uses THIS, not `this.$refs.model`, because flush can be reached
  // from an Alpine x-on EXPRESSION (the date cell's calendar-select → commitEdit), where
  // `this` is Alpine's scope proxy and `this.$refs` is undefined — a `this.$refs.model`
  // there silently no-ops the flush. A closure ref works from every call context.
  let modelEl = null;

  const sync = String(opts.sync ?? 'debounce:400');
  const syncMode = sync.split(':')[0]; // change | debounce | manual
  const debounceMs = sync.startsWith('debounce')
    ? (parseInt(sync.split(':')[1], 10) || 400)
    : 400;

  const castValue = (raw, type) => {
    if (type === 'checkbox') return truthy(raw);
    if (raw === '' || raw === null || raw === undefined) return null;
    if (type === 'number') { const n = Number(raw); return Number.isFinite(n) ? n : null; }
    return String(raw);
  };

  return {
    // ── reactive state (Alpine-proxied — see Design note) ──
    rows: [],
    cols: [],
    sel: null,          // { r, c } selected cell, or null when empty
    editing: null,      // { r, c } cell being edited, or null
    editValue: '',      // the in-flight editor value (bound via x-model)
    selectOnFocus: true,// on edit focus: select-all (Enter/dblclick) vs caret-at-end (typed)
    openSel: null,      // { r, c } of the OPEN select-cell dropdown, or null
    selPx: null, selPy: null, selW: null,   // fixed-anchor for the open select dropdown

    init() {
      // Deep-clone out of the opts proxy, then let Alpine re-proxy our own copy.
      this.cols = JSON.parse(JSON.stringify(opts.columns ?? []));
      this.rows = JSON.parse(JSON.stringify(opts.rows ?? []));
      this.sel = (this.rows.length && this.cols.length) ? { r: 0, c: 0 } : null;
      modelEl = this.$refs.model;   // capture once — see the closure note above
      this.flush();   // unconditional seed flush (mirrors data-grid: populates the carrier)
    },

    get rowCount() { return this.rows.length; },

    // ── display helpers (used by x-text / x-bind in the Blade) ──
    truthy,
    fmt(v) { return (v === null || v === undefined) ? '' : String(v); },
    colKey(c) { return this.cols[c]?.key; },
    colType(c) { return this.cols[c]?.type ?? 'text'; },
    editableCol(c) { return opts.editable !== false && this.cols[c]?.editable !== false; },
    isSel(r, c) { return this.sel && this.sel.r === r && this.sel.c === c; },
    isEd(r, c) { return this.editing && this.editing.r === r && this.editing.c === c; },

    // ── selection ──
    selectCell(r, c) {
      if (this.editing && !this.isEd(r, c)) this.commitEdit();
      this.sel = { r, c };
      if (this.colType(c) === 'checkbox' && this.editableCol(c)) this.toggleCell(r, c);
      this.focusGrid();
    },
    moveSelection(dr, dc) {
      if (this.editing) this.commitEdit();
      if (!this.sel) { this.sel = { r: 0, c: 0 }; return; }
      this.sel = {
        r: Math.min(Math.max(this.sel.r + dr, 0), this.rows.length - 1),
        c: Math.min(Math.max(this.sel.c + dc, 0), this.cols.length - 1),
      };
    },
    focusGrid() { this.$nextTick(() => this.$refs.grid?.focus({ preventScroll: true })); },

    // ── keyboard (bound on the focusable grid container) ──
    onKey(e) {
      if (this.editing) return;             // the inline editor handles its own keys
      if (e.isComposing || e.keyCode === 229) return;  // don't hijack nav/edit-start mid-IME-composition (CJK)
      if (!this.sel) { if (this.rows.length) this.sel = { r: 0, c: 0 }; return; }
      const k = e.key;
      const { r, c } = this.sel;
      if (k === 'ArrowUp') { e.preventDefault(); this.moveSelection(-1, 0); }
      else if (k === 'ArrowDown') { e.preventDefault(); this.moveSelection(1, 0); }
      else if (k === 'ArrowLeft') { e.preventDefault(); this.moveSelection(0, -1); }
      else if (k === 'ArrowRight') { e.preventDefault(); this.moveSelection(0, 1); }
      else if (k === 'Tab') { e.preventDefault(); this.moveSelection(0, e.shiftKey ? -1 : 1); }
      else if (k === 'Enter') {
        e.preventDefault();
        if (this.colType(c) === 'checkbox') { if (this.editableCol(c)) this.toggleCell(r, c); }
        else this.beginEdit(r, c);
      }
      else if (k === ' ' && this.colType(c) === 'checkbox') { e.preventDefault(); if (this.editableCol(c)) this.toggleCell(r, c); }
      else if (k.length === 1 && !e.metaKey && !e.ctrlKey && !e.altKey && this.editableCol(c)) {
        // Type-to-edit only where typing a char makes sense: text always, number for
        // numeric chars. date/select/checkbox are picked (Enter/dblclick/Space), not typed.
        const t = this.colType(c);
        if (t === 'text' || (t === 'number' && /[-0-9.]/.test(k))) this.beginEdit(r, c, k);
      }
    },

    // ── editing ──
    beginEdit(r, c, initial = null) {
      if (!this.editableCol(c)) return;
      if (this.colType(c) === 'checkbox') return;   // checkboxes toggle on click/Space/Enter, never open an editor
      if (this.colType(c) === 'select') return;     // select is ALWAYS a live <select> — no text-edit mode
      if (this.editing) this.commitEdit();
      this.sel = { r, c };
      const cur = this.rows[r]?.[this.colKey(c)];
      this.editValue = initial !== null ? initial : (cur ?? '');
      this.selectOnFocus = (initial === null);   // Enter/dblclick → select-all; typed char → caret at end
      this.editing = { r, c };
      // focus is handled by the input's x-init in the Blade ($nextTick → focus/select).
    },
    commitEdit() {
      if (!this.editing) return;
      const { r, c } = this.editing;
      const key = this.colKey(c);
      const next = castValue(this.editValue, this.colType(c));
      this.editing = null;
      if (this.rows[r] && this.rows[r][key] !== next) { this.rows[r][key] = next; this.schedule(); }
      this.focusGrid();
    },
    cancelEdit() { this.editing = null; this.focusGrid(); },
    editorKey(e) {
      const k = e.key;
      if (k === 'Enter') { e.preventDefault(); this.commitEdit(); this.moveSelection(1, 0); }
      else if (k === 'Escape') { e.preventDefault(); this.cancelEdit(); }
      else if (k === 'Tab') { e.preventDefault(); this.commitEdit(); this.moveSelection(0, e.shiftKey ? -1 : 1); }
    },
    toggleCell(r, c) {
      if (!this.editableCol(c)) return;
      const key = this.colKey(c);
      if (!this.rows[r]) return;
      this.rows[r][key] = !truthy(this.rows[r][key]);
      this.schedule();
    },
    // Set a cell directly (used by the custom select dropdown). Cast per type.
    setCell(r, c, value) {
      if (!this.editableCol(c) || !this.rows[r]) return;
      const key = this.colKey(c);
      const next = castValue(value, this.colType(c));
      if (this.rows[r][key] !== next) { this.rows[r][key] = next; this.schedule(); }
    },

    // ── custom select-cell dropdown (pinion <x-select> look; no native <select>, so it
    //    survives Alpine re-renders, and is position:fixed so the grid overflow can't clip it) ──
    cellEl(r, c) { return this.$refs.grid?.querySelector(`[data-r="${r}"][data-c="${c}"]`); },
    isSelOpen(r, c) { return this.openSel && this.openSel.r === r && this.openSel.c === c; },
    toggleSelect(r, c) {
      if (!this.editableCol(c)) return;
      this.sel = { r, c };
      if (this.isSelOpen(r, c)) { this.openSel = null; return; }
      this.openSel = { r, c };
      this.$nextTick(() => {
        const td = this.cellEl(r, c);
        if (!td) return;
        const rect = td.getBoundingClientRect();
        const H = 240, M = 8;
        this.selW = Math.round(rect.width);
        let left = rect.left, top = rect.bottom + 2;
        if (top + H > window.innerHeight - M) top = Math.max(M, rect.top - H - 2);
        this.selPx = Math.round(left);
        this.selPy = Math.round(top);
      });
    },
    chooseOption(r, c, value) { this.setCell(r, c, value); this.openSel = null; this.focusGrid(); },
    step(r, c, delta) {
      if (!this.editableCol(c) || this.colType(c) !== 'number' || !this.rows[r]) return;
      const key = this.colKey(c);
      const cur = Number(this.rows[r][key]);
      // round to 6dp so repeated steps on a decimal don't accumulate IEEE float noise.
      this.rows[r][key] = Math.round(((Number.isFinite(cur) ? cur : 0) + delta) * 1e6) / 1e6;
      this.sel = { r, c };
      this.schedule();
    },

    // ── toolbar (STANDALONE convenience; Livewire hosts own structure + pass :toolbar=false) ──
    addRow() {
      const blank = {};
      this.cols.forEach((col) => { blank[col.key] = col.type === 'checkbox' ? false : null; });
      this.rows.push(blank);
      this.sel = { r: this.rows.length - 1, c: 0 };
      this.schedule();
    },
    addColumn() {
      const keys = this.cols.map((c) => c.key);
      let n = this.cols.length + 1;
      let key = 'col_' + n;
      while (keys.includes(key)) { n++; key = 'col_' + n; }
      this.cols.push({ key, title: '列 ' + n, type: 'text' });
      this.rows.forEach((row) => { row[key] = null; });
      this.schedule();
    },

    // ── carrier flush (IDENTICAL contract to data-grid) ──
    schedule() {
      if (syncMode === 'manual') return;
      if (syncMode === 'debounce') {
        clearTimeout(flushTimer);
        flushTimer = setTimeout(() => this.flush(), debounceMs);
        return;
      }
      if (flushPending) return;   // 'change': dedupe to one flush per microtask
      flushPending = true;
      queueMicrotask(() => { flushPending = false; this.flush(); });
    },
    flush() {
      const input = modelEl || this.$refs?.model;
      if (!input) return;
      input.value = JSON.stringify(this.rows);
      input.dispatchEvent(new Event('input', { bubbles: true }));
    },
    destroy() {
      if (syncMode !== 'manual') this.flush();   // don't lose a debounced edit on :key re-seed
      clearTimeout(flushTimer);
    },
  };
}

export default pinionSheet;
