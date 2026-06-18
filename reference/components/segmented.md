# x-segmented

A minimal **segmented control** (iOS-style sliding pill track) — a compact, mutually-exclusive choice between 2–4 options, each an icon and/or label. Pure inline Alpine; no `ui:install`. Good for view toggles (PC / mobile), density switches, tab-like mode pickers.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `options` | `array` | `[]` | `[{ value, label?, icon? }, …]`. `icon` is a raw inline-SVG string (sized to 1rem). At least one of `label`/`icon`. |
| `value` | `mixed` | first option | The initially-selected value. |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Track height + text scale (field tokens). |
| `ariaLabel` | `string \| null` | `null` | `aria-label` for the `role="tablist"` track. |

`wire:model` is forwarded to a hidden `<input>` (the selected value). All other attributes pass through to the root.

## Value contract

On change the control sets `current`, writes the value to the hidden `wire:model` carrier, **and** dispatches a bubbling `segmented-change` CustomEvent `{ value }` — so a non-Livewire host can react with `x-on:segmented-change`.

## Examples

### Livewire

```blade
<x-segmented wire:model.live="view" :options="[
    ['value' => 'list', 'label' => 'リスト'],
    ['value' => 'grid', 'label' => 'グリッド'],
]" />
```

### Alpine host (event)

```blade
<div x-data="{ mobile: false }">
    <x-segmented
        x-on:segmented-change="mobile = ($event.detail.value === 'mobile')"
        :options="[
            ['value' => 'pc', 'label' => 'PC', 'icon' => '<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><rect x=\'2\' y=\'3\' width=\'20\' height=\'14\' rx=\'2\'/><path d=\'M8 21h8M12 17v4\'/></svg>'],
            ['value' => 'mobile', 'label' => 'スマホ'],
        ]"
    />
</div>
```

## Class composition

Fully utility-composed (no Composer): a soft `bg-base-200/70` track with tune-token radius; the active segment is `bg-base-100` + `shadow-sm`, inactive `text-base-content/55`. Tracks data-theme × data-tune. Never daisyUI component classes.

## Related

- [`<x-button-group>`](./button-group.md) — joined buttons that *act* (each fires); segmented *selects* one of N.
- [`<x-tabs>`](./tabs.md) — for switching larger panels of content.
