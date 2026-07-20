# x-theme-tune-switcher

A self-contained **`data-theme` × `data-tune` switcher** — two dropdowns (theme + tune) that retune the page live. The theme dropdown shows the shipped v0.7.0 lineup **grouped** (Brand / Mood / SaaS / Industry, from `pn_theme_groups()` — the same `lineup.json` that generates the theme CSS, so the picker cannot drift). Theme names are bare (no `mood-` prefix since v0.7.0), so the category is surfaced visually instead: each group heading carries a colored dot (Brand=primary / Mood=accent / SaaS=info / Industry=secondary) and the trigger shows a matching category chip next to the selected theme name. A **sun/moon mode toggle** that flips every entry between its `<name>` (light) and `<name>-dark` columns. Each theme option previews its own palette via color dots (`:data-theme`); each tune option previews its own shape via its label (`:data-tune`). Pure inline Alpine; no `ui:install`. The look matches the visualize playground switcher.

> Distinct from [`<x-theme-switcher>`](./theme-switcher.md), which is a simple click-to-cycle light/dark **button**. Use this one when you want to expose the full theme **and** tune axes (e.g. a demo/playground header).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `position` | `'fixed' \| 'inline'` | `'fixed'` | `fixed` = floating top-right card; `inline` = sits in flow (e.g. a header). |
| `compact` | `bool` | `false` | Icon-only triggers; labels and value text hidden, current values discoverable via hover `title`. For mobile or tight chrome. In both full and compact the bar order is sun/moon (mode) **first/leftmost**, then theme, then tune. |
| `drop` | `'down' \| 'up'` | `'down'` | Dropdown direction. Use `'up'` when the switcher sits at the bottom of the screen (e.g. a compact mobile bar), so the panels open above it. |
| `attribution` | `bool` | `true` | Show the pinion-ui attribution link, pinned faint/small at each dropdown's **top-right** (outside the scrollable list, so it stays visible while the list scrolls). Opt out with `:attribution="false"`. |
| `link` | `'github' \| 'site' \| URL` | `'github'` | Attribution link target: `'github'` = the pinion-ui repo (default), `'site'` = pinion-ui.dev, or any URL string. |
| `storage` | `bool` | `true` | Persist the choice to `localStorage` (so it survives reloads). |
| `storageKey` | `string` | `'pn'` | localStorage key prefix (`{key}-theme` / `{key}-tune`). |
| `themes` | `array \| null` | grouped lineup | Override with a FLAT list of literal shipped theme ids (e.g. `['pinion', 'reactive']`). Disables grouping and the light/dark mode toggle. daisyUI stock names don't exist in the build. |
| `tunes` | `array \| null` | all tunes | Override the tune list. |

## Behavior

On select it sets `document.documentElement.dataset.theme` / `.tune` (and persists if `storage`). The mode toggle re-resolves the current selection to its light/dark counterpart (`payments` ↔ `payments-dark`); initial mode is inferred from the `-dark` suffix of the active theme. A host that needs to react to the change (e.g. mirror it into an iframe `src`) can watch the `<html>` attributes with a `MutationObserver`. Needs Alpine on the page.

## Example

```blade
{{-- floating top-right (default) --}}
<x-theme-tune-switcher />

{{-- inline in a demo header, no persistence --}}
<x-theme-tune-switcher position="inline" :storage="false" />

{{-- compact icon bar pinned to the bottom of a mobile shell — dropdowns open upward --}}
<div class="fixed bottom-4 right-4 z-50">
    <x-theme-tune-switcher position="inline" compact drop="up" />
</div>
```

## Class composition

Fully utility-composed (no Composer): a `bg-base-100/90 backdrop-blur` card (fixed) or bare flex (inline); trigger buttons and dropdown panels use semantic colors with **static** radii/borders/shadows. The switcher chrome is **tune-neutral** (`tune-exempt`, v0.8.2): a control that changes the tune is not itself re-rendered by it — pinned base font, no draft rough-filter, no tune-token radius/shadow. Only the per-option previews react: the color-dot preview is `bg-primary`/`bg-secondary`/`bg-accent` inside a `:data-theme` span, and each tune option renders its own name under its own `:data-tune`. (The trigger shows the current tune's *name* in the neutral font — it no longer previews the tune's typeface.) Never daisyUI component classes.

## Related

- [`<x-theme-switcher>`](./theme-switcher.md) — light/dark toggle button (no tune axis).
