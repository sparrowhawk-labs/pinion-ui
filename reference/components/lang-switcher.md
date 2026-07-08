# x-lang-switcher

Navbar-style language switcher — a `<x-dropdown>` trigger (current locale) over a list of `<x-menu-item>` links, one per locale. Locale-routing-agnostic: it never builds a URL itself, it renders whatever `href` each locale entry hands it.

**Playground page**: no dedicated demo page yet — see the [layout header](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/layouts/playground.blade.php) where a hand-rolled locale-chip row currently lives (candidate to migrate to this component).

## When to use

- Site headers / app shells with 2+ localized routes (`/ja/…`, `/en/…`, …) and server-rendered navigation (no client-side i18n framework).
- You already compute each locale's target URL server-side (locale-prefixed path, subdomain, query param — component doesn't care) and just need a compact trigger + list UI.
- Not for client-side-only locale switching with no distinct URL per locale — that's a plain `<x-dropdown>` with `@click` handlers instead of `href`s.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `locales` | `array<array{code?:string,label?:string,href?:string,active?:bool}>` | `[]` | One entry per locale. `code` identifies it (matched against `current`), `label` is the rendered text (e.g. `'🇯🇵 JA'`), `href` is the link target (defaults to `'#'` if omitted), `active` optionally marks it directly. |
| `current` | `string\|null` | `null` | The active locale's `code`. Matched against `locales[].code` to pick the trigger label and highlight the matching menu item. Falls back to an entry with `active => true`, then to `locales[0]`. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'sm'` | Forwarded to the underlying `<x-dropdown>` and `<x-menu-item>` — trigger height/text size and item height/text size. |
| `position` | `'bottom-end' \| 'bottom-start' \| 'top-end' \| 'top-start'` | `'bottom-end'` | Forwarded to `<x-dropdown>` — menu placement relative to the trigger. |
| `width` | Tailwind width class | `'w-40'` | Forwarded to `<x-dropdown>` — menu panel width. |

All other attributes pass through to the root `<x-dropdown>` element.

## Slots

None — locales are data-driven via the `locales` prop, not slot content.

## Examples

### Basic (Laravel route-based locale prefix)

```blade
<x-lang-switcher
    :current="app()->getLocale()"
    :locales="[
        ['code' => 'ja', 'label' => '🇯🇵 JA', 'href' => '/ja' . $path],
        ['code' => 'en', 'label' => '🇬🇧 EN', 'href' => '/en' . $path],
        ['code' => 'zh-Hans', 'label' => '🇨🇳 ZH-S', 'href' => '/zh-Hans' . $path],
        ['code' => 'zh-Hant', 'label' => '🇹🇼 ZH-T', 'href' => '/zh-Hant' . $path],
    ]"
/>
```

### Inside a header

```blade
<header class="flex items-center justify-between p-4">
    <a href="/" class="font-semibold">My App</a>
    <x-lang-switcher :current="$locale" :locales="$localeLinks" size="sm" position="bottom-end" />
</header>
```

## Class composition

Lang-switcher has no Composer of its own — it's a thin composition of [`<x-dropdown>`](./dropdown.md) (trigger + panel chrome) and [`<x-menu-item>`](./menu-item.md) (each locale row), both of which read `DropdownComposer` / `FieldVariants` tune tokens. Override the panel width via `width`, or the trigger/item look by overriding `class` on the two underlying components if you fork this template.

## Related

- [`<x-dropdown>`](./dropdown.md) — the generic trigger+panel primitive this component wraps.
- [`<x-menu-item>`](./menu-item.md) — the per-locale row; renders `<a>` since `href` is always set.
- [`<x-theme-tune-switcher>`](./theme-tune-switcher.md) — a sibling navbar control for `data-theme` × `data-tune`, useful to pair in the same header row.
