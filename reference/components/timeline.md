# x-timeline

Vertical (default) or horizontal timeline of events, built on daisyUI's `timeline` utility. Pass an `:items` array of `{title, time, desc, side, state}` entries; each item gets a middle icon, a connector line, and a content box. Per-item `state` (`done` / `current` / `upcoming`) tints both the icon and the connecting line.

**Playground page**: [`pinion-ui-playground/resources/views/pages/timeline.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/timeline.blade.php) — full variant matrix and live demos.

## When to use

- Activity logs, shipment tracking, onboarding progress, version history.
- Use `compact` for dense lists; use `snap` to keep middle icons aligned to the start of the box.
- For static step counts (1-2-3) prefer a simple flex row; reach for `<x-timeline>` when each event has time / description metadata.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | `array<array{title: string, time?: string, desc?: string, side?: 'start'\|'end', state?: 'done'\|'current'\|'upcoming'}>` | `[]` | List of events. `title` is required-ish (renders even if blank). `time` shows as small muted text on the opposite side. `desc` is a subdued second line inside the box. `side` controls which side the title box sits on for alternating layouts. `state` drives icon + connector color. |
| `orientation` | `'vertical' \| 'horizontal'` | `'vertical'` | `vertical` → `timeline-vertical`. `horizontal` → `timeline-horizontal` (events laid out left-to-right). |
| `compact` | `bool` | `false` | Adds `timeline-compact` for tighter spacing — drops every item to the start side. |
| `snap` | `bool` | `false` | Adds `timeline-snap-icon` so middle icons align to the start of the box rather than the centerline. |
| `appearance` | `'solid' \| 'soft'` | `'solid'` | Saturation of the done/default state. `'solid'` (default — full `text-primary` icons + `bg-primary` connector) makes the completion chain stand out. Opt into `'soft'` (`text-primary/70` + `bg-primary/30`) for a calmer look when many done items stack and the saturated trail feels heavy. `current` and `upcoming` states are unaffected. |

All other attributes pass through to the root `<ul>`.

## Slots

This component is array-driven; it does not accept a default slot.

## Examples

### Basic vertical

```blade
<x-timeline :items="[
    ['time' => '2024', 'title' => 'Founded', 'desc' => 'First commit.'],
    ['time' => '2025', 'title' => 'Public beta', 'desc' => 'v0.1 release.'],
    ['time' => '2026', 'title' => 'v1.0', 'desc' => 'Stable API.'],
]" />
```

### With state coloring

```blade
<x-timeline :items="[
    ['time' => 'Step 1', 'title' => 'Sign up', 'state' => 'done'],
    ['time' => 'Step 2', 'title' => 'Verify email', 'state' => 'current'],
    ['time' => 'Step 3', 'title' => 'Invite team', 'state' => 'upcoming'],
]" />
```

### Alternating sides

```blade
<x-timeline :items="[
    ['time' => '9:00', 'title' => 'Standup', 'side' => 'start'],
    ['time' => '11:00', 'title' => 'Review', 'side' => 'end'],
    ['time' => '14:00', 'title' => 'Deploy', 'side' => 'start'],
]" />
```

### Horizontal + compact

```blade
<x-timeline orientation="horizontal" compact :items="[
    ['title' => 'Cart', 'state' => 'done'],
    ['title' => 'Address', 'state' => 'done'],
    ['title' => 'Payment', 'state' => 'current'],
    ['title' => 'Done', 'state' => 'upcoming'],
]" />
```

### Soft variant (muted saturation)

```blade
<x-timeline appearance="soft" :items="[
    ['time' => 'Step 1', 'title' => 'Sign up', 'state' => 'done'],
    ['time' => 'Step 2', 'title' => 'Verify email', 'state' => 'done'],
    ['time' => 'Step 3', 'title' => 'Invite team', 'state' => 'current'],
]" />
```

## Class composition

See [`src/Compose/TimelineComposer.php`](../../src/Compose/TimelineComposer.php). Returns `root`, `orientation`, `middle`, `box`, `stateColors`, `hrColors`. State → color maps are pipe-joined strings consumed by the static helper `TimelineComposer::pick($map, $key)` (used inside the Blade view to look up per-item icon + connector colors without per-item PHP arrays). Missing or unknown `state` falls back to `done` (primary tint).

## Related

- [`<x-accordion>`](./accordion.md) — alternative for log-like content where items expand inline.
- [`<x-stat>`](./stat.md) — single-event variant when only the latest matters.

## Notes

- **v0.3.4 default reverted**: `appearance` defaults to `'solid'` again. v0.3.0 briefly flipped to `'soft'` to calm dense done-chains, but the visual hierarchy of the timeline relies on the saturated primary trail — soft made the completed segments fade into the upcoming ones. Use `appearance="soft"` per call site when you specifically want the muted look.
- Default item state is `done` (primary-tinted icon + connector). Pass `state="upcoming"` to fade items still ahead.
- The middle icon is a hardcoded check-circle SVG; to customise per item, fork the blade view — there is currently no `icon` field on the item array.
- Per `docs/daisyui/pages/timeline.md`, `timeline-compact` collapses all items onto the start side regardless of the per-item `side` field — pair it with consistent left-aligned items.
- `time` field is rendered as small muted text (`text-xs text-base-content/60`) on the opposite side of the title box.
