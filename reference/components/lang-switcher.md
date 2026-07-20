# x-lang-switcher

Navbar-style language switcher — a label + chip trigger over a dropdown of locale links, in the **same control family as [`<x-theme-tune-switcher>`](./theme-tune-switcher.md)** so the two sit naturally in one header row (v0.8.0 redesign; the previous `<x-dropdown>`-based chrome read as a different control family). Locale-routing-agnostic: it never builds a URL itself, it renders whatever `href` each locale entry hands it. Options are **server-rendered `<a href>` links**, so static exporters (e.g. spatie/laravel-export) and crawlers see every locale without JS.

## When to use

- Site headers / app shells with 2+ localized routes (`/ja/…`, `/en/…`, …) and server-rendered navigation (no client-side i18n framework).
- You already compute each locale's target URL server-side (locale-prefixed path, subdomain, query param — component doesn't care) and just need a compact trigger + list UI.
- Not for client-side-only locale switching with no distinct URL per locale — that's a plain `<x-dropdown>` with `@click` handlers instead of `href`s.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `locales` | `array<array{code?:string,label?:string,href?:string,active?:bool}>` | `[]` | One entry per locale. `code` identifies it (matched against `current`), `label` is the rendered text (e.g. `'🇯🇵 JA'`), `href` is the link target (defaults to `'#'` if omitted), `active` optionally marks it directly. |
| `current` | `string\|null` | `null` | The active locale's `code`. Matched against `locales[].code` to pick the trigger label and highlight the matching row. Falls back to an entry with `active => true`, then to `locales[0]`. |
| `label` | `string` | `'Lang'` | Visible label text left of the trigger. Pass `''` to hide. |
| `width` | Tailwind width class | `'w-40'` | Dropdown panel width. |
| `size` | `string` | `'sm'` | **Legacy** (pre-v0.8 `<x-dropdown>` chrome) — accepted for backwards compatibility; the redesigned control has a single size. |
| `position` | `string` | `'bottom-end'` | **Legacy** — only the vertical axis is honored now: values starting with `top` open the panel upward, everything else opens downward (right-aligned either way). |

All other attributes pass through to the root element. Needs Alpine on the page (open/close state only).

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

### Beside the theme/tune switcher in a header

```blade
<div class="flex items-center gap-3">
    <x-theme-tune-switcher position="inline" />
    <x-lang-switcher :current="$locale" :locales="$localeLinks" />
</div>
```

## Class composition

Fully utility-composed (no Composer), matching `<x-theme-tune-switcher>`: `text-xs` chip trigger and dropdown panel with **static** radii/borders/shadows, rows as semantic-color links. Like the rest of the switcher family the chrome is **tune-neutral** (`tune-exempt`, v0.8.2) — site chrome is not restyled by the active tune. Never daisyUI component classes.

## Related

- [`<x-theme-tune-switcher>`](./theme-tune-switcher.md) — sibling navbar control for `data-theme` × `data-tune`; pair in the same header row.
- [`<x-settings-switcher>`](./settings-switcher.md) — theme × tune × lang consolidated into one panel for tight (mobile) chrome.
- [`<x-dropdown>`](./dropdown.md) — the generic trigger+panel primitive (used by this component before v0.8.0).
