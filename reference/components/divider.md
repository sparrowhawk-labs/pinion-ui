# x-divider

Horizontal or vertical separator with optional label. Built from plain Tailwind utilities (no daisyUI `divider`/`divider-*` classes — see CLAUDE.md invariant 6): a flex container split into two line segments with the label rendered between them (line - label - line). `direction="vertical"` produces a vertical line inside a flex row; this prop naming was originally chosen to normalize daisyUI's confusing `divider-horizontal` (which despite its name rendered a *vertical* bar) and is kept for backwards compatibility even though the implementation no longer touches daisyUI CSS.

**Playground page**: [`pinion-ui-playground/resources/views/pages/divider.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/divider.blade.php) — full variant matrix and live demos.

## When to use

- Visually separating stacked sections (no label, `direction="horizontal"`).
- Splitting two halves of a flex row (`direction="vertical"` inside `<div class="flex">`).
- Labeling the boundary ("OR", "AND", section names) via the default slot.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `direction` | `'horizontal' \| 'vertical'` | `'horizontal'` | Line orientation. `horizontal` = `border-t` line stacked between blocks (root is a flex row). `vertical` = `border-l` line inside a flex row (root is a flex column filling the parent's height — see Notes). |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error' \| null` | `null` | Tints the line (`border-{color}/30`) and the label text (`text-{color}`). `null` uses `border-base-content/10` / `text-base-content/60`. |
| `position` | `'start' \| 'center' \| 'end'` | `'center'` | Alignment of the label along the line. `start`/`end` shrink the line segment nearest that edge to a fixed `w-4`/`h-4` (was daisyUI's `divider-start`/`divider-end`) so the other segment grows to fill the remaining space; `center` gives both segments equal `flex-1`. |

All other attributes pass through to the root `<div>`.

## Slots

- **default** — optional label content shown inline with the line. Omit for a plain rule.

## Examples

### Basic horizontal

```blade
<x-divider />
```

### Labeled

```blade
<x-divider>OR</x-divider>
<x-divider color="primary">Section break</x-divider>
```

### Position variants

```blade
<x-divider position="start">Start</x-divider>
<x-divider position="end">End</x-divider>
```

### Vertical (inside a flex row)

```blade
<div class="flex h-24">
    <div class="grid place-items-center flex-1">A</div>
    <x-divider direction="vertical">OR</x-divider>
    <div class="grid place-items-center flex-1">B</div>
</div>
```

## Class composition

See [`src/Compose/DividerComposer.php`](../../src/Compose/DividerComposer.php). Returns four keys consumed by the Blade view: `root` (flex container), `lineStart` / `lineEnd` (the two border segments either side of the label — sizes/colors vary with `position`/`color`/`direction`), and `label` (only rendered when the slot is non-empty).

## Related

- [`<x-card>`](./card.md) — uses its own internal header/footer dividers; do not nest `<x-divider>` inside a divided card.
- [`<x-collapse>`](./collapse.md) — visual section split that also toggles.

## Notes

- **No daisyUI classes**: per CLAUDE.md invariant 6, this component never emits `divider`/`divider-*`. It's plain Tailwind (`flex`, `border-t`/`border-l`, `flex-1`/`flex-none`) — see `src/Compose/DividerComposer.php`.
- **`direction` prop naming (historical)**: the original daisyUI-backed implementation inverted `direction="vertical"` to work around daisyUI's own `divider-horizontal` class rendering a *vertical* bar (see `docs/daisyui/pages/daisyui-5-components__2.md`, `divider` section — `direction: divider-vertical, divider-horizontal`). The prop naming is unchanged for backwards compatibility even though the plain-Tailwind implementation no longer touches that class.
- `direction="vertical"` only makes sense inside a parent with `display: flex` (or grid) along the row axis, and the divider itself needs a height to span (`root` is `flex flex-col items-center h-full`) — otherwise the line has nothing to span.
- `position="center"` gives both line segments equal `flex-1` — passing it is a no-op vs. omitting the prop (safe but redundant).
