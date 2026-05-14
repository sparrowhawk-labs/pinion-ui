# x-tabs

Tabbed content panel driven by a single `:tabs` array. Renders the tab list (buttons) and the panels in one component, switching the active panel via an internal Alpine `activeTab` state. Three visual variants and three sizes; the active panel cross-fades on switch.

**Playground page**: [`pinion-ui-playground/resources/views/pages/tabs.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/tabs.blade.php) — full variant matrix and live demos.

## When to use

- Switching between related views inside the same surface — settings sections, product detail pages, dashboard panels.
- When all panels share the same context and only one is visible at a time. For independent collapsible regions use [`<x-accordion>`](./accordion.md) or [`<x-collapse>`](./collapse.md).
- For top-level page navigation prefer [`<x-menu-item>`](./menu-item.md) inside your own nav, not tabs.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `tabs` | `array<string, array{label: string, content?: string, icon?: string}>` | `[]` | Tabs keyed by an arbitrary string id. Each value supplies `label` (required), `content` (raw HTML, rendered via `{!! !!}`), and optional `icon` (raw inline SVG markup, rendered before the label). |
| `variant` | `'underline' \| 'boxed' \| 'pill'` | `'underline'` | Visual style. `underline` shows a bottom border and a colored underline under the active tab; `boxed` wraps the list in a `bg-base-200/50` strip with pill-shaped buttons; `pill` is borderless with a soft background on the active tab. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Tab button height / horizontal padding / text size via `--h-field-*`, `--px-field-*`, `--text-field-*` tune tokens. |
| `default` | `string \| null` | `null` | Key of the tab open on first render. When `null`, the first key in `$tabs` is used. |

All other attributes pass through to the root `<div>` (`id`, `wire:ignore`, etc.).

## Slots

None. Tab content is supplied through the `content` value of each entry in `:tabs` and is rendered with `{!! !!}`, so it must be trusted HTML.

## Examples

### Basic

```blade
<x-tabs :tabs="[
    'overview' => ['label' => 'Overview', 'content' => '<p>Overview body</p>'],
    'specs'    => ['label' => 'Specs',    'content' => '<p>Specs body</p>'],
    'reviews'  => ['label' => 'Reviews',  'content' => '<p>Reviews body</p>'],
]" />
```

### Boxed variant with explicit default

```blade
<x-tabs
    variant="boxed"
    default="specs"
    :tabs="[
        'overview' => ['label' => 'Overview', 'content' => '...'],
        'specs'    => ['label' => 'Specs',    'content' => '...'],
    ]"
/>
```

### With per-tab icon

`icon` is raw SVG/HTML — inline an icon and the label will follow it inside the same flex row.

```blade
@php
    $icon = '<svg class="w-4 h-4" ...>...</svg>';
@endphp

<x-tabs :tabs="[
    'home' => ['label' => 'Home', 'icon' => $icon, 'content' => '...'],
    'docs' => ['label' => 'Docs', 'icon' => $icon, 'content' => '...'],
]" />
```

### Pill variant, small size

```blade
<x-tabs variant="pill" size="sm" :tabs="$tabs" />
```

## Class composition

Class strings are built by [`TabsComposer::compose($props)`](../../src/Compose/TabsComposer.php) — keys: `root`, `tabList`, `tabBase`, `tabActive`, `tabIdle`, `iconWrap`, `panels`. The active/idle classes are bound on each tab `<button>` via `:class` so theme tokens stay consistent across variants.

## Related

- [`<x-accordion>`](./accordion.md) — multiple regions visible at once.
- [`<x-collapse>`](./collapse.md) — single collapsible region.
- [`<x-menu-item>`](./menu-item.md) — for navigational links rather than panel switching.

## Notes

- **Security: `content` is rendered raw** via `{!! !!}` so you can pass HTML / Blade-rendered partials. The rule: **never interpolate untrusted input into `content`**. If you need user-supplied text, escape it before passing (e.g. `'content' => e($userText)`), or stitch via `view('partial', [...])->render()` so Blade handles escaping inside the partial. The same rule applies to `icon` (raw inline SVG).
- Tab keys must be unique per `<x-tabs>` instance; duplicates collapse to the same panel.
- Panels use `x-show` + `x-cloak`, so include `[x-cloak]{display:none}` in your CSS to avoid a flash of all panels on first paint.
- The underline variant draws the active indicator with a `border-b-2` on the button itself — no separate moving indicator element.
- Default `panels` margin is `mt-[var(--space-compact)]`; override with `class="..."` on the root if you need a tighter join.
