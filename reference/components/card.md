# x-card

Surface container for grouped content. Renders `<div>` by default with optional `header` and `footer` slots that are visually separated by either a divider line (`divider=true`, the default) or section spacing (`divider=false`). Eleven appearance variants pair with eight semantic colors to drive bg/border/text tone.

**Playground page**: [`pinion-ui-playground/resources/views/pages/card.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/card.blade.php) — full variant matrix and live demos.

## When to use

- Grouping a logical chunk of UI — a form, a stat block, a list of actions — into a clear surface.
- Adding a colored top accent bar (`appearance="bordered-top"` + a `color`) to mark category / status.
- For overlay dialogs use `<x-modal>`; for collapsible sections use `<x-collapse>` or `<x-accordion>`.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'neutral' \| 'info' \| 'success' \| 'warning' \| 'error'` | `'neutral'` | Semantic color. Ignored for `default`, `elevated`, `filled`, `ghost` (those use base surface only). Drives accent for `outline`, `soft`, `solid`, `base-*`, `bordered-top`. |
| `appearance` | `'default' \| 'elevated' \| 'filled' \| 'ghost' \| 'outline' \| 'soft' \| 'solid' \| 'base-100' \| 'base-200' \| 'base-300' \| 'bordered-top'` | `'default'` | Visual style. `default` = base-100 surface + subtle border; `elevated` = shadow, no border; `filled` = base-200 tint; `ghost` = transparent; `outline` = colored border; `soft` = tinted bg + colored text; `solid` = colored fill; `base-*` = matching surface bg + colored text; `bordered-top` = base-100 surface with a 4px colored accent on top. |
| `padding` | `bool` | `true` | Toggles inner padding (uses the `p-lg` token). Set `false` for media-flush cards. |
| `divider` | `bool` | `true` | If `true`, header/footer get a 1px line between them and the slot. If `false`, sections flow with text-gap spacing. |
| `hoverable` | `bool` | `false` | Adds `transition-shadow hover:shadow-lg cursor-pointer` — pair with a wrapping `<a>` for clickable cards. |
| `as` | `string` | `'div'` | HTML tag for the root element. Use `as="article"` / `as="section"` for semantic landmark wrapping. |

All other attributes pass through to the root element.

## Slots

- **default** — main body content.
- **header** *(named)* — title row / actions above the body. Rendered only if defined.
- **footer** *(named)* — actions / metadata below the body. Rendered only if defined.

## Examples

### Basic

```blade
<x-card>
    <p>A plain surface card with default border and padding.</p>
</x-card>
```

### With header and footer

```blade
<x-card>
    <x-slot:header>
        <h3 class="font-semibold">Plan summary</h3>
    </x-slot:header>

    <p>You're on the Pro plan. Renews monthly.</p>

    <x-slot:footer>
        <x-button size="sm">Manage</x-button>
    </x-slot:footer>
</x-card>
```

### Colored accent bar

```blade
<x-card appearance="bordered-top" color="success">
    <p>Operation completed.</p>
</x-card>
```

### Hoverable, no divider

```blade
<x-card hoverable :divider="false" appearance="elevated">
    <h4 class="font-semibold mb-1">Featured article</h4>
    <p class="text-sm text-base-content/70">Hover to lift.</p>
</x-card>
```

### Media-flush (no padding)

```blade
<x-card :padding="false" appearance="elevated">
    <img src="/cover.jpg" alt="" class="w-full h-40 object-cover">
    <div class="p-lg">
        <p>Body padded manually because the image bleeds edge-to-edge.</p>
    </div>
</x-card>
```

## Class composition

Card composes classes **inline** in [`src/resources/views/components/card.blade.php`](../../src/resources/views/components/card.blade.php) — it predates the Composer pattern used by form components. Class strings are stable across patch versions. Override with `class="..."` (Tailwind classes merge naturally via the attribute bag).

## Related

- [`<x-modal>`](./modal.md) — dialog variant of a card with backdrop.
- [`<x-collapse>`](./collapse.md) — card-like surface that toggles open/closed.
- [`<x-stat>`](./stat.md) — preformatted single-figure block; wrap several in a card for a dashboard panel.

## Notes

- `default`, `elevated`, `filled`, `ghost` ignore the `color` prop — these are surface-only variants. To accent a neutral card, prefer `bordered-top` + `color`.
- When `divider=false`, the outer wrapper takes the `p-lg` padding; when `divider=true`, each of header/body/footer carries its own `p-lg` so the dividing line spans full width.
- `bordered-top` always lays its accent on `bg-base-100` even if the parent uses a different surface — use `appearance="base-200"` instead if you want both surface tinting AND a colored text accent without the top stripe.
