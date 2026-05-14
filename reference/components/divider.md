# x-divider

Horizontal or vertical separator with optional label. Built on daisyUI's `divider` utility, with this wrapper normalizing daisyUI's confusing class names: `direction="vertical"` produces a vertical line (daisyUI's own `divider-horizontal` class, which despite its name renders vertically inside a flex row).

**Playground page**: [`pinion-ui-playground/resources/views/pages/divider.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/divider.blade.php) — full variant matrix and live demos.

## When to use

- Visually separating stacked sections (no label, `direction="horizontal"`).
- Splitting two halves of a flex row (`direction="vertical"` inside `<div class="flex">`).
- Labeling the boundary ("OR", "AND", section names) via the default slot.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `direction` | `'horizontal' \| 'vertical'` | `'horizontal'` | Line orientation. `horizontal` = line stacked between blocks (no modifier class — daisyUI default). `vertical` = line inside a flex row (emits daisyUI's `divider-horizontal`, which actually renders vertically — see Notes). |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error' \| null` | `null` | Adds `divider-{color}` to tint the line and the label text. |
| `position` | `'start' \| 'center' \| 'end'` | `'center'` | Alignment of the label along the line. `start` and `end` push the label toward one side; `center` is the daisyUI default and emits no class. |

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

See [`src/Compose/DividerComposer.php`](../../src/Compose/DividerComposer.php). Returns a single `root` class string composed of `divider` + `divider-horizontal` (only when `direction="vertical"`) + optional `divider-{color}` + optional `divider-start` / `divider-end`.

## Related

- [`<x-card>`](./card.md) — uses its own internal header/footer dividers; do not nest `<x-divider>` inside a divided card.
- [`<x-collapse>`](./collapse.md) — visual section split that also toggles.

## Notes

- **daisyUI naming gotcha (normalized here):** daisyUI's CSS class `divider-horizontal` renders a *vertical* bar (a divider that sits horizontally laid out inside a flex *row*). The default `divider` (no modifier) renders a *horizontal* line that stacks blocks vertically. This wrapper inverts the prop naming so `direction="vertical"` does what you expect, and `direction="horizontal"` is the default no-modifier case. Source: `docs/daisyui/pages/divider.md` + composer comment.
- `direction="vertical"` only makes sense inside a parent with `display: flex` (or grid) along the row axis — otherwise the line has nothing to span.
- `position="center"` is the daisyUI default and emits no class — passing it is a no-op (safe but redundant).
