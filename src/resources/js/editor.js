// ─────────────────────────────────────────────────────────────────────────────
// pinion-ui · <x-editor> behavior module  (Tiptap / headless ProseMirror)
// ─────────────────────────────────────────────────────────────────────────────
//
// This is pinion-ui's FIRST JS-behavior component. It is OPT-IN: a consumer
// imports it (or lets `php artisan ui:install --editor` wire it) into their
// resources/js/app.js. Non-editor apps never import it and pay ZERO JS bundle
// cost. The Blade library itself ships no bundled JS.
//
// Consumer wiring (what ui:install --editor injects):
//
//     import Alpine from 'alpinejs';
//     import { pinionEditor } from
//       '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/editor.js';
//     Alpine.data('pinionEditor', pinionEditor);
//
// Requires these npm deps in the consumer (added by ui:install --editor):
//   @tiptap/core  @tiptap/starter-kit  @tiptap/extension-placeholder
//   @tiptap/extension-task-list  @tiptap/extension-task-item  @tiptap/extension-link
// (task-list / task-item are NOT in StarterKit. StarterKit v3 bundles its own
//  Link, so we disable it there [link: false] and add the standalone Link once
//  with our config — passing `link: {…}` through StarterKit double-registers it
//  and Tiptap warns "Duplicate extension names ['link']".)
//
// ── Body contract (W2) ───────────────────────────────────────────────────────
// The wire:model value is a thin ENVELOPE around the ProseMirror doc so future
// engine swaps / schema versioning stay clean:
//
//     { "format": "tiptap", "version": 1, "doc": { ...ProseMirror doc... } }
//
// `format`/`version` let a consumer (nonblock) migrate or validate without
// re-sniffing the doc shape. The component reads the same envelope back on init.
// See reference/components/editor.md §Body contract.
//
// ── Spike finding, load-bearing ──────────────────────────────────────────────
// The Tiptap/ProseMirror instance MUST NOT live in Alpine's reactive data —
// Alpine proxies every property, and a proxied EditorView corrupts ProseMirror
// transactions ("Applying a mismatched transaction"). Keep it in this closure;
// expose only plain serializable state (json, chars) to Alpine.

import { Editor, Mark, markInputRule } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import TaskList from '@tiptap/extension-task-list';
import TaskItem from '@tiptap/extension-task-item';
import Link from '@tiptap/extension-link';

export const BODY_FORMAT = 'tiptap';
export const BODY_VERSION = 1;

// Custom MARK — a "pn-highlight" mark with its own toggle command, keyboard
// shortcut (Mod-Shift-h), and input rule (==text== → highlighted). Proves
// schema extension is a handful of lines, not a fork.
export const PnHighlight = Mark.create({
  name: 'pnHighlight',
  parseHTML() {
    return [{ tag: 'mark.pn-highlight' }];
  },
  renderHTML() {
    return ['mark', { class: 'pn-highlight' }, 0];
  },
  addCommands() {
    return {
      togglePnHighlight: () => ({ commands }) => commands.toggleMark(this.name),
    };
  },
  addKeyboardShortcuts() {
    return { 'Mod-Shift-h': () => this.editor.commands.togglePnHighlight() };
  },
  addInputRules() {
    return [markInputRule({ find: /==([^=]+)==$/, type: this.type })];
  },
});

// Wrap a bare ProseMirror doc in the W2 envelope.
function wrap(doc) {
  return { format: BODY_FORMAT, version: BODY_VERSION, doc };
}

// Accept either an envelope or a bare doc (defensive — older payloads, or a
// caller that passed the doc directly) and return the bare ProseMirror doc, or
// null when there's nothing usable.
function unwrap(value) {
  if (!value) return null;
  let v = value;
  if (typeof v === 'string') {
    try { v = JSON.parse(v); } catch { return null; }
  }
  if (v && typeof v === 'object') {
    if (v.format === BODY_FORMAT && v.doc) return v.doc;     // envelope
    if (v.type === 'doc') return v;                          // bare doc
  }
  return null;
}

