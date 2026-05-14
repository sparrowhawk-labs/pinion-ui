# x-stat

Single statistic block — a label / value / description triple with an optional figure (icon or image) and a trend arrow (↑ / ↓ / →). Wraps in daisyUI's `stats shadow` container by default; set `wrapped="false"` to drop the outer wrapper when grouping multiple stats yourself.

**Playground page**: [`pinion-ui-playground/resources/views/pages/stat.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/stat.blade.php) — full variant matrix and live demos.

## When to use

- Dashboard KPI tiles (revenue, signups, conversion).
- Compact metric strips inside cards or section headers.
- Group multiple stats by placing several `<x-stat :wrapped="false">` inside a single `<div class="stats shadow">`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `label` | `string` | `''` | Top-line label (`stat-title`). Muted by daisyUI's default styling. |
| `value` | `string \| int \| null` | `null` | The headline number / text (`stat-value`). Hidden if null. |
| `desc` | `string \| null` | `null` | Secondary description line (`stat-desc`). Combined with `trend` arrow and `trendValue` when those are present. |
| `valueColor` | `'primary' \| 'secondary' \| 'accent' \| 'info' \| 'success' \| 'warning' \| 'error' \| null` | `null` | Tints the `value` text and the `figure` slot. Null = base-content. |
| `trend` | `'up' \| 'down' \| 'flat' \| null` | `null` | Arrow + desc color. `up` → ↑ + success; `down` → ↓ + error; `flat` → → + muted. |
| `trendValue` | `string \| null` | `null` | Bold-weight inline value appended to the description (e.g. `'+12%'`). |
| `wrapped` | `bool` | `true` | If `true`, wraps the block in `stats shadow`. Set `false` to compose multiple stats into one external `stats` container. |

All other attributes pass through to the root element.

## Slots

- **default** — figure content (icon, small image) rendered before the label. The presence of any non-whitespace content activates the `stat-figure` block.

## Examples

### Basic

```blade
<x-stat label="Total revenue" value="$12,480" />
```

### With trend

```blade
<x-stat
    label="Active users"
    value="8,421"
    trend="up"
    trendValue="+12.3%"
    desc="from last month"
/>
```

### Tinted value

```blade
<x-stat label="Errors today" value="3" valueColor="error" />
```

### With figure (icon)

```blade
<x-stat label="Downloads" value="1.2M" valueColor="primary">
    <x-i type="download" class="w-8 h-8" />
</x-stat>
```

### Grouped (unwrapped)

```blade
<div class="stats shadow">
    <x-stat :wrapped="false" label="Revenue" value="$12k" />
    <x-stat :wrapped="false" label="Orders" value="248" />
    <x-stat :wrapped="false" label="Refunds" value="3" valueColor="error" />
</div>
```

## Class composition

See [`src/Compose/StatComposer.php`](../../src/Compose/StatComposer.php). Returns `root` (`stats shadow` or empty), `inner` (`stat`), `figure`, `title`, `value`, `desc`, `arrow`. The arrow character (`↑` / `↓` / `→`) is returned as a literal string by the composer and rendered inline before the description.

## Related

- [`<x-card>`](./card.md) — wrap a stat or stats group in a card for additional structure.
- [`<x-badge>`](./badge.md) — alternative for very small metric pills.
- [`<x-timeline>`](./timeline.md) — for ordered event lists rather than point-in-time values.

## Notes

- The `desc` line displays the trend arrow only when `trend` is set (not just `trendValue`). To show a value without arrow, leave `trend` null and put the value in `desc`.
- `valueColor` tints both `stat-value` and the figure slot (so a "danger" stat gets a red icon to match).
- Per `docs/daisyui/pages/stat.md`, the `stats` container handles per-stat dividers when more than one `.stat` is inside — that's the reason for `:wrapped="false"` mode.
- Setting both `desc` and `trendValue` renders them on the same line with a space between, and the `trendValue` bolded via `font-medium`.
