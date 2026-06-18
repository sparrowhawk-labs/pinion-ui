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
// S2 scope (this file): single-range selection (anchor + active `sel`) via drag-rectangle,
// Shift-extend (click + arrows), whole row/column pick (gutter/header), TSV copy/paste
// (clipboard, clipped to bounds — no row/col growth), Cmd/Ctrl+D fill-down, Delete clear,
// Cmd/Ctrl+A select-all. Out: fill-handle DRAG, sort, resize, reorder = S3.

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
    sel: null,          // { r, c } the ACTIVE cell (moving corner of the range, outlined), or null
    anchor: null,       // { r, c } the FIXED corner; range = rect(anchor, sel). null when empty
    dragging: false,    // true while a rectangle drag (mousedown → mouseenter → mouseup) is in progress
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
      this.anchor = this.sel ? { ...this.sel } : null;
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

    // ── range geometry (range = bounding rect of anchor + active `sel`; single cell when equal) ──
    rLo() { return Math.min(this.anchor.r, this.sel.r); },
    rHi() { return Math.max(this.anchor.r, this.sel.r); },
    cLo() { return Math.min(this.anchor.c, this.sel.c); },
    cHi() { return Math.max(this.anchor.c, this.sel.c); },
    hasRange() { return !!(this.sel && this.anchor) && (this.rLo() !== this.rHi() || this.cLo() !== this.cHi()); },
    inRange(r, c) {
      if (!this.sel || !this.anchor) return false;
      return r >= this.rLo() && r <= this.rHi() && c >= this.cLo() && c <= this.cHi();
    },
    isRowActive(r) { return !!(this.sel && this.anchor) && r >= this.rLo() && r <= this.rHi(); },
    isColActive(c) { return !!(this.sel && this.anchor) && c >= this.cLo() && c <= this.cHi(); },
    collapse(r, c) { this.sel = { r, c }; this.anchor = { r, c }; },   // single-cell selection (anchor === active)

    // Selection rectangle = a 2px primary stroke on the cells lying on the range PERIMETER,
    // drawn as an inset box-shadow (no border-collapse layout shift; overlays the gridlines).
    // The stroke hugs each cell's OWN edge, so the right/bottom edges overlap the cell's own
    // gridline cleanly — but the top/left edges sit beside the NEIGHBOUR's gridline and would
    // double. So we also paint that just-outside gridline `transparent` (color only → still no
    // shift). Interior cells get nothing → one clean rectangle, single cell included. There is
    // deliberately NO per-cell "active cell" outline — the whole selection reads as one block.
    cellSelStyle(r, c) {
      if (!this.sel || !this.anchor) return '';
      const parts = [];
      // hide the gridline just OUTSIDE the range's top / left edge (the neighbour cell owns it)
      if (r === this.rLo() - 1 && c >= this.cLo() && c <= this.cHi()) parts.push('border-bottom-color: transparent');
      if (c === this.cLo() - 1 && r >= this.rLo() && r <= this.rHi()) parts.push('border-right-color: transparent');
      if (this.inRange(r, c)) {
        const e = [];
        if (r === this.rLo()) e.push('inset 0 2px 0 0 var(--color-primary)');
        if (r === this.rHi()) e.push('inset 0 -2px 0 0 var(--color-primary)');
        if (c === this.cLo()) e.push('inset 2px 0 0 0 var(--color-primary)');
        if (c === this.cHi()) e.push('inset -2px 0 0 0 var(--color-primary)');
        if (e.length) parts.push(`box-shadow: ${e.join(', ')}`);
      }
      return parts.join('; ');
    },
    // Connected row/column indicator (S2.1 review): NOT a line beside the border (it merged /
    // doubled). Instead the selected row-number / column-header LABEL turns primary — subtle,
    // distinct, theme-tracking, off the border edge. When the range touches the header/gutter,
    // also paint their divider transparent so the range's top/left stroke isn't doubled there.
    // A FAINT primary wash (10% — much lighter than the rejected /20) mixed INTO base-100 so
    // it stays opaque (the header is sticky, the gutter is frozen — both must hide content
    // scrolling under them), plus the label in primary (gutter also bold). Inline so it beats
    // the base bg/text classes. Theme-independent: the wash is visible even where primary ==
    // base-content (e.g. pinion), unlike colouring the label alone.
    gutterSelStyle(r) {
      if (!this.isRowActive(r)) return '';
      // Same subtle treatment as the header (wash + primary label, no heavy bold) so the two
      // halves of the connected pair read at matching weight (S2.1 round-2 polish).
      const parts = [
        'background-color: color-mix(in oklab, var(--color-primary) 10%, var(--color-base-100))',
        'color: var(--color-primary)',
      ];
      if (this.cLo() === 0) parts.push('border-right-color: transparent');
      return parts.join('; ');
    },
    headerSelStyle(c) {
      if (!this.isColActive(c)) return '';
      const parts = [
        'background-color: color-mix(in oklab, var(--color-primary) 10%, var(--color-base-100))',
        'color: var(--color-primary)',
      ];
      if (this.rLo() === 0) parts.push('border-bottom-color: transparent');
      return parts.join('; ');
    },

    // ── selection (mouse) ──
    // Selection STARTS on mousedown (so a drag can paint a rectangle); a plain click only
    // toggles a checkbox. Shift = extend the range from the existing anchor.
    startSelect(r, c, e) {
      if (this.editing && !this.isEd(r, c)) this.commitEdit();
      if (e && e.shiftKey && this.anchor) { this.sel = { r, c }; }   // extend: keep anchor fixed
      else { this.collapse(r, c); this.dragging = true; }           // new anchor; begin a drag
      this.focusGrid();
    },
    extendDrag(r, c) { if (this.dragging) this.sel = { r, c }; },     // anchor fixed → the rect grows
    endDrag() { this.dragging = false; },
    onCellClick(r, c, e) {
      // checkbox toggles on a PLAIN click of the already-selected cell (not Shift, not a drag end)
      if (e && e.shiftKey) return;
      if (this.colType(c) === 'checkbox' && this.editableCol(c) && this.isSel(r, c)) this.toggleCell(r, c);
    },

    // ── selection (header / gutter) — pick a whole column / row; Shift extends the block ──
    selectCol(c, e) {
      if (this.editing) this.commitEdit();
      const lastR = Math.max(this.rows.length - 1, 0);
      if (e && e.shiftKey && this.anchor) this.anchor = { r: 0, c: this.anchor.c };
      else this.anchor = { r: 0, c };
      this.sel = { r: lastR, c };
      this.focusGrid();
    },
    selectRow(r, e) {
      if (this.editing) this.commitEdit();
      const lastC = Math.max(this.cols.length - 1, 0);
      if (e && e.shiftKey && this.anchor) this.anchor = { r: this.anchor.r, c: 0 };
      else this.anchor = { r, c: 0 };
      this.sel = { r, c: lastC };
      this.focusGrid();
    },
    selectAll() {
      if (!this.rows.length || !this.cols.length) return;
      this.anchor = { r: 0, c: 0 };
      this.sel = { r: this.rows.length - 1, c: this.cols.length - 1 };
    },
    // Shift-extend to a cell, anchor fixed. Used by select cells, whose trigger button
    // swallows mousedown (dropdown guard) so startSelect's shift path can't see the click.
    extendTo(r, c) {
      if (this.anchor) this.sel = { r, c };
      else this.collapse(r, c);
      this.focusGrid();
    },

    moveSelection(dr, dc, extend = false) {
      if (this.editing) this.commitEdit();
      if (!this.sel) { this.collapse(0, 0); return; }
      const r = Math.min(Math.max(this.sel.r + dr, 0), this.rows.length - 1);
      const c = Math.min(Math.max(this.sel.c + dc, 0), this.cols.length - 1);
      this.sel = { r, c };
      if (!extend) this.anchor = { r, c };   // collapse the range unless Shift-extending
    },
    focusGrid() { this.$nextTick(() => this.$refs.grid?.focus({ preventScroll: true })); },

    // ── keyboard (bound on the focusable grid container) ──
    onKey(e) {
      if (this.editing) return;             // the inline editor handles its own keys
      if (e.isComposing || e.keyCode === 229) return;  // don't hijack nav/edit-start mid-IME-composition (CJK)
      if (!this.sel) { if (this.rows.length) this.collapse(0, 0); return; }
      const k = e.key;
      const { r, c } = this.sel;
      const mod = e.metaKey || e.ctrlKey;
      // ── range clipboard / fill / clear / select-all (S2) ──
      if (mod && (k === 'c' || k === 'C')) { e.preventDefault(); this.copyRange(); return; }
      if (mod && (k === 'v' || k === 'V')) { e.preventDefault(); this.pasteRange(); return; }
      if (mod && (k === 'd' || k === 'D')) { e.preventDefault(); this.fillDown(); return; }
      if (mod && (k === 'a' || k === 'A')) { e.preventDefault(); this.selectAll(); return; }
      if (!mod && (k === 'Delete' || k === 'Backspace')) { e.preventDefault(); this.clearRange(); return; }
      if (k === 'ArrowUp') { e.preventDefault(); this.moveSelection(-1, 0, e.shiftKey); }
      else if (k === 'ArrowDown') { e.preventDefault(); this.moveSelection(1, 0, e.shiftKey); }
      else if (k === 'ArrowLeft') { e.preventDefault(); this.moveSelection(0, -1, e.shiftKey); }
      else if (k === 'ArrowRight') { e.preventDefault(); this.moveSelection(0, 1, e.shiftKey); }
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
      this.collapse(r, c);
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

    // ── range operations (S2): clipboard (TSV), fill-down, clear. Single range only. ──
    // writeCell = setCell without the per-call flush, so a bulk op schedules ONE flush.
    writeCell(r, c, raw) {
      if (!this.editableCol(c) || !this.rows[r]) return;
      const key = this.colKey(c);
      const next = castValue(raw, this.colType(c));
      if (this.rows[r][key] !== next) this.rows[r][key] = next;
    },
    copyRange() {
      if (!this.sel) return;
      const lines = [];
      for (let r = this.rLo(); r <= this.rHi(); r++) {
        const cells = [];
        for (let c = this.cLo(); c <= this.cHi(); c++) {
          const v = this.rows[r]?.[this.colKey(c)];
          cells.push(v === null || v === undefined ? '' : String(v));
        }
        lines.push(cells.join('\t'));
      }
      navigator.clipboard?.writeText?.(lines.join('\n'));   // TSV: rows by \n, cells by \t
    },
    async pasteRange() {
      if (!this.sel || !navigator.clipboard?.readText) return;
      let text;
      try { text = await navigator.clipboard.readText(); } catch { return; }   // permission denied / no gesture
      if (!text) return;
      // a copied block ends in a newline; drop ONE trailing newline so paste adds no blank row.
      const matrix = text.replace(/\r\n/g, '\n').replace(/\n$/, '').split('\n').map((ln) => ln.split('\t'));
      const top = this.rLo(), left = this.cLo();
      if (matrix.length === 1 && matrix[0].length === 1) {
        // single value → fill the whole current range with it
        const val = matrix[0][0];
        for (let r = this.rLo(); r <= this.rHi(); r++)
          for (let c = this.cLo(); c <= this.cHi(); c++) this.writeCell(r, c, val);
      } else {
        // matrix → paste from the top-left, CLIPPED to existing bounds (row/col growth = host's job)
        let maxC = 0;
        for (let i = 0; i < matrix.length; i++) {
          const r = top + i;
          if (r >= this.rows.length) break;
          for (let j = 0; j < matrix[i].length; j++) {
            const c = left + j;
            if (c >= this.cols.length) break;
            this.writeCell(r, c, matrix[i][j]);
            if (j + 1 > maxC) maxC = j + 1;
          }
        }
        // grow the selection to cover what actually landed
        this.anchor = { r: top, c: left };
        this.sel = {
          r: Math.min(top + matrix.length - 1, this.rows.length - 1),
          c: Math.min(left + maxC - 1, this.cols.length - 1),
        };
      }
      this.schedule();
      this.focusGrid();
    },
    fillDown() {
      if (!this.sel || this.rHi() <= this.rLo()) return;   // need at least one row below the top
      let changed = false;
      for (let c = this.cLo(); c <= this.cHi(); c++) {
        if (!this.editableCol(c)) continue;
        const key = this.colKey(c);
        const src = this.rows[this.rLo()]?.[key];          // already a typed stored value — copy as-is
        for (let r = this.rLo() + 1; r <= this.rHi(); r++) {
          if (this.rows[r] && this.rows[r][key] !== src) { this.rows[r][key] = src; changed = true; }
        }
      }
      if (changed) this.schedule();
    },
    clearRange() {
      if (!this.sel) return;
      let changed = false;
      for (let r = this.rLo(); r <= this.rHi(); r++) {
        for (let c = this.cLo(); c <= this.cHi(); c++) {
          if (!this.editableCol(c) || !this.rows[r]) continue;
          const key = this.colKey(c);
          const blank = this.colType(c) === 'checkbox' ? false : null;
          if (this.rows[r][key] !== blank) { this.rows[r][key] = blank; changed = true; }
        }
      }
      if (changed) this.schedule();
    },

    // ── custom select-cell dropdown (pinion <x-select> look; no native <select>, so it
    //    survives Alpine re-renders, and is position:fixed so the grid overflow can't clip it) ──
    cellEl(r, c) { return this.$refs.grid?.querySelector(`[data-r="${r}"][data-c="${c}"]`); },
    isSelOpen(r, c) { return this.openSel && this.openSel.r === r && this.openSel.c === c; },
    toggleSelect(r, c) {
      if (!this.editableCol(c)) return;
      this.collapse(r, c);
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
      this.collapse(r, c);
      this.schedule();
    },

    // ── toolbar (STANDALONE convenience; Livewire hosts own structure + pass :toolbar=false) ──
    addRow() {
      const blank = {};
      this.cols.forEach((col) => { blank[col.key] = col.type === 'checkbox' ? false : null; });
      this.rows.push(blank);
      this.collapse(this.rows.length - 1, 0);
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
