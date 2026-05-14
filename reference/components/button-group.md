# x-button-group

Joined row (or column) of buttons that share borders — toolbars, segmented controls, paired prev/next actions. Wraps children in a daisyUI `.join` container. Children should opt in to the joined visual with `class="join-item"`.

**Playground page**: [`pinion-ui-playground/resources/views/pages/button-group.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/button-group.blade.php) — full variant matrix and live demos.

## When to use

- Grouping related actions that read as one control — toolbar, segmented selector, paginator-style prev/next.
- Stacking icon-only `<x-button>`s into a compact vertical control rail (`orientation="vertical"`).
- For a single primary action, just use `<x-button>`; for navigation with separator slashes, use `<x-breadcrumb>`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `orientation` | `'horizontal' \| 'vertical'` | `'horizontal'` | Layout direction. `horizontal` is daisyUI's default for `.join` and emits no extra class; `vertical` adds `join-vertical`. |

All other attributes pass through to the wrapping `<div>` (e.g. `class`, `x-data`, `role="group"`).

## Slots

- **default** — the buttons (or other join-compatible children). Each child needs `class="join-item"` to pick up the shared-border treatment.

## Examples

### Basic horizontal toolbar

```blade
<x-button-group>
    <x-button class="join-item" appearance="outline" color="neutral">Left</x-button>
    <x-button class="join-item" appearance="outline" color="neutral">Center</x-button>
    <x-button class="join-item" appearance="outline" color="neutral">Right</x-button>
</x-button-group>
```

### Vertical stack

```blade
<x-button-group orientation="vertical">
    <x-button class="join-item" appearance="outline" color="neutral">Top</x-button>
    <x-button class="join-item" appearance="outline" color="neutral">Middle</x-button>
    <x-button class="join-item" appearance="outline" color="neutral">Bottom</x-button>
</x-button-group>
```

### Segmented control (Alpine-driven active state)

```blade
<div x-data="{ active: 'center' }">
    <x-button-group>
        <x-button class="join-item" appearance="outline" color="neutral"
                  :class="active === 'left'   && '!bg-primary !text-primary-content !border-primary'"
                  @click="active = 'left'">Left</x-button>
        <x-button class="join-item" appearance="outline" color="neutral"
                  :class="active === 'center' && '!bg-primary !text-primary-content !border-primary'"
                  @click="active = 'center'">Center</x-button>
        <x-button class="join-item" appearance="outline" color="neutral"
                  :class="active === 'right'  && '!bg-primary !text-primary-content !border-primary'"
                  @click="active = 'right'">Right</x-button>
    </x-button-group>
</div>
```

## Class composition

Class strings live in [`src/Compose/ButtonGroupComposer.php`](../../src/Compose/ButtonGroupComposer.php) and the blade renders only the resulting dict. Keys:

- `root` — `join` (+ `join-vertical` when `orientation="vertical"`).

## Related

- [`<x-button>`](./button.md) — the natural child component; pair with `class="join-item"`.
- [`<x-tabs>`](./tabs.md) — for tabbed content rather than action grouping.
- [`<x-pagination>`](./pagination.md) — already uses a button-group internally for page numbers.

## Notes

- `join-item` is required on each child to flatten adjacent borders. Without it children render as standalone buttons with full borders.
- daisyUI's `.join` defaults to horizontal layout, so `orientation="horizontal"` emits no class. Only `vertical` adds the modifier.
- Icon-only join items typically need `class="join-item w-10 h-10 !p-0"` to keep square cells; `aria-label` is recommended on each.
