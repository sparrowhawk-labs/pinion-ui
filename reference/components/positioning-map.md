# `<x-positioning-map>`

A generic **2-axis positioning / perceptual map**: plots labelled points on a plane defined by two axes. Use it for competitive positioning (price × quality), prioritisation (effort × impact), perceptual maps, or any quadrant scatter. One built-in consumer is the Tune distance map ("shape × voice"), but the component knows nothing about tunes — you pass the points.

Theme-agnostic (daisyUI semantic colours only) and tune-aware on the **frame** (radius + border read the active tune); point **coordinates are fixed data** and never move when the tune changes.

## Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `points` | array | `[]` | The data. Each item: `['x' => 0..100, 'y' => 0..100, 'label' => 'A', 'sub' => 'optional caption']`. `x`: 0 = left, 100 = right. `y`: 0 = top, 100 = bottom. **A point may also carry an `icon` or an `image` (see below)** — all of `label`/`icon`/`image` are optional. |

### Point marker — label, icon, or image

Each point renders whichever the datum provides, so icons and logos are one-line:

| Datum field | Renders |
|---|---|
| `label` (+ optional `sub`) | A small tune-aware text chip with an optional caption below (default). |
| `icon` (+ optional `iconLibrary`) | A leading `<x-i>` glyph (pinion-icons) inside the chip, before the label. e.g. `'icon' => 'rocket'`. |
| `image` (+ optional `imageShape`) | A framed thumbnail / logo. `'imageShape' => 'circle'` for round avatars/logos, else the tune's field radius. `label` becomes the caption below. |
| `xLabels` | array | `[]` | `[leftCaption, rightCaption]` — captions at the bottom corners. |
| `yLabels` | array | `[]` | `[topCaption, bottomCaption]` — vertical captions on the left edge. |
| `quadrants` | array | `[]` | Optional faint background labels `[topLeft, topRight, bottomLeft, bottomRight]`. |
| `active` | string\|null | `null` | Static highlight — the point whose `label` matches gets the active marker. |
| `xActive` | string\|null | `null` | **Live** highlight — an Alpine expression (e.g. `"tune"`) compared per-point via `x-bind`. Use inside an `x-data` scope to make the highlight follow state (e.g. a tune switcher). Overrides `active`. |
| `size` | `sm`\|`md`\|`lg` | `md` | Plot height (15 / 22 / 30 rem). |
| `grid` | bool | `true` | Show the 8×8 reference grid. |

No slots — the map is data-driven via `points`.

## Examples

```blade
{{-- Competitive positioning, static --}}
<x-positioning-map
    :points="[
        ['x' => 20, 'y' => 30, 'label' => 'Us', 'sub' => '$$'],
        ['x' => 70, 'y' => 65, 'label' => 'Rival A'],
        ['x' => 45, 'y' => 80, 'label' => 'Rival B'],
    ]"
    :x-labels="['budget', 'premium']"
    :y-labels="['simple', 'feature-rich']"
    active="Us"
/>
```

```blade
{{-- Live highlight that follows a switcher (Alpine) --}}
<div x-data="{ pick: 'soft' }">
    <x-positioning-map :points="$tunePoints" x-active="pick" :x-labels="['sharp','round']" :y-labels="['quiet','loud']" />
    <button x-on:click="pick = 'brutal'">brutal</button>
</div>
```

## Class-composition notes

- `PositioningMapComposer::compose()` returns class strings only (Compose invariant). Point geometry (`left%/top%`) is **not** a class — the Blade view writes it inline from each datum.
- `PositioningMapComposer::gridStyle()` is a non-class helper (like `StatComposer::arrowChar`) returning the grid's `background-image` inline style.
- Colours are `bg-base-100` / `text-base-content` / `border-base-content/15` and the active marker uses `bg-primary` — all daisyUI semantic tokens, so the map reads correctly under every theme. The frame uses `rounded-[var(--radius-box)]` + `tune-border`, so its shape follows the active tune.

## Using it in `/visualize`

The map is also available as a self-contained CSS recipe (no Blade runtime) for `/visualize` HTML reports — copy the `.positioning-map` / point markup from this component (or the recipe in the playground demo) and position dots with inline `left%/top%`. A dedicated ` ```positioning-map ` visualize block may follow.
