d# Pinion UI — Component Reference

Per-component API docs for `sparrowhawk-labs/pinion-ui`. Each linked page covers props, slots, examples, and class-composition notes. For **live visual demos** see the [playground repo](https://github.com/sparrowhawk-labs/pinion-ui-playground); for design language (themes, tunes, the three style layers) see the [package README](../../README.md).

## Form

| Component | Description |
|---|---|
| [`<x-button>`](./button.md) | Primary action control. 8 colors × 7 appearances, sizes, loading/disabled, icons, renders as `<a>` when `href` is set. |
| [`<x-button-group>`](./button-group.md) | Joined row of buttons that share borders — toolbars, segmented controls. |
| [`<x-input>`](./input.md) | Text input with label, error, prefix/suffix slots, and all native `type` values. |
| [`<x-textarea>`](./textarea.md) | Multi-line text input. Same field-token system as `<x-input>`. |
| [`<x-select>`](./select.md) | Native `<select>` with consistent styling, label, and error slot. |
| [`<x-checkbox>`](./checkbox.md) | Single checkbox with `soft` (default) and `solid` appearances; label inline or wrapped. |
| [`<x-radio>`](./radio.md) | Single radio input — pair with `<x-radio-group>` for shared `name` semantics. |
| [`<x-radio-group>`](./radio-group.md) | Manages a set of radios with one `name`, label, and error. |
| [`<x-toggle>`](./toggle.md) | iOS-style switch for boolean state. |
| [`<x-file-upload>`](./file-upload.md) | File input with drag-and-drop affordance and selected-file preview. |
| [`<x-rating>`](./rating.md) | Star rating input — supports half-star granularity via `rating-half`. |
| [`<x-range-slider>`](./range-slider.md) | `<input type="range">` with label / hint / error chrome, 8 colours × 5 sizes. Optional live `showValue` via Alpine. |

## Data display

| Component | Description |
|---|---|
| [`<x-card>`](./card.md) | Surface container with optional header/footer dividers, hover lift, and padding control. |
| [`<x-badge>`](./badge.md) | Small inline label — color, appearance (`soft` / `solid` / `outline` / `ghost`), pill, and optional icon. |
| [`<x-avatar>`](./avatar.md) | User avatar — image, initials, or icon fallback in priority order. |
| [`<x-avatar-group>`](./avatar-group.md) | Stacked / overlapping avatars with optional `+N` overflow badge. |
| [`<x-accordion>`](./accordion.md) | Collapsible item group built on `<details>` for no-JS expand/collapse. |
| [`<x-collapse>`](./collapse.md) | Single collapsible disclosure region — daisyUI `collapse` (checkbox-based, no-JS). Minimal by default; opt in to `icon="arrow"` or `icon="plus"` affordance. |
| [`<x-divider>`](./divider.md) | Horizontal or vertical separator with optional label. (daisyUI `divider-horizontal` ↔ vertical naming inverted; this wrapper normalizes.) |
| [`<x-kbd>`](./kbd.md) | Keyboard-key display (`<kbd>`) — sizes, color variants, often nested in buttons for shortcuts. |
| [`<x-table-scroll>`](./table-scroll.md) | Overflow-x wrapper for tables that keeps the page from flexing on narrow viewports. |
| [`<x-timeline>`](./timeline.md) | Vertical timeline of events — per-item `state` (`done`/`current`/`upcoming`), `appearance="soft"` default (v0.3) for muted connector colours, `'solid'` for the pre-v0.3 saturated look. |
| [`<x-stat>`](./stat.md) | Single statistic block — label / value / change indicator. Group via flex/grid. |
| [`<x-indicator>`](./indicator.md) | Positions a badge/dot at a corner of arbitrary child content (notification counters, status dots). `appearance="soft"` default (v0.3) with `'solid'`/`'outline'`/`'ghost'`/`'dash'` opt-ins. |

## Feedback

| Component | Description |
|---|---|
| [`<x-alert>`](./alert.md) | Inline message — color, 11 appearances (`bordered-left` default, plus `solid`, `soft`, `outline`, `vivid`, `bordered-top`, etc.), title, dismissible, icon. |
| [`<x-progress>`](./progress.md) | Linear progress bar — determinate (`value` + `max`) or indeterminate stripe. |
| [`<x-skeleton>`](./skeleton.md) | Animated placeholder for loading state — rectangle / circle / line shapes, custom sizing. |
| [`<x-spinner>`](./spinner.md) | Loading spinner — sizes, color variants, and `dots` / `ring` shapes. |
| [`<x-notification-system>`](./notification-system.md) | Toast container — listens for `notify` events; trigger via Alpine `$dispatch('notify', {type, content})`, vanilla JS `window.dispatchEvent`, Livewire `$this->dispatch()`, or Laravel `session('notify')` flash. Stackable, positioned. |

## Navigation

| Component | Description |
|---|---|
| [`<x-tabs>`](./tabs.md) | Tabbed content with `underline` / `lift` / `box` / `border` variants and `:tabs` prop API. |
| [`<x-menu-item>`](./menu-item.md) | Single nav item — active/disabled states, optional icon, renders `<a>` when `href` is set. |
| [`<x-dropdown>`](./dropdown.md) | Trigger + panel menu built on Alpine — placements, hover/click open, keyboard nav. |
| [`<x-breadcrumb>`](./breadcrumb.md) | Path-style nav from an `$items` array — separators, current-page highlight. |
| [`<x-sidebar>`](./sidebar.md) | Off-canvas drawer panel — left/right placement, `trigger` slot, focus trap, ESC + backdrop click to close. |
| [`<x-pagination>`](./pagination.md) | Full pagination — page numbers, ellipsis, first/last/prev/next, sizes. Accepts a Laravel paginator. |
| [`<x-pagination-simple>`](./pagination-simple.md) | Prev/Next-only pagination for cursor-style or simple lists. |

## Overlay

| Component | Description |
|---|---|
| [`<x-modal>`](./modal.md) | Dialog with backdrop, sizes, title/footer slots, optional floating close button for titleless layouts, ESC + click-outside dismiss. |
| [`<x-tooltip>`](./tooltip.md) | Hover/focus tooltip — placements, sizes, surface variants (`tooltip-light` default, `base-100/200/300`, `color="<semantic>"` for daisyUI native dark bubble). |

## Section

| Component | Description |
|---|---|
| [`<x-pn::section.hero>`](./section-hero.md) | Hero section — two variants (`centered`, `split`), title/subtitle, primary/secondary actions, `size` (`md`/`lg`/`xl`) for title scale. `split` accepts an `image` prop / `media` slot for image layouts. |

## Theme / Tune

| Component | Description |
|---|---|
| [`<x-theme-switcher>`](./theme-switcher.md) | Click-to-cycle theme switcher — pass `:themes` array, persists to `data-theme` on `<html>`. |
| [`<x-tune-styles>`](./tune-styles.md) | Injects the `<style>` block that defines all `data-tune="*"` presets. Place once in your layout `<head>`. Use `:only="['default','tech']"` to ship only the tunes you use. |

## Conventions used in these docs

- **Defaults**: daisyUI defaults are omitted from output classes unless another modifier requires the explicit class. e.g. `size="md"` on `<x-rating>` still emits `rating-md` because `rating-half` requires it.
- **Backwards compatibility**: prop names and defaults are never silently changed. New props are opt-in (default = previous behavior).
- **Pass-through attributes**: any attribute not listed in **Props** is forwarded to the component's root element. Use `class="..."` to extend styles via Tailwind's natural merging.
- **Required peer**: every component that renders icons (`icon` / `iconRight` / `<x-i>` markers) requires [`sparrowhawk-labs/pinion-icons`](https://github.com/sparrowhawk-labs/pinion-icons) — installed as a hard dependency.
