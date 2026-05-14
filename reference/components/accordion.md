# x-accordion

Group of expandable items driven by Alpine state. Pass an `:items` array of `{title, content}` entries; only one is open at a time by default, or pass `multiple` to allow several. Uses `x-collapse` for height animation and emits `aria-expanded` on each header button.

**Playground page**: [`pinion-ui-playground/resources/views/pages/accordion.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/accordion.blade.php) — full variant matrix and live demos.

## When to use

- FAQ pages, settings groups, or any list of disclosure regions where most are closed at rest.
- Set `multiple` when items are independent (e.g. filter panels); leave default for mutually exclusive sections.
- For a single disclosure use `<x-collapse>`; for tabbed (always-one-visible) navigation use `<x-tabs>`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | `array<int\|string, array{title: string, content: string}>` | `[]` | List of accordion items. Array keys (when string) become the open-state identifier; numeric keys fall back to the loop index. `content` is rendered with `{!! !!}` — pass safe markup only. |
| `multiple` | `bool` | `false` | If `true`, multiple items can be open simultaneously (state is an array). If `false`, opening one closes the others (state is a single key or null). |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Drives `--h-field-*`, `--px-field-*`, `--text-field-*` tune tokens for header height / padding / text size. |

All other attributes pass through to the root `<div>`.

## Slots

This component is array-driven; it does not accept a default slot. For per-item slot control, compose multiple `<x-collapse>` instances manually.

## Examples

### Basic (single-open)

```blade
<x-accordion :items="[
    ['title' => 'What is Pinion UI?', 'content' => 'A Blade component kit for Laravel.'],
    ['title' => 'Does it require Alpine?', 'content' => 'Only dynamic components need Alpine.'],
    ['title' => 'How do I theme it?', 'content' => 'Use daisyUI themes via <code>data-theme</code>.'],
]" />
```

### Multiple open

```blade
<x-accordion multiple :items="[
    ['title' => 'Privacy', 'content' => '<p>We collect minimal telemetry.</p>'],
    ['title' => 'Cookies', 'content' => '<p>Only session cookies are used.</p>'],
]" />
```

### Large size with string keys

```blade
<x-accordion size="lg" :items="[
    'install'  => ['title' => 'Install', 'content' => '<code>composer require ...</code>'],
    'config'   => ['title' => 'Configure', 'content' => 'Publish the config file.'],
]" />
```

## Class composition

See [`src/Compose/AccordionComposer.php`](../../src/Compose/AccordionComposer.php). Returns `root`, `header`, `icon`, `content`, and `sizeClasses` keys. The root takes `radius-box tune-border border-base-300` with `divide-y` between items; the chevron uses `transition-transform duration-200` and rotates 180° when the item is open (driven by Alpine `:class`).

## Related

- [`<x-collapse>`](./collapse.md) — single disclosure region (this component renders one wrapper per item but without the shared open-state coordinator).
- [`<x-tabs>`](./tabs.md) — choose tabs when content swaps in place rather than expanding inline.

## Notes

- **Security: `content` is rendered raw** via `{!! !!}` so you can pass markup. The rule: **never interpolate untrusted input into `content`**. If you need user-supplied text, escape it before passing (e.g. `'content' => e($userText)`), or stitch via `view('partial', [...])->render()` so Blade handles escaping inside the partial. The wrapper does not sanitize.
- The component generates a unique `accordion_<uniqid>` per instance, but state is local to that Alpine `x-data` scope — opening an item in one accordion does not affect others on the page.
- Header height tracks `--h-field-*` from the active tune; that's why `size` uses the same `sm/md/lg` token names as form fields.
- `multiple=false` toggles with `open = open === '<key>' ? null : '<key>'`, so clicking the open item closes it (no always-one-open enforcement).
