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
// Scope (all shipped):
// S1  single-cell selection, keyboard nav (arrows/Tab/Enter/typing), per-type inline editing
//     (text/number/date/select + checkbox toggle), flush round-trip.
// S2  single-range selection (anchor + active `sel`) via drag-rectangle, Shift-extend, whole
//     row/column pick, TSV copy/paste (clipped to bounds), Cmd/Ctrl+D fill-down, Delete clear,
//     Cmd/Ctrl+A select-all.
// S3a sort (header caret asc⇄desc + ↺ restore).  S3b row/column reorder (HTML5 drag, same
//     events as <x-data-grid>).  S3c fill-handle drag (tile fill).  S3d column resize (lazy
//     freeze → table-fixed).  S3e right-click context menu + column type conversion.
// Cross-cutting: undo (Cmd/Ctrl+Z) — a snapshot stack fed from schedule(), the single mutation
// chokepoint (resize pushes its own snapshot at drag start).

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
  let colGhost = null;   // transient whole-column drag-image element (built on colDragStart, removed on dragend)
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
    sort: null,         // { c, dir:'asc'|'desc' } active sort, or null (S3)
    sortSnapshot: null, // [id,…] original row order, captured on first sort (for the 'clear' restore)
    dragRow: null, dropRow: null,   // row reorder: dragged index / insertion index (S3)
    dragCol: null, dropCol: null,   // column reorder: dragged index / insertion index (S3)
    fillSrc: null,      // { r0,r1,c0,c1 } source rect captured at fill-handle drag start (S3c)
    fill: null,         // { r, c } current fill corner while dragging the handle, or null (S3c)
    filling: false,     // true while a fill-handle drag is in progress (S3c)
    history: [],        // undo stack of PRE-mutation snapshots (Cmd/Ctrl+Z pops one); capped
    lastState: null,    // snapshot of the current committed state; the next mutation pushes THIS
    resizing: null,     // { c, startX, startW, snap } during a column-resize drag (S3d), or null
    widthsFrozen: false,// once true, the table is table-fixed with explicit per-column widths (S3d)
    gutterWidth: null,  // frozen row-number gutter width in px (S3d)
    menu: null,         // { x, y, r, c } open right-click context menu (S3e), or null

    init() {
      // Deep-clone out of the opts proxy, then let Alpine re-proxy our own copy.
      this.cols = JSON.parse(JSON.stringify(opts.columns ?? []));
      this.rows = JSON.parse(JSON.stringify(opts.rows ?? []));
      this.sel = (this.rows.length && this.cols.length) ? { r: 0, c: 0 } : null;
      this.anchor = this.sel ? { ...this.sel } : null;
      modelEl = this.$refs.model;   // capture once — see the closure note above
      this.flush();   // unconditional seed flush (mirrors data-grid: populates the carrier)
      this.lastState = this.snapshot();   // undo baseline — the first mutation pushes this
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
      if (e && e.button === 2) return;   // right-click is the context menu's (handled by contextmenu)
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
      if (mod && !e.shiftKey && (k === 'z' || k === 'Z')) { e.preventDefault(); this.undo(); return; }
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

    // ── fill-handle drag (S3c): the small square at the range's BOTTOM-RIGHT. Dragging it
    //    down/right (the only directions the corner affords) extends a preview rectangle along
    //    the DOMINANT axis (whichever delta is larger — never diagonal at once), then TILES the
    //    source block's values into the new cells (generalises Cmd/Ctrl+D fill-down; copy only,
    //    NO numeric series extrapolation). On release the selection grows to cover the result. ──
    isFillCorner(r, c) { return !!(this.sel && this.anchor) && !this.editing && r === this.rHi() && c === this.cHi(); },
    fillStart() {
      if (!this.sel || !this.anchor) return;
      this.fillSrc = { r0: this.rLo(), r1: this.rHi(), c0: this.cLo(), c1: this.cHi() };
      this.fill = { r: this.fillSrc.r1, c: this.fillSrc.c1 };
      this.filling = true;
    },
    fillMoveTo(r, c) {   // clamp to extend down/right only (the handle sits at the bottom-right)
      if (!this.filling) return;
      this.fill = { r: Math.max(r, this.fillSrc.r1), c: Math.max(c, this.fillSrc.c1) };
    },
    // The preview/target rectangle along the dominant axis, or null when there's no extension yet.
    fillRect() {
      if (!this.filling || !this.fillSrc || !this.fill) return null;
      const s = this.fillSrc;
      const dDown = this.fill.r - s.r1, dRight = this.fill.c - s.c1;
      if (dDown <= 0 && dRight <= 0) return null;
      return dDown >= dRight
        ? { r0: s.r0, r1: s.r1 + dDown, c0: s.c0, c1: s.c1 }      // vertical fill
        : { r0: s.r0, r1: s.r1, c0: s.c0, c1: s.c1 + dRight };    // horizontal fill
    },
    inFillPreview(r, c) {   // cells the drag WILL fill (inside the rect, outside the source)
      const f = this.fillRect(); if (!f) return false;
      const s = this.fillSrc;
      const inRect = r >= f.r0 && r <= f.r1 && c >= f.c0 && c <= f.c1;
      const inSrc = r >= s.r0 && r <= s.r1 && c >= s.c0 && c <= s.c1;
      return inRect && !inSrc;
    },
    fillEnd() {
      if (!this.filling) return;
      const f = this.fillRect(), s = this.fillSrc;
      this.filling = false; this.fill = null; this.fillSrc = null;
      if (!f) return;
      const sH = s.r1 - s.r0 + 1, sW = s.c1 - s.c0 + 1;   // source block size (for the tiling modulo)
      let changed = false;
      for (let r = f.r0; r <= f.r1; r++) {
        for (let c = f.c0; c <= f.c1; c++) {
          if (r >= s.r0 && r <= s.r1 && c >= s.c0 && c <= s.c1) continue;   // leave the source intact
          if (!this.editableCol(c) || !this.rows[r]) continue;
          const srcVal = this.rows[s.r0 + ((r - s.r0) % sH)]?.[this.colKey(s.c0 + ((c - s.c0) % sW))];
          const key = this.colKey(c), next = castValue(srcVal, this.colType(c));
          if (this.rows[r][key] !== next) { this.rows[r][key] = next; changed = true; }
        }
      }
      this.anchor = { r: f.r0, c: f.c0 };   // grow the selection to cover source ∪ filled
      this.sel = { r: f.r1, c: f.c1 };
      if (changed) this.schedule();
      this.focusGrid();
    },
    // Cell mouseenter dispatcher: a fill-handle drag fills; a plain drag paints a selection.
    onCellEnter(r, c) { if (this.filling) this.fillMoveTo(r, c); else this.extendDrag(r, c); },

    // ── sort (S3): DESTRUCTIVE — reorders `rows` (spreadsheet convention), flushes the new
    //    order. caret cycles asc → desc → clear; clear restores the order captured on the
    //    first sort. Header BODY click stays column-select (S2); the caret is x-on:click.stop. ──
    colSortable(c) { return opts.sortable !== false && this.cols[c]?.sortable !== false; },
    sortDir(c) { return this.sort && this.sort.c === c ? this.sort.dir : null; },
    snapshotOrder() { this.sortSnapshot = this.rows.map((row, i) => row.id ?? i); },
    restoreOrder() {
      if (!this.sortSnapshot) return;
      const pos = new Map(this.sortSnapshot.map((id, i) => [id, i]));
      const at = (row, i) => (pos.has(row.id ?? i) ? pos.get(row.id ?? i) : Infinity);
      this.rows = this.rows.slice().sort((a, b) => at(a) - at(b));   // reassign → Alpine re-renders
      this.sortSnapshot = null;
    },
    applySort() {
      const { c, dir } = this.sort;
      const key = this.colKey(c), type = this.colType(c), sign = dir === 'asc' ? 1 : -1;
      const empty = (v) => v === null || v === undefined || v === '';
      this.rows = this.rows.slice().sort((ra, rb) => {
        const a = ra[key], b = rb[key];
        if (empty(a) && empty(b)) return 0;
        if (empty(a)) return 1;        // empties last, regardless of direction
        if (empty(b)) return -1;
        let cmp;
        if (type === 'number') cmp = Number(a) - Number(b);
        else if (type === 'checkbox') cmp = (truthy(a) ? 1 : 0) - (truthy(b) ? 1 : 0);
        else if (type === 'date') cmp = String(a) < String(b) ? -1 : String(a) > String(b) ? 1 : 0;
        else cmp = String(a).localeCompare(String(b), 'ja');
        return cmp * sign;
      });
    },
    toggleSort(c) {
      if (!this.colSortable(c)) return;
      if (this.editing) this.commitEdit();
      const dir = this.sortDir(c) === 'asc' ? 'desc' : 'asc';   // null→asc, asc→desc, desc→asc (2-state)
      if (!this.sort) this.snapshotOrder();   // remember the pre-sort order for the reset (↺) button
      this.sort = { c, dir };
      this.applySort();
      this.collapse(0, c);   // row order changed → put the cursor at the sorted column's top
      this.schedule();
    },
    clearSort() {   // the "↺ 元に戻す" button shown next to the caret while a column is sorted
      if (!this.sort) return;
      const c = this.sort.c;
      this.sort = null;
      this.restoreOrder();
      this.collapse(0, c);
      this.schedule();
    },

    // ── reorder (S3): native HTML5 drag-and-drop (auto-separates a click=select from a drag),
    //    gated by movableRows / movableColumns in the Blade. Drop position is decided by which
    //    half of the target the cursor is over; on drop we reorder the array, fire the SAME
    //    event as <x-data-grid> (rows → id list, columns → key list), and clear any active sort
    //    (a manual order supersedes it). The drop line + source dimming are CSS hooks. ──
    rowDragStart(r, e) {
      if (this.editing) this.commitEdit();
      this.sel = null; this.anchor = null;        // reorganising, not selecting
      this.dragRow = r;
      if (e?.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(r));
        const tr = e.currentTarget.closest('tr');     // drag image = the WHOLE row, not the gutter cell
        if (tr) e.dataTransfer.setDragImage(tr, 24, tr.offsetHeight / 2);
      }
    },
    // Insertion index from cursor Y vs row midpoints. Cursor above all → 0, below all → N, so
    // OVERSHOOT past the table top/bottom clamps to first/last. Queries via $refs.grid (not
    // currentTarget) because the listener is window-level — a drop anywhere on the page lands.
    rowDragOverAt(e) {
      if (this.dragRow === null) return;
      const trs = Array.from(this.$refs.grid.querySelectorAll('tbody tr')).filter((el) => el.offsetHeight > 0);
      let idx = trs.length;
      for (let i = 0; i < trs.length; i++) {
        const rect = trs[i].getBoundingClientRect();
        if (e.clientY < rect.top + rect.height / 2) { idx = i; break; }
      }
      this.dropRow = idx;
    },
    rowDrop() {
      if (this.dragRow !== null && this.dropRow !== null) {
        let to = this.dropRow; const from = this.dragRow;
        if (to !== from && to !== from + 1) {     // a real move
          const next = this.rows.slice();
          const [moved] = next.splice(from, 1);
          if (from < to) to -= 1;                 // removal shifts the target
          next.splice(to, 0, moved);
          this.rows = next;
          this.sort = null; this.sortSnapshot = null;   // manual order supersedes sort
          this.emitRowOrder();
          this.schedule();
        }
      }
      this.clearRowDrag();
    },
    clearRowDrag() { this.dragRow = null; this.dropRow = null; },
    emitRowOrder() { this.$dispatch('grid-rows-reordered', { order: this.rows.map((r) => r.id).filter((id) => id != null) }); },

    colDragStart(c, e) {
      if (this.editing) this.commitEdit();
      this.sel = null; this.anchor = null;
      this.dragCol = c;
      if (e?.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(c));
        const ghost = this.buildColGhost(c);          // drag image = the WHOLE column
        if (ghost) e.dataTransfer.setDragImage(ghost, 24, 16);
      }
    },
    colDragOverAt(e) {   // cursor X vs header midpoints; overshoot left/right clamps to first/last
      if (this.dragCol === null) return;
      const ths = Array.from(this.$refs.grid.querySelectorAll('thead [role="columnheader"]'));
      let idx = ths.length;
      for (let i = 0; i < ths.length; i++) {
        const rect = ths[i].getBoundingClientRect();
        if (e.clientX < rect.left + rect.width / 2) { idx = i; break; }
      }
      this.dropCol = idx;
    },
    // Window-level during a drag (added in the Blade) so a drop ANYWHERE on the page lands —
    // overshooting far past the table edge still drops at first/last (the rowDragOverAt /
    // colDragOverAt computation clamps). Self-guarded by dragRow/dragCol → multi-sheet safe,
    // and preventDefault only while WE are dragging so other page drags are unaffected.
    onWinDragOver(e) {
      if (this.dragRow !== null) { e.preventDefault(); this.rowDragOverAt(e); }
      else if (this.dragCol !== null) { e.preventDefault(); this.colDragOverAt(e); }
    },
    onWinDrop(e) {
      if (this.dragRow !== null) { e.preventDefault(); this.rowDrop(); }
      else if (this.dragCol !== null) { e.preventDefault(); this.colDrop(); }
    },
    // Build a styled WHOLE-column clone as the drag image (native default = just the grabbed header
    // cell). Appended off-screen, removed in clearColDrag. Defensive: returns null on failure.
    buildColGhost(c) {
      const grid = this.$refs.grid;
      const head = grid?.querySelectorAll('thead [role="columnheader"]')[c];
      if (!head) return null;
      const cells = [head, ...grid.querySelectorAll(`[data-c="${c}"]`)];
      const wrap = document.createElement('div');
      wrap.className = 'pn-sheet';
      wrap.style.cssText = 'position:fixed; top:-9999px; left:-9999px; width:max-content; background:var(--color-base-100); box-shadow:0 6px 16px rgba(0,0,0,.18); opacity:.95;';
      const table = document.createElement('table');
      table.className = 'pn-sheet-table';
      const tb = document.createElement('tbody');
      cells.forEach((cell) => {
        const tr = document.createElement('tr');
        const td = cell.cloneNode(true);
        td.removeAttribute('style');   // drop selection / transparent-gridline inline styles
        tr.appendChild(td);
        tb.appendChild(tr);
      });
      table.appendChild(tb);
      wrap.appendChild(table);
      document.body.appendChild(wrap);
      colGhost = wrap;
      return wrap;
    },
    colDrop() {
      if (this.dragCol !== null && this.dropCol !== null) {
        let to = this.dropCol; const from = this.dragCol;
        if (to !== from && to !== from + 1) {
          const next = this.cols.slice();
          const [moved] = next.splice(from, 1);
          if (from < to) to -= 1;
          next.splice(to, 0, moved);
          this.cols = next;
          this.sort = null; this.sortSnapshot = null;
          this.emitColOrder();
          this.schedule();
        }
      }
      this.clearColDrag();
    },
    clearColDrag() {
      this.dragCol = null; this.dropCol = null;
      if (colGhost) { colGhost.remove(); colGhost = null; }
    },
    emitColOrder() { this.$dispatch('grid-columns-reordered', { order: this.cols.map((c) => c.key).filter(Boolean) }); },

    // ── column resize (S3d): drag a header's right edge. The table is content-auto by default;
    //    the FIRST resize lazily FREEZES the current rendered widths into cols[c].width and flips
    //    the table to table-fixed (width:auto), making per-column widths authoritative (widening a
    //    column past the container then scrolls horizontally). Width is presentation, not row data
    //    — it rides on cols (never flushed to the carrier), is undoable, and fires
    //    grid-column-resized on release. mousemove/mouseup are window-level (drag can leave the th). ──
    freezeWidths() {
      const grid = this.$refs.grid;
      // Semantic selectors — NOT `thead tr > *`, whose count includes Alpine's inert x-for
      // <template> node and the gutter, throwing the index off. role=columnheader = the rendered
      // column th's only; the corner gutter is the sole thead .pn-sheet-gutter.
      const headers = Array.from(grid.querySelectorAll('thead [role="columnheader"]'));
      if (headers.length !== this.cols.length) return;   // not painted yet → stay auto, bail
      headers.forEach((th, c) => { this.cols[c].width = Math.round(th.getBoundingClientRect().width); });
      const corner = grid.querySelector('thead .pn-sheet-gutter');
      if (corner) this.gutterWidth = Math.round(corner.getBoundingClientRect().width);
      this.widthsFrozen = true;
    },
    // Explicit total width once frozen (gutter + every column). Forcing the table to this px width
    // (overriding the w-full class) makes column widths authoritative: widening a column grows the
    // total → the overflow-auto grid scrolls horizontally, instead of table-fixed+width:100%
    // re-distributing the drag across the other columns.
    get frozenTableWidth() {
      if (!this.widthsFrozen) return null;
      return this.cols.reduce((a, col) => a + (col.width || 0), 0) + (this.gutterWidth || 0);
    },
    resizeStart(c, e) {
      if (this.editing) this.commitEdit();
      const snap = this.snapshot();              // pre-resize (and pre-freeze) state, for undo
      if (!this.widthsFrozen) this.freezeWidths();
      this.resizing = { c, startX: e.clientX, startW: this.cols[c].width, snap };
    },
    resizeMove(e) {
      if (!this.resizing) return;
      const { c, startX, startW } = this.resizing;
      this.cols[c].width = Math.max(120, startW + (e.clientX - startX));   // 120px floor — fits a header's
      // controls + a number cell's −/＋ steppers without overflow (review RP2).
    },
    resizeEnd() {
      if (!this.resizing) return;
      const { c, startW, snap } = this.resizing;
      this.resizing = null;
      if (JSON.stringify(snap.cols) === JSON.stringify(this.cols)) return;   // no freeze, no width change
      this.pushHistory(snap);              // make the resize (and first-time freeze) undoable
      this.lastState = this.snapshot();
      if (startW !== this.cols[c].width) this.$dispatch('grid-column-resized', { key: this.colKey(c), width: this.cols[c].width });
    },

    // ── right-click context menu (S3e): a sheet-specific menu (browser default suppressed). Acts
    //    on the current selection / the right-clicked cell+column. Column TYPE conversion (per
    //    column) coerces every cell via castValue; →select seeds options from the distinct existing
    //    values. Structural ops (insert/delete row·column) are the same standalone-convenience as
    //    the toolbar add buttons (Livewire hosts own structure and pass :context-menu="false").
    //    Every action routes through schedule() so it is flushed + undoable. ──
    colTypeOptions: [
      { value: 'text', label: 'テキスト' },
      { value: 'number', label: '数値' },
      { value: 'date', label: '日付' },
      { value: 'select', label: '選択肢' },
      { value: 'checkbox', label: 'チェック' },
    ],
    menuAt(e, r, c) {
      const W = 220, H = 360;   // clamp so the menu stays in the viewport
      this.openSel = null;      // close any open select dropdown
      this.menu = {
        x: Math.max(8, Math.min(e.clientX, window.innerWidth - W - 8)),
        y: Math.max(8, Math.min(e.clientY, window.innerHeight - H - 8)),
        r, c,
      };
    },
    openCellMenu(r, c, e) {
      if (this.editing) this.commitEdit();
      if (!this.inRange(r, c)) this.collapse(r, c);   // right-click outside the range re-selects
      this.menuAt(e, r, c);
    },
    openHeaderMenu(c, e) {
      if (this.editing) this.commitEdit();
      this.collapse(0, c);   // target column c (single cell at its top — keeps row ops safe)
      this.menuAt(e, 0, c);
    },
    closeMenu() { this.menu = null; },

    convertColumn(c, type) {
      this.closeMenu();
      const col = this.cols[c];
      if (!col || col.type === type) return;
      if (type === 'select') {
        const seen = new Set();
        this.rows.forEach((row) => { const v = row[col.key]; if (v !== null && v !== undefined && v !== '') seen.add(String(v)); });
        col.options = Array.from(seen);   // seed choices from the distinct existing values
      }
      col.type = type;
      this.rows.forEach((row) => { row[col.key] = castValue(row[col.key], type); });   // coerce cells
      this.schedule();
    },
    insertRow(at) {
      const blank = {};
      this.cols.forEach((col) => { blank[col.key] = col.type === 'checkbox' ? false : null; });
      this.rows.splice(Math.max(0, Math.min(at, this.rows.length)), 0, blank);
      this.collapse(Math.max(0, Math.min(at, this.rows.length - 1)), this.sel?.c ?? 0);
      this.closeMenu(); this.schedule();
    },
    deleteRows() {
      if (!this.sel) { this.closeMenu(); return; }
      const lo = this.rLo(), hi = this.rHi();
      this.rows.splice(lo, hi - lo + 1);
      if (!this.rows.length) { this.sel = null; this.anchor = null; }
      else this.collapse(Math.min(lo, this.rows.length - 1), this.sel.c);
      this.closeMenu(); this.schedule();
    },
    insertColumn(at) {
      const keys = this.cols.map((c) => c.key);
      let n = this.cols.length + 1, key = 'col_' + n;
      while (keys.includes(key)) { n++; key = 'col_' + n; }
      const col = { key, title: '列 ' + n, type: 'text' };
      if (this.widthsFrozen) col.width = 120;   // keep table-fixed consistent (else the new col gets 0)
      this.cols.splice(Math.max(0, Math.min(at, this.cols.length)), 0, col);
      this.rows.forEach((row) => { row[key] = null; });
      this.collapse(this.sel?.r ?? 0, Math.max(0, Math.min(at, this.cols.length - 1)));
      this.closeMenu(); this.schedule();
    },
    deleteColumns() {
      if (!this.sel) { this.closeMenu(); return; }
      const lo = this.cLo(), hi = this.cHi();
      const removed = this.cols.slice(lo, hi + 1).map((c) => c.key);
      this.cols.splice(lo, hi - lo + 1);
      this.rows.forEach((row) => removed.forEach((k) => { delete row[k]; }));
      if (!this.cols.length) { this.sel = null; this.anchor = null; }
      else this.collapse(this.sel.r, Math.min(lo, this.cols.length - 1));
      this.closeMenu(); this.schedule();
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

    // ── undo (Cmd/Ctrl+Z): a stack of PRE-mutation snapshots. schedule() is the single mutation
    //    chokepoint (every state change calls it once, guarded by a real-change check in its
    //    caller), so recording history THERE covers edit / toggle / fill / paste / clear / step /
    //    sort / reorder / add-row|col without touching each method. Captures rows+cols+sort
    //    (presentation-only widths ride along on cols). Resize history is pushed at drag start. ──
    snapshot() {
      return {
        rows: JSON.parse(JSON.stringify(this.rows)),
        cols: JSON.parse(JSON.stringify(this.cols)),
        sort: this.sort ? { ...this.sort } : null,
        sortSnapshot: this.sortSnapshot ? this.sortSnapshot.slice() : null,
        widthsFrozen: this.widthsFrozen,   // S3d: so undoing a resize also reverts the freeze /
        gutterWidth: this.gutterWidth,     //      width-mode (else table-fixed lingers, all-equal)
      };
    },
    pushHistory(snap) {
      if (!snap) return;
      this.history.push(snap);
      if (this.history.length > 100) this.history.shift();   // cap the stack
    },
    applyState(s) {
      this.rows = JSON.parse(JSON.stringify(s.rows));
      this.cols = JSON.parse(JSON.stringify(s.cols));
      this.sort = s.sort ? { ...s.sort } : null;
      this.sortSnapshot = s.sortSnapshot ? s.sortSnapshot.slice() : null;
      this.widthsFrozen = s.widthsFrozen ?? false;
      this.gutterWidth = s.gutterWidth ?? null;
    },
    clampSelection() {
      if (!this.rows.length || !this.cols.length) { this.sel = null; this.anchor = null; return; }
      const fix = (p) => p ? { r: Math.min(Math.max(p.r, 0), this.rows.length - 1), c: Math.min(Math.max(p.c, 0), this.cols.length - 1) } : p;
      this.sel = fix(this.sel); this.anchor = fix(this.anchor);
    },
    undo() {
      if (!this.history.length) return;
      this.editing = null; this.openSel = null;   // drop any transient editor/dropdown
      this.applyState(this.history.pop());         // restore the state before the last mutation
      this.lastState = this.snapshot();            // this restored state is the new baseline
      this.clampSelection();
      if (syncMode !== 'manual') this.flush();     // push the restored rows to the carrier now
      this.focusGrid();
    },

    // ── carrier flush (IDENTICAL contract to data-grid) ──
    schedule() {
      this.pushHistory(this.lastState);   // record the pre-mutation state, then refresh the baseline
      this.lastState = this.snapshot();
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
