# x-calendar

A minimal month-grid **date picker** — a trigger button that opens a popover calendar. Pure Alpine + Tailwind, **no third-party dependency** (PrelineUI-flavoured: a flat themed grid, rounded day cells, primary fill on the selected day, a soft ring on today). Emits an ISO `YYYY-MM-DD` string for `wire:model`.

Navigation: `‹` / `›` step the month; **clicking the month label switches to a year grid** (pick a year, then back to days); **今日** jumps to the current month; **クリア** empties the value.

The same month grid (`calendar-grid`) is reused as the **date-cell editor inside [`<x-sheet>`](./sheet.md)** — double-clicking a date cell opens this calendar.

**Opt-in JS** (like `<x-editor>`/`<x-sheet>`): registered only by `php artisan ui:install --calendar`. No npm dep; the styles are utility-composed (no extra CSS bundle).

## Install

```bash
php artisan ui:install --calendar     # registers the pinionCalendar Alpine factory (no npm dep)
npm run build
```

Adds to `resources/js/app.js` (before `Alpine.start()` / `Livewire.start()`):

```js
import { pinionCalendar } from '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/calendar.js';
Alpine.data('pinionCalendar', pinionCalendar);
```

> A Livewire-ESM single-Alpine host (e.g. nonblock) registers this with a **manual** `app.js` edit (its `app.js` is hand-authored).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `value` | `string \| null` | `null` | Initial ISO date `YYYY-MM-DD` (or empty). |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Trigger field scale (the popover grid is size-invariant). |
| `placeholder` | `string` | `'日付を選択'` | Trigger text when no date is chosen. |

`wire:model` is forwarded to a hidden `<input>` (a string ISO date). All other attributes pass through to the root.

## Value contract

The popover writes the selected ISO date string to the hidden `wire:model` carrier and dispatches a bubbling `calendar-select` CustomEvent `{ value }`. Picking a day sets the value and closes the popover; **今日** jumps the view to the current month; **クリア** empties the value.

```blade
<x-calendar wire:model="dueDate" />
```

## Examples

### Standalone

```blade
<x-calendar :value="$task->due?->toDateString()" wire:model.live="due" />
```

### Inside `<x-sheet>`

Date columns (`'type' => 'date'`) automatically use this calendar as their cell editor — no extra wiring. Double-click a date cell to open it.

## Class composition

Chrome (trigger / panel / header / nav / weekday / day / selected / today / footer) → [`CalendarComposer`](../../src/Compose/CalendarComposer.php) (a pure flat class-string dict). The month grid markup is the shared [`calendar-grid`](../../src/resources/views/components/calendar-grid.blade.php) partial, rendered inside the `pinionCalendar` Alpine scope (Alpine scope follows DOM ancestry, so the partial's `x-on`/`x-bind` resolve against the factory). All classes are plain Tailwind + daisyUI semantic colors + tune tokens — never daisyUI component classes.

## Notes

- **`new Date()`** is used in the browser factory (current month / today highlight) — this is fine; it only runs client-side.
- **Overflow-safe**: inside `<x-sheet>` the popover is `position: fixed` (anchored via `anchorTo()`), so the grid's `overflow:auto` never clips it.

## Related

- [`<x-sheet>`](./sheet.md) — reuses this calendar as its date-cell editor.
- [`<x-input>`](./input.md) — for a plain native `<input type="date">` when a popover isn't wanted.
