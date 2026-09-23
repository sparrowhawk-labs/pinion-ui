# x-backdrop

A **page background** behind everything — textures, still glow, and the animated `flow` (two theme-colored glows drifting on unrelated periods). Colors come from the theme only (`base-content` ink; `accent` / `secondary` for `flow`), so a backdrop follows `data-theme` and no hex is written. The SVG textures are used as **masks** for the same reason. `flow` stops under `prefers-reduced-motion`. Lifted from SendSignal, where each pattern was tried on a real page (v0.20.0).

Put it **once, right after `<body>`**. It is a real fixed element (`z-index:-1`, `pointer-events:none`) rather than `body::before`/`::after`, so pages never compete for the free pseudo-element.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `pattern` | `string \| null` | `pn_look()['backdrop']` | `<name>` or `<name>-<s\|m\|l>` (density; `m` when omitted). With no prop, the value comes from `config('pinion-ui.look.backdrop')`, overridable by `?bg=` within `look.backdrops`. `none` renders nothing. |
| `local` | `bool` | `false` | Position `absolute` inside a `relative` parent instead of the viewport (a hero, a card). |

## Patterns

| Name | What | Size s / m / l |
|------|------|------|
| `flow` | two drifting glows (accent, secondary) — **animated** | slow (40s/53s) / 20s/26s / fast (12s/16s) |
| `glow` | still, off-centre light in ink | faint / medium / strong |
| `grid` | line grid | 8 / 24 / 64px |
| `dot`, `ndot`, `ring` | dots, dot-matrix lattice, sparse rings | — |
| `cross`, `rule`, `diag` | grid crossings, horizontal rules, 45° hatch | — |
| `paper`, `chev`, `sig` | SVG textures: drafting paper, chevrons, signal blocks | small / medium / large |

The list is `pn_backdrops()`. `--pn-backdrop-base` sets the ground color (default `base-200`).

## Example

```blade
<body>
    <x-backdrop pattern="flow" />
    …
</body>

{{-- default from config, ?bg=grid-s to try another --}}
<x-backdrop />

{{-- inside a hero only --}}
<section class="relative isolate overflow-hidden">
    <x-backdrop pattern="sig-l" local />
    …
</section>
```
