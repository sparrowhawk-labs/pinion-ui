# x-collapse

Single disclosure region built on daisyUI's `collapse` utility. Uses a hidden `<input type="checkbox">` for no-JS toggle support — open/close works without Alpine. Minimal by default (no affordance icon); opt in to a chevron (`arrow`) or plus/minus (`plus`) when needed. Supports an optional border.

**Playground page**: [`pinion-ui-playground/resources/views/pages/collapse.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/collapse.blade.php) — full variant matrix and live demos.

## When to use

- One-off disclosure regions (privacy disclosure, optional settings).
- Inside lists where you compose your own open-state coordination.
- For a coordinated group with single-open behavior use `<x-accordion>` instead.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | `string \| null` | `null` | Text shown in the always-visible header. Ignored if the `titleSlot` is set. |
| `open` | `bool` | `false` | Initial open state — sets the `checked` attribute on the inner checkbox. |
| `icon` | `'arrow' \| 'plus' \| null` | `null` | Affordance shown on the header. `null` (default) → no icon; `'arrow'` → `collapse-arrow` (rotating chevron); `'plus'` → `collapse-plus` (+/− toggle). |
| `bordered` | `bool` | `true` | Adds `border border-base-300` to the root. Set `false` for a borderless surface (e.g. inside an already-bordered card). |

All other attributes pass through to the root `<div>`.

## Slots

- **default** — collapsed body content.
- **titleSlot** *(named)* — replaces the `title` prop with rich markup (icons, badges, etc.) in the header.

## Examples

### Basic

```blade
<x-collapse title="Show details">
    <p>Hidden body revealed on click.</p>
</x-collapse>
```

### Open by default

```blade
<x-collapse title="Advanced options" open>
    <p>This panel starts expanded.</p>
</x-collapse>
```

### With chevron affordance

```blade
<x-collapse title="Show details" icon="arrow">
    <p>Header gets a rotating chevron on the right.</p>
</x-collapse>
```

### Plus/minus affordance, borderless

```blade
<x-collapse title="FAQ — refund policy" icon="plus" :bordered="false">
    <p>Refunds processed within 7 days.</p>
</x-collapse>
```

### Rich header slot

```blade
<x-collapse>
    <x-slot:titleSlot>
        <span class="flex items-center gap-2">
            <x-badge color="warning" size="xs">Beta</x-badge>
            <span>Experimental settings</span>
        </span>
    </x-slot:titleSlot>

    <p>These options may change between releases.</p>
</x-collapse>
```

## Class composition

See [`src/Compose/CollapseComposer.php`](../../src/Compose/CollapseComposer.php). Returns `root`, `title`, `content`. The root composes `collapse` + `collapse-arrow` / `collapse-plus` + optional border + `bg-base-100`. Per `docs/daisyui/pages/collapse.md`, the daisyUI `collapse` utility relies on a sibling `<input>` for the toggle — that's why the markup includes a hidden checkbox even though it visually looks like a button.

## Related

- [`<x-accordion>`](./accordion.md) — coordinated group with single-open or multi-open behavior.
- [`<x-card>`](./card.md) — static surface variant with the same `bg-base-100` + border look but no toggle.

## Notes

- The toggle is checkbox-based, so it works without JavaScript. Form submission will include the checkbox state unless you add a `name=""` attribute (or pass `name=""` deliberately to capture it).
- The default (`icon=null`) emits no `collapse-arrow` / `collapse-plus` modifier — the surface still toggles open/closed; only the visual affordance is absent. Opt in to an icon only when the header alone doesn't communicate the affordance.
- Per CLAUDE.md's docs/daisyui grep rule: `collapse-arrow` and `collapse-plus` are mutually exclusive — only one is added, with `arrow` winning when both somehow get set.
