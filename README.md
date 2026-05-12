# Pinion UI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sparrowhawk-labs/pinion-ui.svg?style=flat-square)](https://packagist.org/packages/sparrowhawk-labs/pinion-ui)
[![Total Downloads](https://img.shields.io/packagist/dt/sparrowhawk-labs/pinion-ui.svg?style=flat-square)](https://packagist.org/packages/sparrowhawk-labs/pinion-ui)
[![License](https://img.shields.io/packagist/l/sparrowhawk-labs/pinion-ui.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/sparrowhawk-labs/pinion-ui.svg?style=flat-square)](composer.json)

A Blade UI component library for Laravel built on **Tailwind v4 + daisyUI v5 + Alpine.js**. Ships 21 ready-to-use components and a 10-preset *Tune* token system that lets you reshape spacing, radii, and typography with a single attribute swap — without touching component code.

By [Sparrowhawk Labs](https://sparrowhawk-labs.dev) — part of the `pinion-*` series. Hard-requires [`sparrowhawk-labs/pinion-icons`](https://github.com/sparrowhawk-labs/pinion-icons).

## Features

- **21 components** — buttons, inputs, selects, checkboxes, radios, toggles, textareas, file-upload, dropdowns, modals, tabs, accordions, alerts, badges, avatars, cards, theme-switcher, menu-item, hero section, and more.
- **Three orthogonal style layers** — `data-theme` for color, `data-tune` for shape/space/font, Blade props for component variant. Mix freely (`data-theme="dracula" data-tune="playful"`).
- **10 Tune presets** — `default`, `sharp`, `soft`, `playful`, `corporate`, `brutal`, `elegant`, `bold`, `pixel`, `tech`. Each preset bundles ~30 CSS custom properties.
- **Drop-in CSS preset** — one `@import` wires Tailwind `@source` globs (Blade + Compose-layer PHP) and Tune tokens together. No more "did I scan the right paths?" debugging.
- **Compose-layer architecture** — class strings live in typed PHP composers (`InputComposer`, `SelectComposer`, etc.), not scattered in Blade. Variants/sizes/states stay testable and refactor-safe.
- **Dual-use output** — render via `<x-button>` or copy the rendered HTML; it's plain Tailwind + daisyUI + Alpine.

## Installation

```bash
composer require sparrowhawk-labs/pinion-ui
php artisan ui:install
```

`ui:install` adds the required npm dependencies (`daisyui ^5`, `alpinejs ^3`), wires up `resources/css/app.css` and `resources/js/app.js`, and (optionally) writes a `## pinion-ui` reference block into your project's `CLAUDE.md`.

Then build:

```bash
npm install && npm run build
```

### CSS preset (handled by `ui:install`, here's what it adds)

```css
@import "tailwindcss";
@plugin "daisyui" { themes: all; }

/* Pinion UI preset — wires @source globs and Tune tokens.
   Path is resolved relative to your app.css. */
@import "../../vendor/sparrowhawk-labs/pinion-ui/src/resources/css/pinion-ui.css";

/* Your own @source globs go AFTER the preset */
@source "../**/*.blade.php";
@source "../**/*.js";
```

> **Why a preset?**
> Pinion UI's Compose layer keeps class strings inside PHP (e.g. `bg-primary text-primary-content peer-checked:border-primary/70`). Tailwind v4's default scan only sees `*.blade.php` / `*.js`, so without the preset's `@source` rules those classes are silently dropped from the build. The preset's `@source` paths resolve from the preset file's own location — add a new component in the package and consumer apps keep working, no `app.css` re-edit needed.

### Layout

```html
<html data-theme="light" data-tune="default">
```

## Quick start

```blade
{{-- Primary action button --}}
<x-button variant="primary" size="md">Save</x-button>

{{-- Form field with label + error --}}
<x-input label="Email" type="email" error="Required" />

{{-- Card with hover lift --}}
<x-card hoverable>
    <p>Card body</p>
</x-card>

{{-- Modal with Alpine-driven open state --}}
<x-modal title="Confirm" size="md">
    {{-- modal body --}}
</x-modal>

{{-- Tabs --}}
<x-tabs :tabs="$tabs" variant="underline" />

{{-- Hero section --}}
<x-pinion-ui::section.hero
    variant="centered"
    title="Build faster with Pinion"
    :primaryAction="['label' => 'Get started', 'href' => '/docs']" />
```

Components are registered as **anonymous components** (no prefix needed) for the common case. The fully-qualified `<x-pinion-ui::button>` form is also available if you need to disambiguate.

## The three style layers

| Layer | Attribute / Prop | Controls | Examples |
|-------|------------------|----------|----------|
| **Theme** | `data-theme` | Color palette | `light`, `dark`, `cyberpunk`, `dracula` (any daisyUI theme) |
| **Tune** | `data-tune` | Shape, spacing, font, component sizing | `default`, `tech`, `elegant`, `playful` |
| **Component** | Blade props | Variant, size, behavior | `variant="primary"`, `size="lg"`, `dismissible` |

Theme and Tune are fully orthogonal — any combination works.

## Tune presets

| Tune | Shape | Font (heading / body) | Sizing | Character |
|------|-------|-----------------------|--------|-----------|
| **default** | standard radius | Inter / Inter + Noto Sans JP | standard | Neutral, all-purpose |
| **sharp** | no radius | DM Sans + Noto Sans JP | slightly smaller | Geometric, precise |
| **soft** | large radius | Nunito + Zen Maru Gothic | slightly larger | Soft, rounded |
| **playful** | maximum radius | Fredoka / Quicksand + Zen Maru Gothic | larger | Playful, pop |
| **corporate** | small radius, no shadow | Source Sans 3 + Noto Sans JP | compact | Solid, business |
| **brutal** | no radius, thick borders | Space Grotesk + M PLUS 1p | slightly larger | Raw, impactful |
| **elegant** | standard radius, hairline borders | Playfair Display / Lora + Shippori Mincho | standard (wider) | Refined, serif |
| **bold** | standard radius, thick borders | Montserrat + Noto Sans JP (w900) | slightly larger | Heavy, strong |
| **pixel** | no radius, thick borders | Press Start 2P + DotGothic16 | slightly larger | Retro, dotted |
| **tech** | tiny radius, no shadow | JetBrains Mono / IBM Plex Sans + M PLUS 1 Code | compact | Technical, dense |

Each preset writes CSS custom properties across four categories:

- **Shape** — `--radius-box`, `--radius-field`, `--radius-selector`, `--border`, `--depth`, `--noise`
- **Spacing** — `--space-section`, `--space-section-inner`, `--space-element`, `--space-compact`, `--space-text`, `--space-inline`
- **Font** — `--font-heading`, `--font-body`, `--font-mono`, `--font-weight-heading`
- **Component Size** — `--h-field-{xs,sm,md,lg}`, `--px-field-{xs,sm,md,lg}`, `--text-field-{xs,sm,md,lg}`

## Components

### Form
`button`, `input`, `select`, `textarea`, `checkbox`, `radio`, `radio-group`, `toggle`, `file-upload`

### Data display
`card`, `badge`, `avatar`, `accordion`

### Feedback
`alert`

### Navigation
`tabs`, `menu-item`, `dropdown`

### Overlay
`modal`

### Action
`theme-switcher`, `tune-styles`

### Section
`section.hero`

All form components share a `Compose` layer (`src/Compose/*Composer.php`) that centralises variant / size / state class composition — making behaviour testable and easy to extend.

## Pairs with Pinion Icons

`pinion-ui` hard-requires [`sparrowhawk-labs/pinion-icons`](https://github.com/sparrowhawk-labs/pinion-icons) — install it once, then use `<x-i>` anywhere alongside `pinion-ui` components:

```blade
<x-button variant="primary">
    <x-i type="check" class="w-4 h-4" /> Confirm
</x-button>
```

7,404 Solar icons across 6 variants, plus Fluent Emoji and Pixelarticons via virtual variants — no extra setup required.

## Pinion series

Pinion UI is part of the [Sparrowhawk Labs](https://sparrowhawk-labs.dev) `pinion-*` series — Laravel UI plugins built around a shared design language. A *pinion* is a primary flight feather: the stroke that lets a hawk steer.

- [`sparrowhawk-labs/pinion-icons`](https://github.com/sparrowhawk-labs/pinion-icons) — unified icon system, hard-required by this package
- **`sparrowhawk-labs/pinion-ui`** *(this package)*
- `sparrowhawk-labs/sparrowhawk` *(framework core, in design)*

## License

MIT — see [LICENSE](LICENSE).

## Credits

- [daisyUI v5](https://daisyui.com) by Pouya Saadeghi (MIT)
- [Tailwind CSS v4](https://tailwindcss.com) (MIT)
- [Alpine.js](https://alpinejs.dev) by Caleb Porzio (MIT)
- Maintained by [Akihiko Takai](https://github.com/akihiko-takai) at [Sparrowhawk Labs](https://sparrowhawk-labs.dev)
