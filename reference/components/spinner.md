# x-spinner

Inline loading indicator. Six variants (`spinner` default + `dots` / `ring` / `bars` / `ball` / `infinity`), five sizes (`xs`–`xl`), and seven optional semantic colors.

**No daisyUI dependency (migrated 2026-07-09, CLAUDE.md invariant 6)**: this component no longer emits daisyUI's `loading`/`loading-*` classes. Instead it reproduces daisyUI's own underlying technique — `background-color: currentColor` masked by a data-URI SVG whose embedded SMIL `<animate>`/`<animateTransform>` tags drive the motion (no `@keyframes` at all) — verbatim, under pinion-ui's own class names (`spinner`, `spinner-{shape}`, `spinner-{size}`, defined in `src/resources/css/pinion-ui.css`). Because the mask-image SVGs are byte-identical to daisyUI's, all six shapes — including `ring` and `infinity`, the two hardest to fake with plain Tailwind keyframes — should render pixel-identical motion, not an approximation. This could not be confirmed in a live browser in this environment, so treat it as high-confidence-but-not-visually-verified.

**Playground page**: [`pinion-ui-playground/resources/views/pages/spinner.blade.php`](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/spinner.blade.php) — variant × size × color matrix and in-context examples.

## When to use

- Indicate an in-flight async action — button submitting, panel reloading, lazy section.
- `<x-button loading>` already embeds a spinner — use this component for standalone indicators (page, card, inline beside text).
- For unknown-duration progress bars use [`<x-progress>`](./progress.md) without `value`; for placeholder content use [`<x-skeleton>`](./skeleton.md).

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `variant` | `'spinner' \| 'dots' \| 'ring' \| 'bars' \| 'ball' \| 'infinity'` | `'spinner'` | Shape (`spinner-spinner` / `spinner-dots` / `spinner-ring` / `spinner-bars` / `spinner-ball` / `spinner-infinity`), each a mask-image SVG with SMIL animation. |
| `size` | `'xs' \| 'sm' \| 'md' \| 'lg' \| 'xl'` | `'md'` | Size modifier (`spinner-{size}`), same `--size-selector`-based widths as before. |
| `color` | `'primary' \| 'secondary' \| 'accent' \| 'info' \| 'success' \| 'warning' \| 'error' \| null` | `null` | Tints via `text-{color}` (the mask is painted with `currentColor`, same mechanism as before). `null` leaves it inheriting from the parent. |

All other attributes pass through to the root `<span>` (which carries `aria-label="Loading..."`).

## Slots

None — spinner is a self-contained indicator.

## Examples

### Basic

```blade
<x-spinner />
<x-spinner variant="dots" />
<x-spinner variant="ring" color="primary" />
```

### Inline with text

```blade
<p class="flex items-center gap-2 text-base-content/70">
    <x-spinner variant="dots" size="sm" /> Syncing…
</p>
```

### In a button (manual)

```blade
<x-button>
    <x-spinner size="sm" /> Saving…
</x-button>
```

For the common "button shows spinner while loading" case, prefer `<x-button loading>` — it handles icon hiding and pointer-events automatically.

### Size + color matrix

```blade
<x-spinner size="xs" />
<x-spinner size="sm" color="info" />
<x-spinner size="md" color="success" />
<x-spinner size="lg" color="warning" variant="bars" />
<x-spinner size="xl" color="error" variant="infinity" />
```

## Class composition

Class strings come from [`SparrowhawkLabs\PinionUi\Compose\SpinnerComposer`](../../src/Compose/SpinnerComposer.php) — `root` is the only key, combining `spinner` + variant (`spinner-{shape}`) + size (`spinner-{size}`) + optional color (`text-{color}`). The shape/size CSS itself lives in `src/resources/css/pinion-ui.css` under the "spinner (`<x-spinner>`) — no daisyUI dependency" section — no daisyUI `loading` component CSS is loaded for this component.

## Related

- [`<x-button>`](./button.md) — the `loading` prop embeds a spinner with proper icon swap and disabled semantics.
- [`<x-progress>`](./progress.md) — for measured progress or indeterminate stripes.
- [`<x-skeleton>`](./skeleton.md) — for placeholder content blocks.

## Notes

- The `spinner-*` shape classes color via `currentColor` (same as daisyUI's original `loading-*`), so `text-{color}` from `color` is enough — there's no `spinner-primary` etc.
- The root is a `<span>` so the spinner flows inline by default; wrap it in a `<div class="flex justify-center">` (or similar) to center it in a card.
- `aria-label="Loading..."` is fixed in English. Localized labels can be added via a pass-through `aria-label` on the component call.
