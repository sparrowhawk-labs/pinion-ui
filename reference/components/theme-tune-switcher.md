# x-theme-tune-switcher

A self-contained **`data-theme` × `data-tune` switcher** — two dropdowns (theme + tune) that retune the page live. The theme dropdown shows the shipped v0.6.0 lineup **grouped** (Brand / Mood / SaaS / Industry, from `pn_theme_groups()` — the same `lineup.json` that generates the theme CSS, so the picker cannot drift), with a **sun/moon mode toggle** that flips every entry between its `<name>` (light) and `<name>-dark` columns. Each theme option previews its own palette via color dots (`:data-theme`); each tune option previews its own shape via its label (`:data-tune`). Pure inline Alpine; no `ui:install`. The look matches the visualize playground switcher.

> Distinct from [`<x-theme-switcher>`](./theme-switcher.md), which is a simple click-to-cycle light/dark **button**. Use this one when you want to expose the full theme **and** tune axes (e.g. a demo/playground header).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `position` | `'fixed' \| 'inline'` | `'fixed'` | `fixed` = floating top-right card; `inline` = sits in flow (e.g. a header). |
| `storage` | `bool` | `true` | Persist the choice to `localStorage` (so it survives reloads). |
| `storageKey` | `string` | `'pn'` | localStorage key prefix (`{key}-theme` / `{key}-tune`). |
| `themes` | `array \| null` | grouped lineup | Override with a FLAT list of literal shipped theme ids (e.g. `['pinion-light', 'reactive']`). Disables grouping and the light/dark mode toggle. daisyUI stock names don't exist in the build. |
| `tunes` | `array \| null` | all tunes | Override the tune list. |

## Behavior

On select it sets `document.documentElement.dataset.theme` / `.tune` (and persists if `storage`). The mode toggle re-resolves the current selection to its light/dark counterpart (`payments` ↔ `payments-dark`); initial mode is inferred from the `-dark` suffix of the active theme. A host that needs to react to the change (e.g. mirror it into an iframe `src`) can watch the `<html>` attributes with a `MutationObserver`. Needs Alpine on the page.

## Example

```blade
{{-- floating top-right (default) --}}
<x-theme-tune-switcher />

{{-- inline in a demo header, no persistence --}}
<x-theme-tune-switcher position="inline" :storage="false" />
```

## Class composition

Fully utility-composed (no Composer): a `bg-base-100/90 backdrop-blur` card (fixed) or bare flex (inline); trigger buttons and dropdown `<ul>`s use semantic colors + tune-token radii. The color-dot preview is `bg-primary`/`bg-secondary`/`bg-accent` inside a `:data-theme` span so each row shows that theme's palette. Never daisyUI component classes.

## Related

- [`<x-theme-switcher>`](./theme-switcher.md) — light/dark toggle button (no tune axis).
