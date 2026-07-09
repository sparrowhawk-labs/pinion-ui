# x-stat

Single statistic block — a label / value / description triple with an optional figure (icon or image) and a trend arrow (↑ / ↓ / →). Wraps in its own card chrome by default; set `wrapped="false"` to drop the outer wrapper when grouping multiple stats with [`<x-stat-group>`](./stat-group.md).

**Playground page**: [`pinion-ui-playground/resources/views/pages/stat.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/stat.blade.php) — full variant matrix and live demos.

## When to use

- Dashboard KPI tiles (revenue, signups, conversion).
- Compact metric strips inside cards or section headers.
- Group multiple stats with [`<x-stat-group>`](./stat-group.md) — put several `<x-stat :wrapped="false">` inside it.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `label` | `string` | `''` | Top-line label. Muted by default. |
| `value` | `string \| int \| null` | `null` | The headline number / text. Hidden if null. |
| `desc` | `string \| null` | `null` | Secondary description line. Combined with `trend` arrow and `trendValue` when those are present. |
| `valueColor` | `'primary' \| 'secondary' \| 'accent' \| 'info' \| 'success' \| 'warning' \| 'error' \| null` | `null` | Tints the `value` text and the `figure` slot. Null = base-content. |
| `trend` | `'up' \| 'down' \| 'flat' \| null` | `null` | Arrow + desc color. `up` → ↑ + success; `down` → ↓ + error; `flat` → → + muted. |
| `trendValue` | `string \| null` | `null` | Bold-weight inline value appended to the description (e.g. `'+12%'`). |
| `wrapped` | `bool` | `true` | If `true`, wraps the block in its own card (rounded border + shadow). Set `false` to compose multiple stats into one [`<x-stat-group>`](./stat-group.md) container. |

All other attributes pass through to the root element.

## Slots

- **default** — figure content (icon, small image) rendered before the label. The presence of any non-whitespace content activates the figure block.

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

### Grouped

```blade
<x-stat-group>
    <x-stat :wrapped="false" label="Revenue" value="$12k" />
    <x-stat :wrapped="false" label="Orders" value="248" />
    <x-stat :wrapped="false" label="Refunds" value="3" valueColor="error" />
</x-stat-group>
```

See [`<x-stat-group>`](./stat-group.md) for `direction` / `shadow` options.

## Class composition

See [`src/Compose/StatComposer.php`](../../src/Compose/StatComposer.php). The `compose()` dict returns class strings only: `root` (card chrome or empty when `wrapped=false`), `inner` (flex row layout), `text` (label/value/desc column), `figure`, `title`, `value`, `desc`. The trend arrow character (`↑` / `↓` / `→`) is **not** in the dict — it lives in a separate `StatComposer::arrowChar($trend): string` static helper that the Blade view calls directly (since v0.3.1). This keeps the Compose-pattern invariant clean (class strings only — no markup, no literal text) per [CLAUDE.md](../../CLAUDE.md).

As of 2026-07-09, `<x-stat>` no longer uses daisyUI's `.stat`/`.stats`/`.stat-*` classes — root/inner/figure/title/value/desc are plain Tailwind + semantic color utilities (see [CLAUDE.md invariant 6](../../CLAUDE.md)).

## Related

- [`<x-stat-group>`](./stat-group.md) — joins multiple unwrapped stats into one bordered/divided card.
- [`<x-card>`](./card.md) — wrap a stat or stat group in a card for additional structure.
- [`<x-badge>`](./badge.md) — alternative for very small metric pills.
- [`<x-timeline>`](./timeline.md) — for ordered event lists rather than point-in-time values.

## Notes

- The `desc` line displays the trend arrow only when `trend` is set (not just `trendValue`). To show a value without arrow, leave `trend` null and put the value in `desc`.
- `valueColor` tints both `value` and the figure slot (so a "danger" stat gets a red icon to match).
- Setting both `desc` and `trendValue` renders them on the same line with a space between, and the `trendValue` bolded via `font-medium`.
