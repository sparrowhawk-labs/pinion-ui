d# Pinion UI — Component Reference

Per-component API docs for `sparrowhawk-labs/pinion-ui`. Each linked page covers props, slots, examples, and class-composition notes. For **live visual demos** see the [playground repo](https://github.com/sparrowhawk-labs/pinion-ui-playground); for design language (themes, tunes, the three style layers) see the [package README](../../README.md).

## Form

| Component | Description |
|---|---|
| [`<x-button>`](./button.md) | Primary action control. 8 colors × 8 appearances, sizes, loading/disabled, icons, renders as `<a>` when `href` is set. |
| [`<x-button-group>`](./button-group.md) | Joined row of buttons that share borders — toolbars, segmented controls. |
| [`<x-segmented>`](./segmented.md) | iOS-style segmented control — a mutually-exclusive 2–4 option toggle (icon/label), sliding pill. `wire:model` + `segmented-change` event. Pure Alpine. |
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
| [`<x-input-number>`](./input-number.md) | Quantity selector — `<input type="number">` flanked by joined ± buttons. min/max/step clamped both in HTML attrs and Alpine inc/dec logic. Native spinner arrows hidden. |
| [`<x-input-group>`](./input-group.md) | Generic horizontal joiner for form-shaped children — select+input, input+button, multi-input rows. Self-contained Tailwind join (no daisyUI `.join`). Exposes an `addon` helper class for text decorators. |
| [`<x-pin-input>`](./pin-input.md) | OTP / verification code — N single-character boxes with auto-advance, backspace-back, arrow nav, paste-to-fill. `numeric` / `alphanumeric` type, optional `masked`. Combined value submits via hidden input. |
| [`<x-calendar>`](./calendar.md) | Minimal month-grid date picker — a trigger button + popover, ISO `YYYY-MM-DD` via `wire:model`. Pure Alpine (no dep); the same grid is reused as the `<x-sheet>` date-cell editor. **Opt-in JS** — run `ui:install --calendar`. |
| [`<x-editor>`](./editor.md) | Headless rich-text editor (Tiptap / ProseMirror), styled purely with theme × tune tokens. MVP blocks: headings, lists, task list, blockquote, code, link + bold/italic/code/highlight marks. Emits a versioned JSON envelope for `wire:model`. **Opt-in JS** — run `ui:install --editor`. |

## Data display

| Component | Description |
|---|---|
| [`<x-card>`](./card.md) | Surface container with optional header/footer dividers, hover lift, and padding control. |
| [`<x-badge>`](./badge.md) | Small inline label — color, appearance (`soft` / `solid` / `outline` / `ghost`), pill, and optional icon. |
| [`<x-avatar>`](./avatar.md) | User avatar — image, initials, or icon fallback in priority order. |
| [`<x-avatar-group>`](./avatar-group.md) | Stacked / overlapping avatars with optional `+N` overflow badge. |
| [`<x-accordion>`](./accordion.md) | Collapsible item group — parent `<x-accordion>` + nested `<x-accordion-item title>` children, Alpine `x-collapse` animation, single-open by default or `multiple`. |
| [`<x-collapse>`](./collapse.md) | Single collapsible disclosure region — checkbox-driven CSS-only expand/collapse (no-JS). Minimal by default; opt in to `icon="arrow"` or `icon="plus"` affordance. |
| [`<x-divider>`](./divider.md) | Horizontal or vertical separator with optional label. (`direction` prop normalizes daisyUI's historical `divider-horizontal` ↔ vertical naming inversion — kept for backwards compatibility even though the implementation no longer uses daisyUI CSS.) |
| [`<x-kbd>`](./kbd.md) | Keyboard-key display (`<kbd>`) — sizes, color variants, often nested in buttons for shortcuts. |
| [`<x-table-scroll>`](./table-scroll.md) | Overflow-x wrapper for tables that keeps the page from flexing on narrow viewports. |
| [`<x-terminal>`](./terminal.md) | Fake terminal window with a typewriter reveal — demos a CLI step (`artisan tinker`, a seeder run, …) without a real terminal recording. Commands type char-by-char, output lines appear instantly; default slot reveals on finish (`terminal-done` event). |
| [`<x-data-grid>`](./data-grid.md) | Spreadsheet-style editable grid (Tabulator): per-type inline editors, range selection + clipboard + fill, sortable/resizable/reorderable columns. Emits a JSON row-array for `wire:model`. **Opt-in JS** — run `ui:install --data-grid`. |
| [`<x-sheet>`](./sheet.md) | Locality-of-Behavior spreadsheet — same API + data contract as `<x-data-grid>`, but the grid behavior is hand-written in Alpine+Tailwind (no Tabulator). Coexists with `<x-data-grid>`. *(staged build: S0 static render shipped; behavior + `ui:install --sheet` from S1)* |
| [`<x-timeline>`](./timeline.md) | Vertical timeline of events — per-item `state` (`done`/`current`/`upcoming`), `appearance="solid"` default (saturated done-chain), `'soft'` opt-in for muted connector colours. |
| [`<x-stat>`](./stat.md) | Single statistic block — label / value / change indicator. |
| [`<x-stat-group>`](./stat-group.md) | Joins multiple `<x-stat :wrapped="false">` into one bordered/divided card — horizontal, vertical, or responsive. |
| [`<x-indicator>`](./indicator.md) | Positions a badge/dot at a corner of arbitrary child content (notification counters, status dots). `appearance="solid"` default (full color-fill badge — strongest "needs attention" cue) with `'soft'`/`'outline'`/`'ghost'`/`'dash'` opt-ins. |
| [`<x-positioning-map>`](./positioning-map.md) | Generic 2-axis positioning / perceptual map — plots labelled points (price×quality, effort×impact, tune shape×voice). `points` data, `xLabels`/`yLabels`, `active`/`xActive` highlight (static or live Alpine), `quadrants`, sizes. Theme-agnostic, tune-aware frame, zero JS by default. |

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
| [`<x-tabs>`](./tabs.md) | Tabbed content — parent `<x-tabs>` + nested `<x-tab name label :icon>` children, `underline` / `boxed` / `pill` variants, Alpine-driven panel switching. |
| [`<x-menu-item>`](./menu-item.md) | Single nav item — active/disabled states, optional icon, renders `<a>` when `href` is set. |
| [`<x-dropdown>`](./dropdown.md) | Trigger + panel menu built on Alpine — placements, hover/click open, keyboard nav. |
| [`<x-lang-switcher>`](./lang-switcher.md) | Navbar language switcher — `<x-dropdown>` trigger over `<x-menu-item>` locale links, from a `locales` array. Locale-routing-agnostic (you supply each `href`). |
| [`<x-breadcrumb>`](./breadcrumb.md) | Path-style nav from an `$items` array — separators, current-page highlight. |
| [`<x-sidebar>`](./sidebar.md) | Off-canvas drawer panel — left/right placement, `trigger` slot, focus trap, ESC + backdrop click to close. |
| [`<x-pagination>`](./pagination.md) | Full pagination — page numbers, ellipsis, first/last/prev/next, sizes. Accepts a Laravel paginator. |
| [`<x-pagination-simple>`](./pagination-simple.md) | Prev/Next-only pagination for cursor-style or simple lists. |

## Overlay

| Component | Description |
|---|---|
| [`<x-modal>`](./modal.md) | Dialog with backdrop, sizes, title/footer slots, optional floating close button for titleless layouts, ESC + click-outside dismiss. |
| [`<x-tooltip>`](./tooltip.md) | Hover/focus tooltip — placements, sizes, surface variants (`tooltip-light` default, `base-100/200/300`, `color="<semantic>"` for daisyUI native dark bubble). |
| [`<x-popover>`](./popover.md) | Click (or hover) panel hosting arbitrary content — info card, mini form, confirmation prompt. 4 placements, optional arrow, `'click'` (default) / `'hover'` trigger. |

## Section

| Component | Description |
|---|---|
| [`<x-pn::section.hero>`](./section-hero.md) | Hero section — two variants (`centered`, `split`), title/subtitle, primary/secondary actions, `size` (`md`/`lg`/`xl`) for title scale. `split` accepts an `image` prop / `media` slot for image layouts. |
| [`<x-stepper>`](./stepper.md) | Multi-step process indicator — sign-up flow, checkout, wizard. Numbered or dotted circles + connectors, per-item `state` (`done`/`current`/`upcoming`), horizontal or vertical. |

## Theme / Tune

| Component | Description |
|---|---|
| [`<x-theme-switcher>`](./theme-switcher.md) | Click-to-cycle theme switcher — pass `:themes` array, persists to `data-theme` on `<html>`. |
| [`<x-theme-tune-switcher>`](./theme-tune-switcher.md) | `data-theme` × `data-tune` dropdowns (color-dot theme preview + tune preview), live-retune the page. `fixed` / `inline`, localStorage. Pure Alpine. |
| [`<x-tune-styles>`](./tune-styles.md) | Injects the `<style>` block that defines all `data-tune="*"` presets. Place once in your layout `<head>`. Use `:only="['default','tech']"` to ship only the tunes you use. |

## Conventions used in these docs

- **Defaults**: default-value classes are omitted from output unless another modifier requires the explicit class. e.g. `size="md"` on `<x-rating>` still emits its size class because `half` mode requires it.
- **Backwards compatibility**: prop names and defaults are never silently changed. New props are opt-in (default = previous behavior).
- **Pass-through attributes**: any attribute not listed in **Props** is forwarded to the component's root element. Use `class="..."` to extend styles via Tailwind's natural merging.
- **Required peer**: every component that renders icons (`icon` / `iconRight` / `<x-i>` markers) requires [`sparrowhawk-labs/pinion-icons`](https://github.com/sparrowhawk-labs/pinion-icons) — installed as a hard dependency.
