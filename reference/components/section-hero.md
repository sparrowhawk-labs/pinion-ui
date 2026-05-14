# x-pn::section.hero

Top-of-page hero section with `centered` and `split` layouts, title/subtitle, optional badge, and primary/secondary action buttons. Renders a `<section>` with `space-section` vertical rhythm and a max-width container; respects the active `data-tune` for spacing and radii.

**Playground page**: no dedicated demo page — see the [overview](https://github.com/sparrowhawk-labs/pinion-ui-playground/blob/main/resources/views/pages/overview.blade.php) and the [`README` quick-start example](../../README.md#quick-start).

## When to use

- Marketing / landing pages where the first viewport needs a headline, supporting copy, and 1–2 calls-to-action.
- App empty states or dashboard intros that benefit from the same `space-section` rhythm as marketing pages.
- For a plain content header inside an app, use a `<h1>` + plain prose instead — this component is opinionated about vertical space.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `variant` | `'centered' \| 'split'` | `'centered'` | Layout. `centered` stacks content in a centered column. `split` is a two-column grid (text + media) that collapses to one column under `lg`. |
| `title` | `string \| null` | `null` | Heading text. Rendered as `<h1>` with `titleSize` classes. Ignored when the `heading` slot is provided. |
| `subtitle` | `string \| null` | `null` | Supporting paragraph below the title. |
| `badge` | `string \| null` | `null` | Small primary-tinted pill rendered above the title (e.g. "New", "Beta"). |
| `size` | `'md' \| 'lg' \| 'xl'` | `'lg'` | Title size scale. `md` → `text-3xl→4xl`, `lg` → `text-4xl→6xl`, `xl` → `text-5xl→7xl`. |
| `image` | `string \| null` | `null` | Image URL for the `split` variant's media column. Ignored when the `media` slot is provided. |
| `imageAlt` | `string` | `''` | `alt` text for `image`. |
| `primaryAction` | `array \| null` | `null` | Shape: `['label' => string, 'href' => ?string]`. Rendered as `<x-pn::button variant="primary" size="lg">`. |
| `secondaryAction` | `array \| null` | `null` | Same shape as `primaryAction`; rendered as `variant="ghost"`. |
| `bgClass` | `string` | `'bg-base-100'` | Background utility for the outer `<section>`. Pass any Tailwind / daisyUI class (e.g. `bg-gradient-to-br from-primary/10`). |

All other attributes pass through to the root `<section>` (merged with `bgClass` and `space-section`).

## Slots

- **default** — extra content placed below the actions row (e.g. trust badges, an inline form).
- **heading** — full custom markup for the heading area; overrides `title`. Use this when you need rich heading markup (multi-line, accent spans, etc.).
- **actions** — full custom markup for the actions row; overrides `primaryAction` / `secondaryAction`. Useful when you need three buttons, an action with an icon, or a non-button affordance.
- **media** — (split only) full custom markup for the media column; overrides `image`. Use for video, illustrations, or a card-stack composition.

## Examples

### Centered with two actions

```blade
<x-pn::section.hero
    variant="centered"
    badge="New"
    title="Build faster with Pinion"
    subtitle="A Blade UI library for Laravel built on Tailwind v4, daisyUI v5, and Alpine.js."
    :primaryAction="['label' => 'Get started', 'href' => '/docs']"
    :secondaryAction="['label' => 'View on GitHub', 'href' => 'https://github.com/sparrowhawk-labs/pinion-ui']" />
```

### Split with image

```blade
<x-pn::section.hero
    variant="split"
    title="Design that ships with you"
    subtitle="Themes, tunes, and components — wired together once."
    image="/img/hero.png"
    imageAlt="Pinion UI components in a dark theme"
    :primaryAction="['label' => 'Try the playground', 'href' => '/playground']" />
```

### Custom heading + actions slots

```blade
<x-pn::section.hero variant="centered" size="xl">
    <x-slot:heading>
        <h1 class="text-5xl md:text-7xl font-bold tracking-tight">
            Ship UI. <span class="text-primary">Not opinions.</span>
        </h1>
    </x-slot:heading>

    <x-slot:actions>
        <x-pn::button variant="primary" size="lg" icon="rocket">Launch</x-pn::button>
        <x-pn::button variant="outline" size="lg">Watch demo</x-pn::button>
        <x-pn::button variant="ghost" size="lg" href="/pricing">Pricing</x-pn::button>
    </x-slot:actions>
</x-pn::section.hero>
```

## Class composition

Hero composes classes **inline** in [`src/resources/views/components/section/hero.blade.php`](../../src/resources/views/components/section/hero.blade.php) — sections are layout-heavy and not a good fit for the Composer pattern used by form components. Override by passing `class="..."` (merged into the `<section>` root) or by replacing the heading/media columns via slots.

## Related

- [`<x-button>`](./button.md) — used internally for `primaryAction` / `secondaryAction`.
- [`<x-card>`](./card.md) — a common companion below the hero for feature grids.
- [`<x-pn::tune-styles>`](./tune-styles.md) — controls `--space-section` / `--space-section-inner`, which drive the hero's vertical rhythm.

## Notes

- The component is namespaced under the `section.` directory, so the fully-qualified tag is `<x-pn::section.hero>`. The plain `<x-section.hero>` form also works once `pinion-ui` is installed.
- Vertical rhythm uses Tune CSS variables (`space-section`, `space-section-inner`, `gap-element`, `gap-compact`). Switching `data-tune` automatically reflows the hero — no prop changes needed.
- When both `image` and the `media` slot are set, the slot wins. Same rule for `title` ↔ `heading` slot, and `primaryAction`/`secondaryAction` ↔ `actions` slot.
- The badge pill uses `bg-primary/10 text-primary` with the active tune's `--radius-selector`.
