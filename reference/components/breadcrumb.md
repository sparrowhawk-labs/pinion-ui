# x-breadcrumb

Path-style navigation trail showing where the current page sits in the site hierarchy. Renders an array of `$items` as a plain-Tailwind flex list (no daisyUI structural class), or accepts a default slot for fully custom markup. Two separators (`chevron` default, `slash`) and three sizes.

**Playground page**: [`pinion-ui-playground/resources/views/pages/breadcrumb.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/breadcrumb.blade.php) — full variant matrix and live demos.

## When to use

- Showing the current page's position inside a nested hierarchy — admin dashboards, documentation sites, e-commerce category trees.
- When the user benefits from seeing each ancestor as a clickable link back up the tree.
- For flat site navigation use [`<x-menu-item>`](./menu-item.md) inside a custom nav; for tabbed sub-views use [`<x-tabs>`](./tabs.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | `array<array{label: string, url?: string}> \| null` | `null` | Items rendered in order. Each item with a `url` becomes an `<a>`; otherwise renders as a `<span>` (typical for the current page, last in the trail). When `null` the default slot is used instead. |
| `separator` | `'chevron' \| 'slash'` | `'chevron'` | Separator between items, drawn on each non-first `<li>`'s `::before` pseudo-element. `chevron` renders a literal `›`; `slash` renders a literal `/`. Both are muted via reduced opacity. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Adds `text-sm` / `text-lg` to the wrapper; `md` emits no size class (daisyUI default). |

All other attributes pass through to the root `<div>`.

## Slots

- **default** *(optional)* — fully custom `<li>` markup when `:items` is not supplied. The component still renders the outer scrollable `<div>` + flex `<ul>`, so you only need the items.

## Examples

### Basic, items array

```blade
<x-breadcrumb :items="[
    ['label' => 'Home',     'url' => '/'],
    ['label' => 'Products', 'url' => '/products'],
    ['label' => 'Detail'],
]" />
```

### Slash separator

```blade
<x-breadcrumb separator="slash" :items="[
    ['label' => 'Docs',         'url' => '/docs'],
    ['label' => 'Components',   'url' => '/docs/components'],
    ['label' => 'Breadcrumb'],
]" />
```

### Custom slot markup

```blade
<x-breadcrumb>
    <li><a href="/"><x-i type="home" class="w-4 h-4" /> Home</a></li>
    <li><a href="/projects">Projects</a></li>
    <li>Current</li>
</x-breadcrumb>
```

### Compact size inside a header

```blade
<x-breadcrumb size="sm" :items="$crumbs" />
```

## Class composition

Class strings come from [`BreadcrumbComposer::compose($props)`](../../src/Compose/BreadcrumbComposer.php) — plain Tailwind, no daisyUI structural class (per the project rule that daisyUI classes are semantic-color-only). Two keys: `root` (on the outer `<div>`) joins `overflow-x-auto` with the optional size class; `list` (on the `<ul>`) joins `flex items-center flex-nowrap list-none` with the separator override. Both separators are Tailwind arbitrary selectors targeting `[&_li+li]:before:content-[...]` — `›` for chevron, `/` for slash — with `mx-2 opacity-40` for spacing/muting.

`list` also carries `[&_li>a]:inline-flex [&_li>a]:items-center [&_li>a]:gap-1` (and the `span` equivalent) so any icon+label crumb built via the slot API stays on one line. This exists because Tailwind's preflight sets `svg { display: block }` — an `<x-i>` icon dropped into a plain (inline) `<a>`/`<span>` becomes a block-level child, which breaks the inline formatting context and forces the label text onto its own line. You don't need to add `inline-flex`/`gap` yourself when mixing `<x-i>` into a crumb's slot markup (see "Custom slot markup" below) — the component forces it on every `<li>`'s direct `<a>`/`<span>`, icon or not.

## Related

- [`<x-menu-item>`](./menu-item.md) — for top-level nav.
- [`<x-tabs>`](./tabs.md) — for switching between sibling views.

## Notes

- The current (last) page should not have a `url` — render as plain `<span>` so it isn't a clickable link to itself.
- `overflow-x-auto` on the root `<div>` gives built-in horizontal scrolling for very long trails on narrow viewports (this used to be a side effect of daisyUI's `breadcrumbs` class; it's now explicit Tailwind).
- Both separators are drawn purely with Tailwind arbitrary variants — no daisyUI pseudo-element to reset. If you customize further, target `[&_li+li]:before:*` (on the `list` key), not the `<li>` itself — see `BreadcrumbComposer::separatorClass()`.
