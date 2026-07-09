# x-stat-group

Joins multiple `<x-stat :wrapped="false">` blocks into one bordered/shadowed card with dividers between items — the replacement for hand-writing daisyUI's `stats`/`stats-vertical`/`stats-horizontal` container, which computed a 0px divider border in some theme×tune combinations.

**Playground page**: [`pinion-ui-playground/resources/views/pages/stat.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/stat.blade.php) — full variant matrix and live demos.

## When to use

- Grouping 2+ related KPIs into one visual card (dashboard summary row).
- Anywhere you'd have reached for daisyUI's `stats` container — this is its drop-in replacement.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `direction` | `'horizontal' \| 'vertical' \| 'responsive'` | `'horizontal'` | `horizontal` = row with vertical dividers. `vertical` = column with horizontal dividers. `responsive` = column on mobile, row at `lg:`. Unknown values fall back to `horizontal`. |
| `shadow` | `bool` | `true` | Adds the tune's box shadow. |

All other attributes pass through to the root element.

## Slots

- **default** — one or more `<x-stat :wrapped="false">` children.

## Examples

```blade
<x-stat-group>
    <x-stat :wrapped="false" label="Revenue" value="$12k" />
    <x-stat :wrapped="false" label="Orders" value="248" />
    <x-stat :wrapped="false" label="Refunds" value="3" valueColor="error" />
</x-stat-group>
```

```blade
<x-stat-group direction="responsive" :shadow="false">
    <x-stat :wrapped="false" label="Revenue" value="$12k" />
    <x-stat :wrapped="false" label="Orders" value="248" />
</x-stat-group>
```

## Class composition

See [`src/Compose/StatGroupComposer.php`](../../src/Compose/StatGroupComposer.php). The `compose()` dict returns one key, `root`: card chrome (`rounded-[var(--radius-box)] tune-border border-base-content/10 bg-base-100 overflow-hidden`, plus shadow when enabled) + a plain Tailwind flex layout with `divide-x`/`divide-y` for the dividers between children — no daisyUI structural classes (see [CLAUDE.md invariant 6](../../CLAUDE.md)).

## Related

- [`<x-stat>`](./stat.md) — the individual stat block; pass `:wrapped="false"` when nesting inside this component.
- [`<x-card>`](./card.md) — general-purpose surface container, if you need more than a divided stat row.

## Notes

- Each child `<x-stat>` must set `:wrapped="false"` — otherwise you'll get nested card chrome (double border/shadow).
- `direction="responsive"` switches both the flex direction and the divider axis at the `lg:` breakpoint, so the divider always sits perpendicular to the layout direction.
