# x-pn::tune-styles

Injects the `<style>` block that defines every `data-tune="*"` preset. Each preset writes ~12 CSS custom properties (radii, borders, depth, section / element / inline spacing) onto its `[data-tune="<name>"]` selector. Place once in your layout `<head>` and set `<html data-tune="...">` to switch presets at runtime.

**Playground page**: no dedicated demo page — see the [overview](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/overview.blade.php) and the [Tune preset table in the README](../../README.md#tune-presets).

## When to use

- **Every Pinion UI app needs this once.** Without it, `data-tune="..."` is a no-op and components fall back to whatever values the active Tailwind / daisyUI defaults provide.
- During design exploration, when you want to swap entire design languages from a dev console by changing `<html data-tune="...">`.
- For production, prefer importing `tune.css` through your CSS pipeline (better caching, no inline-style payload). This Blade fallback is the zero-config path for prototypes and demos.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `only` | `string \| null` | `null` (all tunes) | Comma-separated list of tune names to emit. Keeps the rendered CSS payload small when you ship only one or two tunes. e.g. `:only="'default,tech'"`. |

All other attributes are ignored — the component emits raw `<style>` blocks, not an element you can style.

## Slots

None.

## Examples

### Layout head (typical)

```blade
<!DOCTYPE html>
<html lang="en" data-theme="light" data-tune="default">
<head>
    <meta charset="utf-8">
    <title>My App</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <x-pn::tune-styles />
</head>
<body>
    {{ $slot }}
</body>
</html>
```

### Ship only the tunes you actually use

```blade
{{-- App only uses default + tech — strip the other 9 presets from the inline CSS --}}
<x-pn::tune-styles only="default,tech" />
```

### Switch tunes at runtime (any JS)

```html
<html data-tune="elegant">
```

```js
document.documentElement.setAttribute('data-tune', 'playful');
```

## Class composition

Tune-styles has **no classes** to compose — it renders a sequence of `<style>` blocks built from a PHP `$tunes` map inside [`src/resources/views/components/tune-styles.blade.php`](../../src/resources/views/components/tune-styles.blade.php). The map is the single source of truth for every preset's CSS variable values; edit it (or import `tune.css` and skip this component) to add or tune presets.

## Related

- [`<x-theme-switcher>`](./theme-switcher.md) — controls `data-theme` (color palette), orthogonal to `data-tune`.
- [`<x-pn::section.hero>`](./section-hero.md) — primary consumer of `--space-section` / `--space-section-inner` Tune variables.
- [`<x-button>`](./button.md), [`<x-input>`](./input.md), etc. — every form / field component reads `--radius-field`, `--h-field-*`, `--px-field-*`, `--text-field-*` from the active tune.

## Notes

- **Place once.** Multiple `<x-pn::tune-styles>` instances on a page will emit duplicate `<style>` blocks. The browser will deduplicate the effect but you pay the bytes twice — keep it in the layout `<head>`.
- All 11 presets (`default`, `minimal`, `sharp`, `soft`, `playful`, `corporate`, `brutal`, `elegant`, `bold`, `pixel`, `tech`) ship in the inline `$tunes` map (the original ten since v0.2.3, `minimal` added later). Shape, border, depth, and spacing tokens render correctly from the Blade fallback alone. Full font fidelity (custom typefaces like Press Start 2P or JetBrains Mono) still requires importing `tune.css` via the CSS pipeline — the inline fallback does not assign `--font-heading` / `--font-body` / `--font-mono`.
- Each preset sets: `--radius-box`, `--radius-field`, `--radius-selector`, `--border`, `--depth`, `--noise`, `--size-selector`, `--size-field`, `--space-section`, `--space-section-inner`, `--space-element`, `--space-compact`, `--space-text`, `--space-inline`.
- Font-family variables (`--font-heading`, `--font-body`, `--font-mono`) are part of the broader Tune system but are loaded from the imported `tune.css` / font CSS, not from this Blade fallback. Use the CSS pipeline path if you need the full font-stack swap.
- This component is **load-bearing for the whole design system** — strip it without an equivalent `tune.css` import and every component silently falls back to daisyUI / Tailwind defaults (square radii, no section spacing, etc.).
