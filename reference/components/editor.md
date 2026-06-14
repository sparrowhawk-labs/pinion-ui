# x-editor

Headless rich-text editor (Tiptap / ProseMirror) styled entirely with pinion-ui
theme (`data-theme`) × tune (`data-tune`) tokens. Emits a versioned JSON
**envelope** for `wire:model`. This is pinion-ui's only JS-behavior component, so
its engine is **opt-in** — see [Install](#install). Non-editor apps that never
run `ui:install --editor` pull zero Tiptap bundle.

**MVP block set**: paragraph, heading H1–H3, bullet list, ordered list,
task/checkbox list, blockquote, code (inline + fenced), link; marks
bold / italic / code / highlight. Tables / images / columns are deferred.

## Install

`<x-editor>` needs its JS module wired into the consumer's `resources/js/app.js`
and its Tiptap npm deps added. One command does both:

```bash
php artisan ui:install --editor
npm install && npm run build
```

This adds to `package.json` (Tiptap v3):

```
@tiptap/core  @tiptap/starter-kit  @tiptap/extension-placeholder
@tiptap/extension-task-list  @tiptap/extension-task-item  @tiptap/extension-link
```

(`task-list` / `task-item` are **not** in StarterKit. StarterKit v3 bundles its
own Link, but `editor.js` disables it and adds the standalone Link with custom
config, so it's listed explicitly.) And injects into `resources/js/app.js`:

```js
import { pinionEditor } from '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/editor.js';
Alpine.data('pinionEditor', pinionEditor);
```

The `.pn-prose` block styles ship in the CSS preset (`pinion-ui.css` imports
`editor.css`), so no extra CSS step is needed — only the JS opt-in.

## When to use

- A document / note / article body that an end-user (or an AI agent) edits as
  structured rich text, persisted as JSON.
- When you need fine-grained control over the schema (custom marks/nodes,
  keymaps, slash menus) — Tiptap is headless, so extension is a few lines.
- For a single-line or short scalar field, use [`<x-input>`](./input.md) /
  [`<x-textarea>`](./textarea.md). The editor body is JSON, not a scalar.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Toolbar button size, body padding, base prose text size, min editable height. |
| `placeholder` | `string \| null` | `null` | Empty-paragraph hint. Empty headings always show `Heading…`. |
| `sync` | `'blur' \| 'debounce:NNN' \| 'manual'` | `'debounce:800'` | When the body JSON is flushed to `wire:model`. `blur` = on focus-out only; `debounce:800` = ~800ms after the last keystroke **plus** a guaranteed flush on blur; `manual` = never automatically — call `flush()` from your own Save control. |
| `disabled` | `bool` | `false` | Dims the surface, blocks pointer events, hides the toolbar. |
| `editable` | `bool` | `true` | When `false`, renders read-only (no toolbar, content not editable). |
| `toolbar` | `bool` | `true` | Show the formatting toolbar. Ignored (hidden) when not editable. |
| `content` | `array \| null` | `null` | Initial value. Accepts the **envelope**, a bare ProseMirror doc, or `null` (empty doc). Usually you omit this and let `wire:model` hydrate. |

All other attributes pass through to the outer Alpine host `<div>`, **except**
`wire:model*`, which is forwarded to a dedicated hidden `<input>` (see below).
Disambiguated use: `<x-pn::editor>`.

## Slots

- **footer** — optional status / shortcut-hint row rendered under the body
  (border-t, muted text). Example: `<x-slot:footer>⌘⇧H highlight</x-slot:footer>`.

## Body contract (the `wire:model` JSON) — W2 / gate G3

The component's `wire:model` value is a **thin envelope** around the ProseMirror
doc, NOT the bare doc:

```json
{
  "format": "tiptap",
  "version": 1,
  "doc": { "type": "doc", "content": [ /* ...ProseMirror nodes... */ ] }
}
```

- `format` — engine id. Lets a future engine swap be detected without sniffing
  the doc shape. Always `"tiptap"` for this component.
- `version` — schema/contract version (currently `1`). Bump on a
  breaking change to the doc shape so consumers can migrate deterministically.
- `doc` — the raw ProseMirror document (`editor.getJSON()`).

The value is stored as a JSON **string** in the hidden input (Livewire receives
a string and the app casts/validates it). On hydration the component accepts an
envelope, a bare doc, **or** a JSON string of either — so an older bare-doc value
still loads.

### Tiny example (empty editor)

```json
{ "format": "tiptap", "version": 1, "doc": { "type": "doc", "content": [ { "type": "paragraph" } ] } }
```

### For an AI agent generating `update_body` payloads

Produce the **whole envelope**. Keep `format: "tiptap"` and `version: 1`. Put the
new document under `doc` as a valid ProseMirror doc using only the MVP node/mark
set above. Minimal valid skeleton:

```json
{ "format": "tiptap", "version": 1,
  "doc": { "type": "doc", "content": [
    { "type": "heading", "attrs": { "level": 1 }, "content": [ { "type": "text", "text": "Title" } ] },
    { "type": "paragraph", "content": [ { "type": "text", "text": "Body." } ] }
  ] } }
```

Task-list nodes use `type: "taskList"` → `taskItem` (with `attrs.checked`):

```json
{ "type": "taskList", "content": [
  { "type": "taskItem", "attrs": { "checked": false },
    "content": [ { "type": "paragraph", "content": [ { "type": "text", "text": "todo" } ] } ] }
] }
```

## Examples

### Livewire-bound (typical)

```blade
<x-editor wire:model="body" placeholder="Write something…" />
```

The Livewire property `$body` receives the envelope JSON string on the chosen
sync cadence (default: 800ms debounce + flush on blur).

### Manual save

```blade
<div x-data>
    <x-editor wire:model="body" sync="manual" x-ref="ed" />
    <x-button x-on:click="$refs.ed._x_dataStack[0].flush()">Save</x-button>
</div>
```

(`manual` never auto-flushes; call `flush()` on the editor's Alpine scope.)

### Read-only render of stored content

```blade
<x-editor :editable="false" :content="$doc->body" />
```

## Class composition

Class strings come from
[`SparrowhawkLabs\PinionUi\Compose\EditorComposer`](../../src/Compose/EditorComposer.php).
Keys: `root`, `toolbar`, `toolbarGroup`, `button`, `buttonActive`, `divider`,
`counter`, `body`, `prose`, `footer`. The `.pn-prose` descendant rules (lists,
taskList, blockquote, code, the `pn-highlight` mark, the placeholder
pseudo-element) live in the bundled
[`editor.css`](../../src/resources/css/editor.css) because they can't be utility
classes. Behavior is in
[`editor.js`](../../src/resources/js/editor.js) (the opt-in module).

## Notes

- **The Tiptap instance is not in Alpine reactive data.** It lives in the
  module's closure; only `json` / `chars` are exposed to Alpine. A proxied
  `EditorView` corrupts ProseMirror transactions. Do not "fix" this by moving the
  editor onto `x-data`.
- **Custom extensions** ship in `editor.js`: the `pnHighlight` mark
  (`⌘⇧H` / `==text==`), a `⌘↵` → horizontal-rule keymap, and a context-aware
  placeholder. These are the seams for slash menus / AI-insert later.
- Non-Livewire scalar `name=""` form posting is **not** supported — the body is
  JSON. Use `wire:model`, or read the value from the Alpine scope.

## Related

- [`<x-textarea>`](./textarea.md) — plain multi-line text (scalar).
- [`<x-input>`](./input.md) — single-line scalar field.
