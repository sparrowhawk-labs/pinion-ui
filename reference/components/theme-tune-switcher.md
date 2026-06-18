# x-theme-tune-switcher

A self-contained **`data-theme` × `data-tune` switcher** — two dropdowns (theme + tune) that retune the page live. Each theme option previews its own palette via color dots (`:data-theme`); each tune option previews its own shape via its label (`:data-tune`). Pure inline Alpine; no `ui:install`. The look matches the visualize playground switcher.

> Distinct from [`<x-theme-switcher>`](./theme-switcher.md), which is a simple click-to-cycle light/dark **button**. Use this one when you want to expose the full theme **and** tune axes (e.g. a demo/playground header).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `position` | `'fixed' \| 'inline'` | `'fixed'` | `fixed` = floating top-right card; `inline` = sits in flow (e.g. a header). |
| `storage` | `bool` | `true` | Persist the choice to `localStorage` (so it survives reloads). |
| `storageKey` | `string` | `'pn'` | localStorage key prefix (`{key}-theme` / `{key}-tune`). |
| `themes` | `array \| null` | curated list | Override the theme list. Any daisyUI theme name works. |
| `tunes` | `array \| null` | all tunes | Override the tune list. |

## Behavior

On select it sets `document.documentElement.dataset.theme` / `.tune` (and persists if `storage`). A host that needs to react to the change (e.g. mirror it into an iframe `src`) can watch the `<html>` attributes with a `MutationObserver`. Needs Alpine on the page.

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
