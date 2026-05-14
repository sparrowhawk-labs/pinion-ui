# x-button-group

Joined row (or column) of buttons that share borders — toolbars, segmented controls, paired prev/next actions. The wrapper handles three things for you (since v0.3.3): zeroes the middle children's border-radius (only the first/last keep rounded ends), collapses the inner border between adjacent children so you never see a double rule, and softens hover to a `bg-base-200` tint so a tight row of buttons doesn't read as a saturated wall on rollover.

**Playground page**: [`pinion-ui-playground/resources/views/pages/button-group.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/button-group.blade.php) — full variant matrix and live demos.

## When to use

- Grouping related actions that read as one control — toolbar, segmented selector, paginator-style prev/next.
- Stacking icon-only `<x-button>`s into a compact vertical control rail (`orientation="vertical"`).
- For a single primary action, just use `<x-button>`; for navigation with separator slashes, use `<x-breadcrumb>`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `orientation` | `'horizontal' \| 'vertical'` | `'horizontal'` | Layout direction. `horizontal` emits `inline-flex`; `vertical` emits `inline-flex flex-col`. Children get their inner border (right for horizontal, bottom for vertical) collapsed automatically. |

All other attributes pass through to the wrapping `<div>` (e.g. `class`, `x-data`, `role="group"`).

## Slots

- **default** — the buttons (or any joinable children — `<x-button>`, `<button>`, `<a class="btn">`, etc.). **No `class="join-item"` needed since v0.3.3** — the wrapper applies all join-style overrides via Tailwind arbitrary descendant variants.

## Examples

### Basic horizontal toolbar

```blade
<x-button-group>
    <x-button appearance="outline" color="neutral">Left</x-button>
    <x-button appearance="outline" color="neutral">Center</x-button>
    <x-button appearance="outline" color="neutral">Right</x-button>
</x-button-group>
```

### Vertical stack

```blade
<x-button-group orientation="vertical">
    <x-button appearance="outline" color="neutral">Top</x-button>
    <x-button appearance="outline" color="neutral">Middle</x-button>
    <x-button appearance="outline" color="neutral">Bottom</x-button>
</x-button-group>
```

### Segmented control (Alpine-driven active state)

The wrapper softens hover. To keep the **active** button fully saturated, use `!important` Tailwind utilities so the active state outranks the wrapper's hover override.

```blade
<div x-data="{ active: 'center' }">
    <x-button-group>
        <x-button appearance="outline" color="neutral"
                  :class="active === 'left'   && '!bg-primary !text-primary-content !border-primary'"
                  @click="active = 'left'">Left</x-button>
        <x-button appearance="outline" color="neutral"
                  :class="active === 'center' && '!bg-primary !text-primary-content !border-primary'"
                  @click="active = 'center'">Center</x-button>
        <x-button appearance="outline" color="neutral"
                  :class="active === 'right'  && '!bg-primary !text-primary-content !border-primary'"
                  @click="active = 'right'">Right</x-button>
    </x-button-group>
</div>
```

### Icon-only toolbar

```blade
<x-button-group>
    <x-button class="w-10 h-10 !p-0" appearance="outline" color="neutral" aria-label="bold">
        <x-i type="text-bold" variant="linear" class="w-4 h-4" />
    </x-button>
    <x-button class="w-10 h-10 !p-0" appearance="outline" color="neutral" aria-label="italic">
        <x-i type="text-italic" variant="linear" class="w-4 h-4" />
    </x-button>
</x-button-group>
```

## Class composition

See [`src/Compose/ButtonGroupComposer.php`](../../src/Compose/ButtonGroupComposer.php). Returns `root` only — a long class string composed of:

- **Layout**: `inline-flex` (+ `flex-col` for vertical).
- **Radii reset + restore**: `[&>*]:rounded-none [&>*:first-child]:rounded-l-[var(--radius-field)] [&>*:last-child]:rounded-r-[var(--radius-field)]` (top/bottom for vertical).
- **Inner-border collapse**: `[&>*:not(:last-child)]:border-r-0` (bottom for vertical).
- **Soft hover**: `[&>*]:hover:bg-base-200 [&>*]:hover:text-base-content [&>*]:hover:border-base-300`.

This replaces the pre-v0.3.3 reliance on daisyUI's `.join` / `.join-item` system, which lost to Tailwind utility specificity in practice (Tailwind's `rounded-[var(--radius-field)]` on each child outranked daisyUI's `:where(...)` selectors). The new approach is self-contained and works regardless of child component.

## Related

- [`<x-button>`](./button.md) — the natural child component.
- [`<x-tabs>`](./tabs.md) — for tabbed content rather than action grouping.
- [`<x-pagination>`](./pagination.md) — uses a similar joined-button visual internally.

## Notes

- **v0.3.3 BC note**: `class="join-item"` on children is no longer needed. Leaving it in place is harmless (the class becomes inert). Drop it when you next touch the markup.
- **v0.3.3 BC note**: hover styling is now overridden by the wrapper (soft tint). If you rely on a child's own appearance hover (e.g. `outline-neutral`'s full-invert), use `!important` in your override class or move that button out of `<x-button-group>`.
- Icon-only group items typically need `class="w-10 h-10 !p-0"` to keep square cells; `aria-label` is recommended on each.
- The `active`-state pattern requires `!important` (`!bg-primary !text-primary-content !border-primary`) so the active style outranks the wrapper's hover override. Without `!`, the hover tint covers the active highlight.
