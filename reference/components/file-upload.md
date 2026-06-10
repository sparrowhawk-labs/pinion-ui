# x-file-upload

File input with two presentation modes — `inline` (looks like a standard text input with a built-in browser button) and `dropzone` (large dashed-border drop area). Selected files render as a preview list (horizontal or grid) with thumbnails, size, an optional simulated upload progress bar, and a remove button.

**Playground page**: [`pinion-ui-playground/resources/views/pages/file-upload.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/file-upload.blade.php) — full variant matrix and live demos.

## When to use

- Any file picker on a form — single attachment, multi-attachment, image gallery.
- When you want drag-and-drop affordance, set `appearance="dropzone"`. (The native `<input type="file">` already accepts dropped files; the visual *is* the affordance.)
- Use `previewLayout="grid"` for image-heavy galleries; default `horizontal` works for mixed file types.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string \| null` | `null` | Form field name. `multiple` mode appends `[]` automatically. |
| `accept` | `string \| null` | `null` | Native `accept` attribute (e.g. `image/*`, `.pdf,.docx`). |
| `multiple` | `bool` | `false` | Allow multi-file selection. |
| `value` | `mixed` | `null` | Reserved for upstream integrations — not used by the native input. |
| `label` | `string \| null` | `null` | Field label above the input. |
| `description` | `string \| null` | `null` | Helper text — rendered inside the dropzone, or below the inline field. Hidden while `error` is set. |
| `helper` | `string \| null` | `null` | Small helper text below the preview list. Hidden while `error` is set. |
| `placeholder` | `string` | `'Drop a file here or browse'` | Reserved placeholder string (the inline input uses the browser-native chooser button text). |
| `color` | `'neutral' \| 'primary' \| 'secondary' \| 'accent' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'neutral'` | Drives focus / hover / link / progress-bar color. |
| `appearance` | `'outline' \| 'soft' \| 'underline' \| 'ghost' \| 'dropzone'` | `'outline'` | Shell style. The first four match `<x-input>`; `'dropzone'` switches to the large dashed-border drop zone. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Field height / padding / icon size. |
| `previewLayout` | `'horizontal' \| 'grid'` | `'horizontal'` | Layout for the selected-files preview. `horizontal` = stacked rows; `grid` = responsive image-card grid. |
| `error` | `string \| null` | `null` | Error message shown below the field. |
| `disabled` | `bool` | `false` | Native `disabled` + visual dim across the entire component. |
| `simulate` | `bool` | `false` | Fake an upload progress bar after selection (purely visual — does not actually upload). Useful for demos / Livewire wire-and-test layouts where the real upload progress isn't hooked up yet. |

All other attributes pass through to the `<input type="file">` (e.g. `wire:model`, `x-model`, `capture`, `data-*`).

## Slots

This component has no public slots.

## Examples

### Basic inline picker

```blade
<x-file-upload name="avatar" label="Avatar" accept="image/*" />
```

### Dropzone with multi-file + grid preview

```blade
<x-file-upload
    name="photos[]"
    label="Photos"
    appearance="dropzone"
    accept="image/*"
    multiple
    previewLayout="grid"
    description="PNG, JPG, GIF up to 10 MB"
/>
```

### Error state

```blade
<x-file-upload name="resume" label="Résumé" :error="$errors->first('resume')" />
```

### Demo with simulated progress

```blade
<x-file-upload name="files[]" appearance="dropzone" multiple simulate />
```

## Class composition

Class strings live in [`src/Compose/FileUploadComposer.php`](../../src/Compose/FileUploadComposer.php) and the blade renders only the resulting dict. Keys: `wrapper`, `labelText`, `field`, `inputClass`, `dropzoneIcon`, `browseLink`, `dropzoneHint`, `hint`, `previewList`, `previewItem`, `previewThumb`, `previewIconBox`, `previewIconSize`, `previewName`, `previewRemove`, `progressTrack`, `progressFill`.

Inline mode reuses the standard `FieldVariants` shell shared with `<x-input>` / `<x-textarea>` / `<x-select>`. Dropzone mode renders a dashed-border box with per-color hover / focus-within rings (statically enumerated to keep the Tailwind safelist surface unchanged).

## Related

- [`<x-input>`](./input.md) — same shell tokens for non-file inputs.
- [`<x-progress>`](./progress.md) — standalone progress bar (this component embeds a tiny inline version per file).
- `<x-i>` (from [`sparrowhawk-labs/pinion-icons`](https://github.com/sparrowhawk-labs/pinion-icons)) — not required (the SVGs are inlined).

## Notes

- The progress bar flips to `bg-success!` once a row completes (the Alpine `complete` flag) — matches MUI / Ant / Dropzone done-state feedback.
- `multiple` mode automatically appends `[]` to `name` so Laravel receives an array.
- The native `<input type="file">` already handles file drops in dropzone mode, so no drag-handler JS is needed; the dashed border is purely visual.
- Object URLs created for image thumbnails are revoked on remove / replace to avoid memory leaks.