const EMPTY_DOC = { type: 'doc', content: [{ type: 'paragraph' }] };

// East-Asian width classification for character counting. Returns the count of
// full-width (CJK ideographs, kana, hangul, full-width forms, CJK punctuation)
// vs. half-width (ASCII, half-width kana, etc.) code points, excluding newlines.
// From these we derive 半角換算 (half-width equivalent: half=1, full=2) and
// 全角換算 (full-width equivalent: full=1, half=0.5 rounded up).
function countWidths(text) {
  let half = 0, full = 0;
  for (const ch of text) {
    const c = ch.codePointAt(0);
    if (c === 0x0a || c === 0x0d) continue; // ignore line breaks
    const isFull =
      (c >= 0x1100 && c <= 0x115f) ||  // Hangul Jamo
      (c >= 0x2e80 && c <= 0x303e) ||  // CJK radicals · Kangxi · CJK punctuation
      (c >= 0x3041 && c <= 0x33ff) ||  // Hiragana · Katakana · CJK symbols
      (c >= 0x3400 && c <= 0x4dbf) ||  // CJK Ext A
      (c >= 0x4e00 && c <= 0x9fff) ||  // CJK Unified
      (c >= 0xa000 && c <= 0xa4cf) ||
      (c >= 0xac00 && c <= 0xd7a3) ||  // Hangul Syllables
      (c >= 0xf900 && c <= 0xfaff) ||  // CJK Compatibility
      (c >= 0xfe30 && c <= 0xfe4f) ||  // CJK Compat Forms
      (c >= 0xff00 && c <= 0xff60) ||  // Full-width ASCII · punctuation
      (c >= 0xffe0 && c <= 0xffe6) ||  // Full-width signs
      (c >= 0x20000 && c <= 0x3fffd);  // CJK Ext B+
    if (isFull) full++; else half++;
  }
  return { half, full };
}

/**
 * Alpine component factory.
 *
 * @param {object}  opts
 * @param {object}  [opts.content]      initial value — envelope, bare doc, or null
 * @param {string}  [opts.placeholder]  empty-paragraph hint
 * @param {string}  [opts.sync]         'blur' | 'debounce:800' | 'manual'  (default 'debounce:800')
 * @param {boolean} [opts.editable]     default true
 */
