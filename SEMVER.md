# Versioning & Deprecation Policy

`sparrowhawk-labs/pinion-ui` follows [Semantic Versioning](https://semver.org/) with the conventions below. Tagged releases on GitHub (`v0.X.Y`) carry release notes that call out anything not in this document.

## 0.x — pre-1.0 (current)

While in `0.x`, the API surface is stable in spirit but **not yet under a 1.0 contract**:

- **Patch (`0.X.Y` → `0.X.Y+1`)** — bug fixes, doc updates, internal refactors, and additive props whose default preserves previous behaviour. Safe to upgrade.
- **Minor (`0.X.0` → `0.X+1.0`)** — new components, new props, default-behaviour changes that improve the common case. May break consumers who relied on a specific default value or class string. Read the release notes before upgrading.
- **Major (`0.x` → `1.0`)** — declared once the API is considered stable. After 1.0, breaking changes live in major versions only.

In `0.x`, we err toward **adding new opt-in props** rather than silently flipping defaults. When we do flip a default — e.g. `<x-checkbox appearance>` `'solid'` → `'soft'` in v0.2.0, `<x-collapse icon>` `'arrow'` → `null` in v0.2.1 — it is called out in the release notes and the previous behaviour remains opt-in via the original value.

We also reserve the right to **un-flip** a default if user testing shows the new default doesn't carry its weight. Example: `<x-indicator>` and `<x-timeline>` got a v0.3.0 `appearance="soft"` default, then reverted to `'solid'` in v0.3.4 — the soft tint was too quiet for the "this needs attention" cue that an indicator is supposed to be, and timeline's `done` chain lost its visual hierarchy. Both kept the `appearance` prop, so `appearance="soft"` is still a one-keyword opt-in.

## What counts as a BC break

- Renaming a prop (without an alias).
- Changing a prop's default in a way that produces different CSS classes or different markup at the call site.
- Removing a component, slot, prop, or appearance/color/size option.
- Changing the Compose-layer key names (`$c['root']`, `$c['title']`, etc.) that downstream templates may read.
- Renaming the namespace prefix (`<x-pn::xxx>`). The current rename from `pinion-ui::` to `pn::` happened in v0.2.1 — no further renames are planned.

## What does NOT count as a BC break

- Internal refactors that don't change the rendered output or the composer's returned dict.
- Adding new props with defaults that preserve previous behaviour.
- Adding new slots.
- Adding new appearance / color / size options.
- daisyUI class-name fixes that bring the output closer to upstream documented behaviour.
- Adding new Tune presets to the inline `$tunes` map.

## Deprecation lifecycle

1. **Introduced** — Marked in the changelog as new.
2. **Deprecated** — Documented in the relevant `reference/components/{name}.md` and in the release notes. Continues to work; consumers should migrate. (A `trigger_error(E_USER_DEPRECATED)` mechanism in composers is on the roadmap; not in place today.)
3. **Removed** — Only in a minor (during `0.x`) or major (after `1.0`) release, never in a patch. Deprecations stay in place for **at least one minor cycle** before removal.

## Reading the changelog

Each tagged release on GitHub has notes covering:

- Fixes (with file references)
- Docs (with link to updated `reference/components/` pages)
- BC notes when relevant — call-outs for defaults changes or removed APIs
- Tests / metrics (e.g. `244 pass / 0 fail`)

If you depend on this package in a production app, pin to a specific patch (`^0.2.3`) until 1.0; review the release notes before bumping the minor.

## Past version BC notes

A non-exhaustive audit trail of intentional breaking changes during `0.x`. Defaults flipped quietly (without a release-note callout) do not appear here; they don't exist.

### v0.8.2 — 2026-07-20

- **Switcher chrome no longer tracks the active tune** (design rule: site chrome that *changes* the tune must not be re-rendered by it — under `draft`/`pixel` the picker itself became handwriting/wobbly and hard to read). Visible deltas: pinned base font on all four switcher components, no draft rough-filter on their buttons, static radius/border/shadow instead of `--radius-field`/`--radius-box`/`tune-border`/`--shadow-box`, and the tune trigger no longer previews the active tune's typeface (per-option previews in the open dropdown are unchanged). Consumers who *want* tune-reactive picker chrome would need to fork the template. New public escape hatch: `.tune-exempt` in `tune.css`.

### v0.8.0 — 2026-07-20

- **`<x-theme-tune-switcher>` attribution repositioned + default link flipped to GitHub.** The always-visible corner badge (absolutely positioned below the switcher bar) is **removed**; attribution is now a single faint link pinned at each dropdown's **top-right**, outside the scrollable list (was: a footer row at the bottom of each list, reachable only after scrolling). The link's default target changed `https://pinion-ui.dev/` → **the GitHub repo**; a new `link` prop selects `'github'` (default) / `'site'` (pinion-ui.dev) / any URL. `:attribution="false"` still opts out entirely. Impact: layout that reserved space under the switcher for the badge can reclaim it; consumers wanting the old pinion-ui.dev target pass `link="site"`.
- **`<x-lang-switcher>` visual redesign** — the `<x-dropdown>`+`<x-menu-item>` chrome is replaced by the `<x-theme-tune-switcher>` control family (label + chip trigger + right-aligned dropdown of server-rendered links). Props are all still accepted: `locales` / `current` / `width` behave as before; `size` is accepted but inert (single size now); `position` honors only its vertical axis (`top*` opens upward). New `label` prop (default `'Lang'`, `''` hides). Markup/classes changed — anything styling the old internals by selector needs re-checking.

### v0.7.3 — 2026-07-20

- **Default flip: `pinion-ui.locale` now defaults to `null` = follow `app()->getLocale()`** (was hard `'ja'`). `pn_trans()` — the helper behind component-internal strings (pagination Previous/Next + info template + nav aria, select placeholder, rating aria-label, table-scroll button labels, notification close) — previously ignored the app's runtime locale entirely, so any non-Japanese or multi-locale app shipped Japanese strings unless it published the config. New lookup chain: explicit `pinion-ui.locale` (config/`PINION_UI_LOCALE`, still wins when set) → `app()->getLocale()` → `en` bucket → per-callsite fallback. Impact: apps with `app.locale` ≠ `ja` that relied on the implicit Japanese default now get their own locale's strings (or English) — which is the intended behavior; set `PINION_UI_LOCALE=ja` to restore the old output. Japanese-locale apps are unchanged. Bundled translation buckets grew: `ja` / `en` / **`zh-Hans` / `zh-Hant` (new)**.

### v0.7.0 — 2026-07-19

- **BREAKING: the `mood-` theme-name prefix is dropped** — aesthetic themes now use bare names like every other group. Rename map (append `-dark` for the dark pair): `mood-monokai` → `monokai`, `mood-synthwave` → **`outrun`** (new word, since bare `synthwave` would collide with daisyUI's stock theme name), `mood-vapor` → `vapor`, `mood-bigblue` → `bigblue`, `mood-neotokyo` → `neotokyo`, `mood-zen` → `zen`, `mood-botanical` → `botanical`, `mood-pop` → `pop`, `mood-verdigris` → `verdigris`. Old `mood-*` values in `data-theme` render unthemed — do the rename. Category (Brand / Mood / SaaS / Industry) is now carried by `lineup.json` metadata, `pn_theme_groups()`, and the grouped `<x-theme-tune-switcher>` (headings + per-category chip), not by the name.
- **All 35 non-frozen palettes re-tuned for individuality** ("theme distance" overhaul, user-approved per category): every light canvas gains a perceivable tint (consumer/mood themes boldly so); primary hues were re-assigned where crowded (`docs` → sepia ink, `factory` → hazard amber, `logistics` → navy×container-orange swap, `food` → citrus lime, `security` → graphite×alert red, `kids` → crayon sky/grass, `atelier` → espresso, `analytics` → plum, `civic` → vermillion accent, `estate` → blueprint canvas); shared support-color hexes were de-duplicated (28 cross-theme reuses → 0). `verdigris` palette untouched (preservation intent); `mono` unchanged. Nearest-pair OKLCH distance rose 1.32 → 1.80.
- **Brand `pinion` reverted to the Primer-derived palette** (light primary `#0969DA`, dark `#4493F8`, canvas `#F6F8FA`) — rolling back the interim "palette v2" cream/saturated variant — with one deliberate delta kept: **secondary is teal** (`#0F766E` / `#2DD4BF`) so it no longer near-duplicates the success green.

- **Brand default `pinion` palette replaced again — verdigris → the `reactive` family** (GitHub-Primer-adjacent "clean technical document": white canvas `#f6f8fa`/`#ffffff`, ink `#1f2328`, link-blue primary `#0969da`, green secondary `#1f883d`, purple accent `#8250df`; dark pair is Primer-Dark-adjacent `#0d1117`/`#161b22` with `#4493f8`/`#3fb950`/`#a371f7`). Same-day follow-up to v0.6.0 while nothing downstream had adopted the verdigris default; a deliberate brand call, not a regression fix. Layouts keep working — only colors change.
- The verdigris palette is **preserved as `verdigris` / `verdigris-dark`** (aesthetic group; named `mood-verdigris` until the v0.7.0 prefix drop), so nothing was lost from the lineup (now 37 themes / 74 blocks).
- The opt-in `reactive` theme itself is unchanged (the /visualize report tooling hardcodes its name); `pinion` is generated from the lineup with the standard token derivation, so the two are near- but not byte-identical (`base-300`/`neutral` differ slightly).

### v0.6.0 — 2026-07-17

- **daisyUI's 35 built-in themes are removed from the build** (`themes: false` in the preset). pinion-ui now ships **only original themes**: a 36-theme lineup × light/dark pairs (72 `[data-theme]` blocks, generated from `src/resources/themes/lineup.json`) plus the opt-in `reactive`. Any `data-theme` set to a daisyUI stock name renders unthemed (base tokens fall back to the `pinion` `:root` default colors but the attribute matches no block).

    Naming convention: `<name>` = light · `<name>-dark` = dark. Brand default pair: `pinion` / `pinion-dark` (`pinion` carries the daisyUI `default` flag → applies at `:root` with no attribute; `pinion-dark` carries `prefersdark` → auto-applies when the OS prefers dark and no `data-theme` is set; an explicit `data-theme` always wins).

    Migration (old → new), pick the nearest by intent:

    | You had | Move to |
    |---|---|
    | `pinion` (v0.4.0 warm-cream palette) | `pinion` (**same name, entirely new verdigris palette** — layouts keep working, colors change) |
    | `light` / `cupcake` / `emerald` / `winter` / `nord` | `pinion`, `mono`, `docs`, or any lineup light |
    | `dark` / `dim` / `night` / `business` | `pinion-dark`, `mono-dark`, `devtool-dark`, or any lineup `-dark` |
    | `dracula` / `synthwave` / `cyberpunk` | `monokai-dark` / `outrun-dark` / `neotokyo-dark` |
    | `corporate` | `bigblue` / `finance` |
    | `retro` / `valentine` / `pastel` | `pop` / `vapor` / `kids` |
    | `forest` / `garden` | `botanical` / `agri` / `ops` |
    | `coffee` / `luxury` / `black` | `atelier-dark` / `mono-dark` |
    | a hand-written `@plugin 'daisyui/theme'` block of your own | keep it — consumer theme blocks still work unchanged |

    `ui:install` migrates `data-theme="light"` layouts to `pinion` (confirmation default *yes* for that historical recommendation, *keep* for anything else); existing `data-theme="pinion"` layouts need no attribute change — the name is kept and only the palette changes. `<x-theme-switcher>`'s default cycle flipped `['light', 'dark']` → `['pinion', 'pinion-dark']`; `<x-theme-tune-switcher>`'s default list is now the grouped lineup (override with `:themes` for a flat list). `ui:eject --theme` default remains `pinion` (now resolving to the new verdigris palette), and the eject table's color keys are the new theme ids.

    Rationale: the lineup (each palette curated as a light/dark pair with per-app-domain triggers for LLM selection — see `AGENTS.md` → "Theme lineup & selection guide") replaces the generic daisyUI catalog as the product's color system. Design record: `docs/design/theme-lineup-v2-implementation.md` (internal).

### v0.5.0 — 2026-07

- **Tune spacing utilities renamed from magnitude tiers to Tailwind-idiom t-shirt sizes.** The legacy tier utilities (`space-section`, `space-section-inner`, `gap-section-inner`, `gap-element`, `gap-compact`, `gap-text`, `gap-inline`, `gap-micro`, `space-x-inline`, `space-x-micro`, `space-y-{micro,text,compact,element}`, `mt/mb-{text,element,section-inner}`, `p-element`, `p-compact`, `px-compact`, `py-compact`) are **removed** — replaced by tune-reactive `@theme --spacing-<size>` keys, which generate the full Tailwind spacing namespace (`p-*`, `px/py-*`, `m/mt/mb-*`, `gap-*`, `space-x/y-*`, …) for sizes `3xs 2xs xs sm md lg xl 2xl 3xl 4xl 5xl 6xl 7xl`. The public spacing tokens renamed accordingly: `--space-{section,section-inner,element,compact,text,inline,micro}` → `--spacing-{4xl,2xl,lg,sm,md,xs,2xs}` (override keys `--ovr-space-<tier>` → `--ovr-space-<size>`). Computed values are unchanged for every mapped tier (verified by the golden computed-style harness: 4,620 spacing cells diff=0 across 2 themes × 11 tunes × 5 strengths).

    Migration map (old → new): `space-section`→`py-4xl` · `space-section-inner`→`p-2xl` · `gap-section-inner`→`gap-2xl` · `gap-element`→`gap-lg` · `gap-compact`→`gap-sm` · `gap-text`→`gap-md` · `gap-inline`→`gap-xs` · `gap-micro`→`gap-2xs` · `space-x-inline`→`space-x-xs` · `space-x-micro`→`space-x-2xs` · `space-y-micro`→`space-y-2xs` · `space-y-text`→`space-y-md` · `space-y-compact`→`space-y-sm` · `space-y-element`→`space-y-lg` · `mt/mb-text`→`mt/mb-md` · `mt/mb-element`→`mt/mb-lg` · `mt/mb-section-inner`→`mt/mb-2xl` · `p-element`→`p-lg` · `p-compact`→`p-sm` · `px-compact`→`px-sm` · `py-compact`→`py-sm` · token `var(--space-<tier>)`→`var(--spacing-<size>)` (same tier map).

    Rationale & design record: `docs/design/spacing-v0.5-tshirt.md` (internal). The t-shirt scale also makes the rhythmic/optical convention self-documenting: t-shirt = tune-reactive rhythm, numeric = fixed optical nudge.

- **`<x-tune-styles>` removed.** The v1-era inline-`<style>` fallback for tune presets. It emitted the v1 tune names deprecated in v0.4.4 (`playful`/`elegant`/`bold`) and flat token values that override the Tune v2 `base + delta × strength` computed values when loaded alongside `pinion-ui.css` (same `[data-tune="…"]` specificity, later in the cascade). No code in the package, stubs, demo, or playground consumed it. Migration: delete the `<x-pn::tune-styles />` line from your layout — `ui:install` already imports the `pinion-ui.css` preset (which bundles `tune.css`) into `app.css`. The never-functional `ui:install --tune-only` option is removed with it.

### v0.4.3 (unreleased) — 2026-06

- **`ui:install` no longer overwrites a custom `data-theme` when run non-interactively.** The layout-patching confirm ("Switch to pinion?") defaulted to *yes*, and Laravel's `confirm()` returns the default in non-interactive runs (CI, agents, `-n`) — so every `ui:install` re-run silently stomped a host's custom theme (found via NADI's `data-theme="nadi"` → `"pinion"`). The default is now *yes* only when the current theme is `light` (the pre-v0.4.0 recommendation the migration was aimed at); any other value defaults to *keep*. Interactive behaviour is unchanged apart from the default answer.

### v0.4.2 — 2026-06

- **daisyUI component classes are no longer generated in consumer builds.** The pinion-ui preset (`pinion-ui.css`) now loads the daisyUI plugin itself with an `exclude:` list, and `ui:install` removes any standalone `@plugin "daisyui"` from the host `app.css` instead of adding one. Consumers keep daisyUI's full color/theme layer (`bg-primary`-style utilities, `data-theme`, all 35 themes) plus only the component CSS pinion-ui's own output references (avatar, breadcrumbs, collapse, divider, indicator, kbd, loading, mask, progress, range, rating, skeleton, stat, timeline, `join`).

    Migration: re-run `php artisan ui:install` (or delete the `@plugin "daisyui" { … }` block from `resources/css/app.css`) and rebuild. If your app hand-wrote daisyUI component markup (`<button class="btn">`, `<span class="badge badge-primary">`, …) those elements lose styling — replace them with the pinion-ui component (`<x-button>`, `<x-badge>`). Apps that don't re-run the installer keep their full plugin line and continue to work unchanged (the boundary is simply not enforced yet).

- **Dead `tooltip-light` / `tooltip-base-*` CSS removed from the preset.** These patched daisyUI's CSS tooltip, which `<x-tooltip>` stopped emitting in v0.3.11. No component output changes.

- **`<x-indicator>` no longer emits daisyUI `badge badge-*` classes.** The indicator chip is now utility-composed (same appearance × color grammar as `<x-badge>`), which let `badge` join the preset's exclude list — `.badge` no longer exists in consumer builds. Props (`position` / `dot` / `color` / `appearance`) are unchanged; `appearance="outline"` / `"dash"` chips now sit on an opaque `base-100` fill instead of transparent so they stay readable over the decorated content, and `dot` renders a fixed 12px circle instead of an empty `badge-xs`. Composer `item` strings changed accordingly (fixtures updated).

### v0.4.0 — 2026-05

- **`<x-tabs>` API: array → nested anonymous components.** The previous `:tabs="[key => [label, content, icon?]]"` array prop was removed in favour of nested children. New form:

    ```blade
    {{-- before (≤ v0.3.x) --}}
    <x-tabs :tabs="[
        'overview' => ['label' => 'Overview', 'content' => '<p>...</p>'],
        'specs'    => ['label' => 'Specs',    'content' => '<p>...</p>'],
    ]" />

    {{-- after (v0.4.0+) --}}
    <x-tabs>
        <x-tab name="overview" label="Overview"><p>...</p></x-tab>
        <x-tab name="specs"    label="Specs"><p>...</p></x-tab>
    </x-tabs>
    ```

    Migration: split each `$tabs` entry into an `<x-tab>` element, move `content` HTML into the child slot (Blade-escaped by default), lift `default` to the `<x-tabs default="…">` prop unchanged. `variant` and `size` remain on the parent. The nested form lets panels carry arbitrary Blade markup (other components, partials, multi-paragraph content) without going through `{!! !!}`.

    Composer keys: `tabList` and `panels` removed; `panel` added. Each `<x-tab>` emits its own button + panel sibling inside the parent's `flex-wrap` container, with CSS `order` keeping panels after the button row. No separate tablist wrapper exists.

- **`<x-accordion>` API: array → nested anonymous components.** The previous `:items="[[title, content], …]"` array prop was removed.

    ```blade
    {{-- before (≤ v0.3.x) --}}
    <x-accordion :items="[
        ['title' => 'Privacy', 'content' => '<p>...</p>'],
        ['title' => 'Cookies', 'content' => '<p>...</p>'],
    ]" />

    {{-- after (v0.4.0+) --}}
    <x-accordion>
        <x-accordion-item title="Privacy"><p>...</p></x-accordion-item>
        <x-accordion-item title="Cookies"><p>...</p></x-accordion-item>
    </x-accordion>
    ```

    Migration: move each `$items` entry into an `<x-accordion-item>`. Pass an explicit `name` if you need open-state stability across renders (e.g. Livewire), otherwise an auto `item_<hex>` is generated per render. `size` and `multiple` remain on the parent.

- **`pinion-dark` theme removed.** Pinion now ships only the `pinion` (light) theme. Consumers who want dark mode pick any daisyUI standard dark theme (`dark`, `dim`, `night`, `business`, …) via `<html data-theme>`. All 35 daisyUI v5 themes remain available unchanged.

### Earlier 0.x flips (already mentioned above)

- v0.2.0 — `<x-checkbox appearance>` default `'solid'` → `'soft'`. Opt back in with `appearance="solid"`.
- v0.2.1 — `<x-collapse icon>` default `'arrow'` → `null`. Opt back in with `icon="arrow"` or `icon="plus"`.
- v0.2.1 — namespace `<x-pinion-ui::…>` → `<x-pn::…>`. No alias was kept; this was a one-time rename.
- v0.3.0 → v0.3.4 — `<x-indicator>` / `<x-timeline>` default `appearance="soft"` reverted to `'solid'` after user testing. The `appearance` prop remains, so `appearance="soft"` is still a one-keyword opt-in.

## What's stable today, even pre-1.0

- The set of components and their prop names — additions only, no silent renames.
- The Compose-pattern contract: `static compose(array $props): array` returning a flat `string => string` dict.
- The namespace prefix (`<x-pn::xxx>`) and the anonymous form (`<x-button>`).
- The three style layers: `data-theme` (color), `data-tune` (shape / space / font), Blade props (variant / size / state).
- `AGENTS.md` and the per-component reference docs as the canonical machine-readable description of behaviour.
