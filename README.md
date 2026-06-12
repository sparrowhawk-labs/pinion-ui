# Pinion UI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sparrowhawk-labs/pinion-ui.svg?style=flat-square)](https://packagist.org/packages/sparrowhawk-labs/pinion-ui)
[![Total Downloads](https://img.shields.io/packagist/dt/sparrowhawk-labs/pinion-ui.svg?style=flat-square)](https://packagist.org/packages/sparrowhawk-labs/pinion-ui)
[![License](https://img.shields.io/packagist/l/sparrowhawk-labs/pinion-ui.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/sparrowhawk-labs/pinion-ui.svg?style=flat-square)](composer.json)

A Blade UI component library for Laravel built on **Tailwind v4 + daisyUI v5 + Alpine.js**. Ships 46 ready-to-use components and an 11-preset *Tune* token system that lets you reshape spacing, radii, and typography with a single attribute swap — without touching component code.

By [Sparrowhawk Labs](https://sparrowhawk-labs.dev) — part of the `pinion-*` series. Hard-requires [`sparrowhawk-labs/pinion-icons`](https://github.com/sparrowhawk-labs/pinion-icons).

## Features

- **46 components** — buttons, inputs, selects, checkboxes, radios, toggles, textareas, file-upload, rating, range-slider, input-number, input-group, pin-input, dropdowns, popovers, modals, tabs, sidebars, accordions, collapses, alerts, badges, avatars, cards, tooltips, breadcrumbs, paginations, timelines, indicators, steppers, stats, skeletons, spinners, notification toasts, hero sections, theme-switcher, tune-styles, and more.
- **Three orthogonal style layers** — `data-theme` for color, `data-tune` for shape/space/font, Blade props for component variant. Mix freely (`data-theme="dracula" data-tune="playful"`).
- **11 Tune presets** — `default`, `minimal`, `sharp`, `soft`, `playful`, `corporate`, `brutal`, `elegant`, `bold`, `pixel`, `tech`. Each preset bundles ~30 CSS custom properties.
- **Drop-in CSS preset** — one `@import` wires Tailwind `@source` globs (Blade + Compose-layer PHP) and Tune tokens together. No more "did I scan the right paths?" debugging.
- **Compose-layer architecture** — class strings live in typed PHP composers (`InputComposer`, `SelectComposer`, etc.), not scattered in Blade. Variants/sizes/states stay testable and refactor-safe.
- **Dual-use output** — render via `<x-button>` or copy the rendered HTML; it's plain Tailwind + daisyUI + Alpine.
- **LLM-native docs** — ships with [`AGENTS.md`](./AGENTS.md) and per-component reference pages under [`reference/components/`](./reference/components/index.md). `php artisan ui:install --ai` wires the entry pointer into your project's `CLAUDE.md` so Claude Code / Cursor / Aider can look up props and gotchas in one place — no hallucinated props, no class-name guesses.

## Installation

```bash
composer require sparrowhawk-labs/pinion-ui
php artisan ui:install --ai
```

`ui:install` adds the required npm dependencies (`daisyui ^5`, `alpinejs ^3`, `@alpinejs/focus ^3` — needed by `<x-sidebar>` and any focus-trap UI), wires up `resources/css/app.css` (single `@import` of the pinion-ui preset) and `resources/js/app.js` (Alpine + focus plugin), and with `--ai` appends a `## pinion-ui (AI agents)` block to your project's `CLAUDE.md` pointing at `vendor/sparrowhawk-labs/pinion-ui/AGENTS.md`. Drop `--ai` to skip the AI snippet — you can re-run later, or copy the contents of `vendor/sparrowhawk-labs/pinion-ui/CLAUDE_SNIPPET.md` into your own `AGENTS.md` if you prefer that convention.

Then build:

```bash
npm install && npm run build
```

### CSS preset (handled by `ui:install`, here's what it adds)

```css
@import "tailwindcss";

/* Pinion UI preset — loads daisyUI (themes + only the component CSS
   Pinion UI itself uses) and wires @source globs and Tune tokens.
   Path is resolved relative to your app.css. */
@import "../../vendor/sparrowhawk-labs/pinion-ui/src/resources/css/pinion-ui.css";

/* Your own @source globs go AFTER the preset */
@source "../**/*.blade.php";
@source "../**/*.js";
```

> **Do not add your own `@plugin "daisyui";` line** (remove it if an earlier setup added one — `ui:install` does this for you). The preset loads daisyUI with an exclude list: daisyUI *color/theme tokens* stay fully available (`bg-primary`, `data-theme`, all 35 themes), but daisyUI *component classes* (`.btn`, `.card`, `.alert`, …) are not generated. Components come from Pinion UI; daisyUI supplies the color system. A standalone full plugin would silently re-enable every daisyUI component class.

> **Why a preset?**
> Pinion UI's Compose layer keeps class strings inside PHP (e.g. `bg-primary text-primary-content peer-checked:border-primary/70`). Tailwind v4's default scan only sees `*.blade.php` / `*.js`, so without the preset's `@source` rules those classes are silently dropped from the build. The preset's `@source` paths resolve from the preset file's own location — add a new component in the package and consumer apps keep working, no `app.css` re-edit needed.

### Layout

```html
<html data-theme="light" data-tune="default">
```

## Quick start

```blade
{{-- Primary action button --}}
<x-button color="primary" size="md">Save</x-button>

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

{{-- Tabs (nested children since v0.4.0) --}}
<x-tabs variant="underline">
    <x-tab name="overview" label="Overview"><p>…</p></x-tab>
    <x-tab name="specs"    label="Specs"><p>…</p></x-tab>
</x-tabs>

{{-- Hero section --}}
<x-pn::section.hero
    variant="centered"
    title="Build faster with Pinion"
    :primaryAction="['label' => 'Get started', 'href' => '/docs']" />
```

Components are registered as **anonymous components** (no prefix needed) for the common case. The fully-qualified `<x-pn::button>` form is also available if you need to disambiguate.

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
| **minimal** | small radius, no shadow | Inter / Inter + Noto Sans JP | airy spacing, smaller text | Clean, restrained |
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

46 components organised into 7 groups. See [`reference/components/index.md`](./reference/components/index.md) for the full API reference (props, slots, examples) per component.

### Form (15)
`button`, `button-group`, `input`, `textarea`, `select`, `checkbox`, `radio`, `radio-group`, `toggle`, `file-upload`, `rating`, `range-slider`, `input-number`, `input-group`, `pin-input`

### Data display (12)
`card`, `badge`, `avatar`, `avatar-group`, `accordion`, `collapse`, `divider`, `kbd`, `table-scroll`, `timeline`, `stat`, `indicator`

### Feedback (5)
`alert`, `progress`, `skeleton`, `spinner`, `notification-system`

### Navigation (7)
`tabs`, `menu-item`, `dropdown`, `breadcrumb`, `sidebar`, `pagination`, `pagination-simple`

### Overlay (3)
`modal`, `tooltip`, `popover`

### Section (1)
`section.hero`

### Process (1)
`stepper`

### Theme / Tune (2)
`theme-switcher`, `tune-styles`

Most components share a `Compose` layer (`src/Compose/*Composer.php`) that centralises variant / size / state class composition — making behaviour testable and easy to extend. A handful of simple components (button, alert, card, badge, avatar, menu-item, section.hero, theme-switcher, tune-styles) compose their classes inline in the Blade file.

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

## Versioning

Semantic versioning with BC discipline — see [SEMVER.md](./SEMVER.md). TL;DR while in `0.x`: patches are safe to bump, minors may flip defaults (always called out in the release notes), and breaking removals only happen at minor or major boundaries — never in a patch.

## License

MIT — see [LICENSE](LICENSE).

## Credits

- [daisyUI v5](https://daisyui.com) by Pouya Saadeghi (MIT)
- [Tailwind CSS v4](https://tailwindcss.com) (MIT)
- [Alpine.js](https://alpinejs.dev) by Caleb Porzio (MIT)
- Maintained by [Akihiko Takai](https://github.com/akihiko-takai) at [Sparrowhawk Labs](https://sparrowhawk-labs.dev)
