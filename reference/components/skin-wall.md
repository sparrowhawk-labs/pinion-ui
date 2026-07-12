# `<x-skin-wall>`

A full-width **diagonal marquee** that renders the SAME slot markup across many `data-tune` × `data-theme` skins — a "wall of looks" to show off the theme × tune system. Decorative (`aria-hidden`), self-contained (ships its own scoped CSS via `@once`).

The slot is rendered once, then repeated across every card, each wrapped in its own `[data-tune][data-theme]` island — so one component tree becomes an endless scrolling grid of skins.

## Usage

```blade
<x-skin-wall :angle="-12" :speed="36" :columns="7" :height="560"
             :card-width="320" :card-height="480" :scale="0.3333">
    {{-- one tile's markup — authored at (card-width / scale) px wide --}}
    <div style="width:100%;">
        <h1>Balance is the default.</h1>
        <x-button color="primary" icon="rocket">Ship it</x-button>
        <x-badge color="success">Active</x-badge>
        {{-- … --}}
    </div>
</x-skin-wall>
```

Wrap it in a full-bleed container if you want it edge-to-edge:

```blade
<div style="position:relative; width:100vw; left:50%; transform:translateX(-50%);">
    <x-skin-wall> … </x-skin-wall>
</div>
```

## Props

| Prop | Default | Description |
|---|---|---|
| `combos` | curated 24-pair set | Array of `[tune, theme]` pairs cycled across the cards. |
| `angle` | `-12` | Diagonal tilt, in degrees. |
| `speed` | `36` | Base seconds for one loop; each column varies gently around it. |
| `columns` | `7` | Number of vertical columns. |
| `per` | `2` | Distinct combos per column (repeated ×3 internally for a seamless loop). |
| `height` | `560` | Stage height, in px. |
| `card-width` | `320` | Card width, in px. |
| `card-height` | `480` | Card height, in px. |
| `scale` | `0.3333` | Content scale. The slot is authored at `card-width / scale` px wide, then scaled down (a 1/3-scale miniature by default). |
| `gap` | `26` | Vertical space between stacked cards, in px. |
| `cover` | `1.14` | Wall scale so the rotated wall still covers the stage. |

## Notes

- **Seamless loop**: each column repeats its set 3× and scrolls by *exactly one set* (`--sw-set`), so the centred window never runs off the track ends — no visible spawn/vanish. Do not switch it to a `-50%` translate.
- **Decorative only**: the slot markup is repeated, so ids/names duplicate; the wall is `aria-hidden` and out of the tab order. Don't put interactive controls a user needs inside it.
- Honors `prefers-reduced-motion` (animation off).
- The tiles inherit their skin from the per-card `data-tune`/`data-theme`; author the slot with theme/tune-reactive tokens (semantic colors, `var(--radius-*)`, tune spacing utilities) so every skin reads correctly.
