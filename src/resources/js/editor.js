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
    chars: 0,
    _tick: 0,     // bumped on selection/transaction so isActive() re-evals in templates

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
            heading: { levels: [1, 2, 3] },
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
        onSelectionUpdate: () => { this._tick++; },
        onBlur: () => {
          // Guaranteed flush on blur for blur AND debounce modes (so a pending
          // debounce can't be lost when focus leaves). 'manual' never auto-flushes.
          if (syncMode !== 'manual') {
            clearTimeout(debounceTimer);
            this.flush();
          }
        },
      });

      // Seed wire:model on mount so the server has the initial value even before
      // any edit (e.g. a fresh empty doc).
      this.flush();
    },

    // Pull plain state out of the editor (no wire:model write).
    read(editor) {
      this.json = editor.getJSON();
      this.chars = editor.getText().length;
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

    // The current envelope as a pretty string (for demo JSON panels).
    get envelopeString() {
      return JSON.stringify(wrap(this.json ?? EMPTY_DOC), null, 2);
    },

    destroy() {
      clearTimeout(debounceTimer);
      editor?.destroy();
      editor = null;
    },
  };
}

export default pinionEditor;