export function pinionEditor(opts = {}) {
  // NON-reactive closure state (see load-bearing note above).
  let editor = null;
  let debounceTimer = null;

  // Parse the sync cadence prop once: 'blur' | 'debounce:NNN' | 'manual'.
  const sync = String(opts.sync ?? 'debounce:800');
  const syncMode = sync.split(':')[0]; // blur | debounce | manual
  const debounceMs = sync.startsWith('debounce')
    ? (parseInt(sync.split(':')[1], 10) || 800)
    : 800;

  return {
    // Plain serializable state exposed to Alpine / the template.
    json: null,   // live ProseMirror doc JSON (bare doc)
    chars: 0,      // total characters (each code point = 1, newlines excluded)
    charsHalf: 0,  // 半角換算 (half-width equivalent: half=1, full=2)
    charsFull: 0,  // 全角換算 (full-width equivalent: full=1, half=0.5 ⌈⌉)
    _tick: 0,     // bumped on selection/transaction so isActive() re-evals in templates
    // Floating toolbox (Notion-style): appears on a non-empty text selection or
    // right-click, positioned by the closure below. Pure serializable state.
    // `mode` distinguishes a selection bubble (closes when the selection
    // collapses) from a context menu (right-click; stays until an explicit
    // dismiss, so the caret-move selectionUpdate can't close it on open).
    menu: { open: false, mode: null, top: 0, left: 0 },

    get editor() { return editor; },

    init() {
      const startDoc = unwrap(opts.content) ?? EMPTY_DOC;

      editor = new Editor({
        element: this.$refs.editor,
        editable: opts.editable !== false,
        editorProps: {
          // `class` comes from the Composer's `prose` key via x-bind below; we
          // set it here too so a bare mount (no Blade) still styles correctly.
          attributes: { class: this.$refs.editor?.dataset.proseClass || 'pn-prose' },
          handleKeyDown: (_view, event) => {
            // Custom keymap: Mod-Enter → horizontal rule. The seam where slash
            // menus / AI-insert would hook in later.
            if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
              editor.commands.setHorizontalRule();
              return true;
            }
            return false;
          },
        },
        extensions: [
          StarterKit.configure({
            heading: { levels: [1, 2, 3, 4] },
            // Disable StarterKit's bundled Link; we add the standalone Link once
            // with our own config (see import note re: duplicate-name warning).
            link: false,
          }),
          PnHighlight,
          Link.configure({ openOnClick: false, autolink: true }),
          TaskList,
          TaskItem.configure({ nested: true }),
          Placeholder.configure({
            placeholder: ({ node }) => {
              if (node.type.name === 'heading') return 'Heading…';
              return opts.placeholder ?? "Write, or type '/' for blocks…";
            },
            showOnlyWhenEditable: true,
            showOnlyCurrent: false,
          }),
        ],
        content: startDoc,
        onCreate: ({ editor }) => this.read(editor),
        onUpdate: ({ editor }) => {
          this.read(editor);
          if (syncMode === 'debounce') {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => this.flush(), debounceMs);
          }
          // 'blur' and 'manual' do not flush on update.
        },
        onSelectionUpdate: () => { this._tick++; this.refreshMenu(); },
        onBlur: () => {
          // Guaranteed flush on blur for blur AND debounce modes (so a pending
          // debounce can't be lost when focus leaves). 'manual' never auto-flushes.
          if (syncMode !== 'manual') {
            clearTimeout(debounceTimer);
            this.flush();
          }
        },
      });

      // Floating-toolbox triggers: right-click anywhere in the editor, and
      // reposition/hide on scroll or resize while open.
      this._onContext = (e) => this.onContextMenu(e);
      this._onScroll = () => { if (this.menu.open) this.refreshMenu(); };
      // Dismiss: a mousedown outside the toolbox, or Escape. Capture phase so we
      // see it before the toolbox's own mousedown.prevent. The opening
      // right-click's mousedown fires while the menu is still closed, so it
      // never self-dismisses.
      this._onDocDown = (e) => {
        if (this.menu.open && this.$refs.menu && !this.$refs.menu.contains(e.target)) this.closeMenu();
      };
      this._onKey = (e) => { if (e.key === 'Escape' && this.menu.open) this.closeMenu(); };
      this.$refs.editor?.addEventListener('contextmenu', this._onContext);
      window.addEventListener('scroll', this._onScroll, true);
      window.addEventListener('resize', this._onScroll);
      document.addEventListener('mousedown', this._onDocDown, true);
      document.addEventListener('keydown', this._onKey);

      // Seed wire:model on mount so the server has the initial value even before
      // any edit (e.g. a fresh empty doc).
      this.flush();
    },

    // Pull plain state out of the editor (no wire:model write).
    read(editor) {
      this.json = editor.getJSON();
      const { half, full } = countWidths(editor.getText());
      this.chars = half + full;
      this.charsHalf = half + full * 2;            // 半角換算
      this.charsFull = full + Math.ceil(half / 2);  // 全角換算
      this._tick++;
    },

    // Write the envelope to the hidden wire:model input and notify Livewire.
    // Public — toolbar/host can call it (manual mode binds a Save button to it).
    flush() {
      const input = this.$refs.model;
      if (!input) return;
      input.value = JSON.stringify(wrap(this.json ?? this.editor?.getJSON() ?? EMPTY_DOC));
      input.dispatchEvent(new Event('input', { bubbles: true }));
    },

    // ── Toolbar command helpers (thin pass-throughs to Tiptap's chain API) ──
    cmd(fn) {
      const c = editor.chain().focus();
      fn(c);
      c.run();
    },
    isActive(name, attrs) {
      // `this._tick` read makes Alpine re-evaluate button active states on every
      // selection/transaction without putting the editor in reactive data.
      void this._tick;
      return editor?.isActive(name, attrs) ?? false;
    },
    setLink() {
      const prev = editor.getAttributes('link').href;
      const url = window.prompt('URL', prev ?? 'https://');
      if (url === null) return;               // cancelled
      if (url === '') { this.cmd(c => c.unsetLink()); return; }
      this.cmd(c => c.extendMarkRange('link').setLink({ href: url }));
    },

    // ── Floating toolbox (Notion-style bubble) ──────────────────────────────
    // Shown on a non-empty text selection; hidden when the selection collapses.
    refreshMenu() {
      if (!this.$refs.menu) return;
      const sel = editor?.state.selection;
      if (!editor?.isEditable) { this.closeMenu(); return; }
      if (sel && !sel.empty) {
        // A real text selection always (re)opens as a selection bubble.
        this.menu.mode = 'selection';
        this.menu.open = true;
        // position after render so offsetWidth/Height are real (centering needs them)
        this.$nextTick(() => this.positionFromSelection());
      } else if (this.menu.mode !== 'context') {
        // Collapsed selection closes a selection bubble, but NOT a context menu
        // (whose open is the side effect of the very caret move we'd react to).
        this.closeMenu();
      }
    },
    // Position the toolbox centered above the selection, flipping below and
    // clamping to the viewport so it's always fully visible at a sane size.
    positionFromSelection() {
      const dsel = window.getSelection();
      if (!dsel || dsel.rangeCount === 0) return;
      const rect = dsel.getRangeAt(0).getBoundingClientRect();
      if (!rect || (rect.width === 0 && rect.height === 0)) return;
      this.placeAt(rect.left + rect.width / 2, rect.top, rect.bottom);
    },
    placeAt(centerX, anchorTop, anchorBottom) {
      const el = this.$refs.menu;
      const mw = el?.offsetWidth || 320;
      const mh = el?.offsetHeight || 40;
      let top = anchorTop - mh - 8;
      if (top < 8) top = (anchorBottom ?? anchorTop) + 8;       // flip below
      let left = centerX - mw / 2;
      left = Math.max(8, Math.min(left, window.innerWidth - mw - 8));
      this.menu.top = Math.round(top);
      this.menu.left = Math.round(left);
    },
    // Right-click: show the same toolbox (over the selection, or at the pointer).
    onContextMenu(e) {
      if (!this.$refs.menu || !editor?.isEditable) return;
      e.preventDefault();
      const empty = editor.state.selection.empty;
      const px = e.clientX, py = e.clientY;
      this.menu.mode = 'context';
      this.menu.open = true;
      this.$nextTick(() => {
        if (!empty) this.positionFromSelection();
        else this.placeAt(px, py, py);
      });
    },
    closeMenu() { this.menu.open = false; this.menu.mode = null; },
    hideMenu() { this.closeMenu(); },

    // The current envelope as a pretty string (for demo JSON panels).
    get envelopeString() {
      return JSON.stringify(wrap(this.json ?? EMPTY_DOC), null, 2);
    },

    destroy() {
      clearTimeout(debounceTimer);
      this.$refs.editor?.removeEventListener('contextmenu', this._onContext);
      window.removeEventListener('scroll', this._onScroll, true);
      window.removeEventListener('resize', this._onScroll);
      document.removeEventListener('mousedown', this._onDocDown, true);
      document.removeEventListener('keydown', this._onKey);
      editor?.destroy();
      editor = null;
    },
  };
}

export default pinionEditor;
