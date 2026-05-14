# x-breadcrumb

Path-style navigation trail showing where the current page sits in the site hierarchy. Renders an array of `$items` as a daisyUI `breadcrumbs` list, or accepts a default slot for fully custom markup. Two separators (`chevron` default, `slash`) and three sizes.

**Playground page**: [`pinion-ui-playground/resources/views/pages/breadcrumb.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/breadcrumb.blade.php) — full variant matrix and live demos.

## When to use

- Showing the current page's position inside a nested hierarchy — admin dashboards, documentation sites, e-commerce category trees.
- When the user benefits from seeing each ancestor as a clickable link back up the tree.
- For flat site navigation use [`<x-menu-item>`](./menu-item.md) inside a custom nav; for tabbed sub-views use [`<x-tabs>`](./tabs.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | `array<array{label: string, url?: string}> \| null` | `null` | Items rendered in order. Each item with a `url` becomes an `<a>`; otherwise renders as a `<span>` (typical for the current page, last in the trail). When `null` the default slot is used instead. |
| `separator` | `'chevron' \| 'slash'` | `'chevron'` | Separator between items. `chevron` is daisyUI's default rotated square; `slash` overrides the `::before` pseudo-element to render a literal `/` with relaxed opacity. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Adds `text-sm` / `text-lg` to the wrapper; `md` emits no size class (daisyUI default). |

All other attributes pass through to the root `<div>`.

## Slots

- **default** *(optional)* — fully custom `<li>` markup when `:items` is not supplied. The component still renders the outer `<div class="breadcrumbs">` + `<ul>`, so you only need the items.

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

Class strings come from [`BreadcrumbComposer::compose($props)`](../../src/Compose/BreadcrumbComposer.php) — single key `root` joining `breadcrumbs` (daisyUI base), the size class, and the optional separator override. The slash variant uses a Tailwind arbitrary selector `[&_li+li]:before:content-['/']` plus several resets to neutralize daisyUI's default rotated-square separator.

## Related

- [`<x-menu-item>`](./menu-item.md) — for top-level nav.
- [`<x-tabs>`](./tabs.md) — for switching between sibling views.

## Notes

- The current (last) page should not have a `url` — render as plain `<span>` so it isn't a clickable link to itself.
- The `breadcrumbs` daisyUI class provides built-in horizontal scrolling for very long trails on narrow viewports.
- The `slash` separator deliberately resets `border-0 rotate-0 w-auto h-auto` because daisyUI's default `::before` renders a rotated, sized, bordered square — see `BreadcrumbComposer::separatorClass()`.
- Per [`docs/daisyui/`](../../docs/daisyui/) the separator size lives on the `::before` pseudo-element; if you customize further, target `[&_li+li]:before:*` not the `<li>`.
