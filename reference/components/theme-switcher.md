# x-theme-switcher

Click-to-cycle theme switcher. Toggles between an Alpine-tracked list of theme names, writes the result to `data-theme` on `<html>`, and persists it to `localStorage` under the key `theme`. Renders a sun / moon SVG that swaps based on whether the current theme is dark (name is `dark` or ends in `-dark` — the v0.6.0 pair convention).

**Playground page**: no dedicated demo page — see the [overview](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/overview.blade.php) where the switcher lives in the header.

## When to use

- Site headers and app shells that expose a light / dark (or n-way) theme toggle.
- Any time the user should be able to cycle between a fixed set of shipped pinion-ui themes without a dropdown.
- For a full theme **picker** (search, preview tiles), build a `<x-dropdown>` over the same `document.documentElement.setAttribute('data-theme', …)` call; this component is intentionally a single-button cycle.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `themes` | `array<string>` | `['pinion-light', 'pinion-dark']` | Themes to cycle through, in order. Each value must be a shipped pinion-ui theme (`<name>` / `<name>-dark` pairs, or `reactive`) — daisyUI stock names don't exist in the build (v0.6.0). |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Button + icon size. `sm` → `w-8 h-8` / `w-4 h-4` icon. `lg` → `w-12 h-12` / `w-6 h-6` icon. |

All other attributes pass through to the root `<button>` (`class`, `aria-label`, `@click`, etc.). A default `aria-label="Toggle theme"` is set unless overridden.

## Slots

None — the icon is rendered by the component (sun in dark theme, moon otherwise). For custom icons, build a thin Alpine wrapper that calls the same `document.documentElement.setAttribute('data-theme', …)` + `localStorage.setItem('theme', …)` pair.

## Examples

### Default (pinion-light ↔ pinion-dark)

```blade
<x-theme-switcher />
```

### Multi-theme cycle

```blade
<x-theme-switcher :themes="['pinion-light', 'pinion-dark', 'mood-monokai', 'mood-monokai-dark']" size="lg" />
```

### Inside a header

```blade
<header class="flex items-center justify-between p-4">
    <a href="/" class="font-semibold">My App</a>
    <x-theme-switcher size="sm" />
</header>
```

## Class composition

Theme-switcher composes classes **inline** in [`src/resources/views/components/theme-switcher.blade.php`](../../src/resources/views/components/theme-switcher.blade.php) — Alpine state lives in the same template, so there is no Composer layer. Override with `class="..."` to change the surface (e.g. `class="bg-base-200 hover:bg-base-300"`).

## Related

- [`<x-theme-tune-switcher>`](./theme-tune-switcher.md) — the Tune layer (`data-tune`, defined in `tune.css` via the `pinion-ui.css` preset) is orthogonal to Theme (`data-theme`). Use this combined switcher for full design-language control.
- [`<x-dropdown>`](./dropdown.md) — wrap multiple `<x-theme-switcher>`-style buttons inside a dropdown if you need a richer picker UI.

## Notes

- The switcher reads the initial theme from `document.documentElement.getAttribute('data-theme')` and falls back to `themes[0]`. If a value exists in `localStorage` under the `theme` key **and** is included in `:themes`, it overrides the SSR value on mount.
- This means the daisyUI / SSR theme can flash briefly before Alpine hydrates. To avoid the flash, render the initial `data-theme` server-side from the same `localStorage` value (e.g. via a small head script).
- Persistence is `localStorage` only — no cookie, no server round-trip. SSR consumers should set `data-theme` from their own store and rely on this component only for client-side toggling.
- The component renders a `<button type="button">` — it will not submit forms.
