# Changelog

All notable changes to `sparrowhawk-labs/pinion-ui` are documented here. The format
is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this
project adheres to the versioning policy in [`SEMVER.md`](./SEMVER.md) (which also
carries the authoritative audit trail of intentional default flips during `0.x`).

For releases before `v0.4.0`, see the per-tag GitHub release notes and `SEMVER.md`.

## [0.8.4] — 2026-07-20

### Fixed
- **v0.8.3's dropdown grid fix had its own regression, corrected here.**
  `self-start justify-self-start` (v0.8.3) suppresses grid/flex stretch, but
  it *hard-codes* `justify-self: start` — so a consumer grid that explicitly
  sets `place-items-center` (deliberately centering triggers in their cells)
  got its centering silently overridden back to flush-left. Replaced with
  `w-fit` (`width: fit-content`): a *definite* size is what actually
  suppresses stretch per the CSS box-alignment spec, without hard-coding an
  alignment — an explicit `place-items-center` (or any other alignment) on
  the consumer's grid/flex is still respected.
- **Same root-cause fix extended to `<x-popover>` and `<x-tooltip>`** — both
  share the identical `relative inline-block` wrapper pattern with
  center-anchored panels (`left-1/2 -translate-x-1/2`), so the same
  grid/flex stretch silently off-centered their panel/bubble too. Root class
  now `relative inline-block w-fit` on all three composers.

## [0.8.3] — 2026-07-20

### Fixed
- **`<x-dropdown>` panel drifted sideways inside a CSS Grid (or a column-direction
  flex) parent.** A grid/flex item's `inline-block` display is blockified, so the
  default `justify-items: stretch` (grid) / `align-items: stretch` (flex, cross
  axis) silently stretched the dropdown's `relative inline-block` wrapper to the
  full grid-cell/flex-cell size. `position="*-end"` (`right-0`) then anchored to
  that stretched edge instead of the trigger button's real right edge, drifting
  the panel right by the leftover cell width — worse the narrower the trigger.
  `position="*-start"` (`left-0`) coincidentally looked fine since the button
  sits flush-left in the stretched wrapper. Fixed by adding `self-start
  justify-self-start` to the composer's `root` class (no-ops outside grid/flex,
  so safe everywhere). `<x-dropdown>` inside any grid layout (card grids,
  dashboards, …) now aligns correctly.

## [0.8.2] — 2026-07-20

### Changed
- **Switcher chrome is now tune-neutral** (`<x-theme-switcher>`,
  `<x-theme-tune-switcher>`, `<x-lang-switcher>`, `<x-settings-switcher>`):
  a control that changes the tune is no longer itself re-rendered by it.
  Chrome font is pinned to the base sans stack (the applied tune's body font
  — most visibly `draft`'s handwriting — no longer leaks into labels,
  triggers, and list rows), `draft`'s rough SVG filter no longer wobbles the
  switcher buttons, and tune-token radii/borders/shadows
  (`--radius-field`/`--radius-box`/`tune-border`/`--shadow-box`) were replaced
  with static equivalents. The trigger now shows the current tune's *name* in
  the neutral font instead of previewing its typeface. Per-option previews
  inside the dropdowns (color dots per `data-theme`, "Aa" labels per
  `data-tune`) are unchanged — that's the feature. Recorded in `SEMVER.md`.

### Added
- `tune.css`: **`.tune-exempt`** escape hatch — mark a subtree as tune-neutral
  site chrome (pinned base font + effect-level tune styling such as draft's
  rough filter lifted). Used by the switcher family; available to consumers
  for their own chrome.

## [0.8.1] — 2026-07-20

### Fixed
- `<x-settings-switcher>` 500'd on render ("Undefined variable $component"):
  a literal `<x-lang-switcher>` inside an `@props` comment was expanded by
  Blade's component-tag compiler (which runs before directive compilation;
  only `@php…@endphp` blocks are placeholder-protected). v0.8.0 shipped the
  broken file — do not use v0.8.0.

## [0.8.0] — 2026-07-20

### Added
- **`<x-settings-switcher>`** — theme × tune × lang consolidated into one
  trigger + panel for tight chrome (mobile navbars): sliders-glyph trigger with
  a live color-dots chip, panel stacking the grouped theme lineup (+ sun/moon
  light/dark toggle), the tune list, and an optional server-rendered Lang
  section. Same storage semantics as `<x-theme-tune-switcher>` (share
  `storage-key` across the responsive pair).
- `<x-theme-tune-switcher>` / `<x-settings-switcher>`: new `link` prop for the
  attribution target — `'github'` (repo, **new default**) / `'site'`
  (pinion-ui.dev) / any URL.

### Changed
- **`<x-theme-tune-switcher>` attribution repositioned** — the corner badge
  (absolutely positioned below the bar) is removed; the in-dropdown footer link
  moved to a faint pin at each dropdown's **top-right, outside the scrollable
  list** (stays visible while the theme list scrolls). Default target flipped
  pinion-ui.dev → GitHub repo. `:attribution="false"` still opts out. Recorded
  in `SEMVER.md`.
- **`<x-lang-switcher>` redesigned into the switcher control family** (label +
  chip trigger + dropdown of server-rendered locale links) so it pairs cleanly
  with `<x-theme-tune-switcher>` in one header row. All previous props accepted
  (`size` inert, `position` vertical-axis only); new `label` prop. Recorded in
  `SEMVER.md`.

## [0.7.3] — 2026-07-20

### Fixed
- **Component-internal strings now follow the app locale** — `pn_trans()`
  resolved its locale from `config('pinion-ui.locale')` with a hard `'ja'`
  default and never consulted `app()->getLocale()`, so pagination labels,
  the select placeholder, the rating aria-label, table-scroll button labels,
  and the notification close label rendered in Japanese on every non-Japanese
  (and every multi-locale) app. The config now defaults to `null` = follow
  the runtime app locale; setting `pinion-ui.locale` / `PINION_UI_LOCALE`
  still pins one. Lookup falls back `{locale}` → `en` → callsite default.
  Recorded as a default flip in `SEMVER.md`.

### Added
- Bundled `zh-Hans` / `zh-Hant` translation buckets for the component-internal
  strings (joining `ja` / `en`).

## [0.7.2] — 2026-07-19

### Added
- **`pn-feather-{t,b,l,r,x,y}` — eased edge-dissolve mask utilities** (new
  `src/resources/css/feather.css`, bundled in the preset and in the
  `pinion-ui-css` dist). Melts an element's edge into the page background with
  no visible boundary line: the alpha curve is `(1 − smoothstep)²` sampled at
  9 stops, so the fade's onset has zero slope (no Mach band) and the middle
  sinks fog-like. Depth knob `--pn-feather` (default `4rem`). Born on the
  pinion-ui.dev hero carousel; intended for scroller/marquee edges, long-text
  bottom fades, and image blends. Docs: AGENTS.md → "Edge dissolve".

## [0.7.1] — 2026-07-19

### Fixed
- **`[x-cloak] { display: none !important; }` is now bundled in the `pinion-ui.css` preset.**
  Package components (dropdown, modal, sheet, `<x-theme-tune-switcher>`, … — 21 templates)
  rely on `x-cloak` to stay hidden until Alpine boots, but the rule that makes `x-cloak`
  effective was left to the consumer's CSS — apps that never added it saw closed dropdown
  panels flash open for a frame on every page load. A consumer's own duplicate rule is the
  identical declaration and remains harmless.
- **Theme switchers no longer show both mode icons stacked before Alpine initializes.**
  The dark-branch (sun) icon in `<x-theme-switcher>` and `<x-theme-tune-switcher>`'s mode
  toggle is now `x-cloak`'d, so pre-init only the light-default moon icon renders.

## [0.7.0] — 2026-07-19

### Changed
- **BREAKING: `mood-` prefix dropped from theme names** — `mood-zen` → `zen`, `mood-monokai` →
  `monokai`, …, and `mood-synthwave` → **`outrun`** (avoids daisyUI's stock `synthwave` name).
  Full rename map in [`SEMVER.md`](./SEMVER.md). Category is now metadata: `lineup.json`,
  `pn_theme_groups()`, and `<x-theme-tune-switcher>` (group headings + a per-category chip
  next to the selected theme name).
- **Theme-individuality overhaul across all 35 non-frozen palettes**: perceivable per-theme
  canvas tints, primary-hue re-assignments where crowded (docs → sepia, factory → hazard amber,
  logistics → navy×orange, food → citrus lime, security → graphite×red, kids → crayon,
  atelier → espresso, analytics → plum, civic → vermillion accent, estate → blueprint canvas),
  and full de-duplication of support-color hexes (28 cross-theme reuses → 0). Nearest-pair
  OKLCH distance 1.32 → 1.80. `verdigris` and `mono` palettes unchanged.
- **Brand `pinion` reverted to the Primer-derived palette** (light `#0969DA` / dark `#4493F8`,
  `#F6F8FA` canvas), keeping one deliberate delta: teal secondary (`#0F766E` / `#2DD4BF`) so
  secondary no longer near-duplicates the success green.

## [0.6.1] — 2026-07-17

### Changed
- **Brand default `pinion` / `pinion-dark` recolored to the `reactive` palette family**
  (GitHub-Primer-adjacent clean technical document: white canvas, ink text, link-blue /
  green / purple semantics; Primer-Dark-adjacent dark pair). Same-day brand call on top
  of v0.6.0. The verdigris palette survives as **`mood-verdigris` / `mood-verdigris-dark`**
  — the lineup is now 37 themes (74 blocks). The opt-in `reactive` theme is unchanged.

## [0.6.0] — 2026-07-17

### Changed
- **BREAKING: theme lineup replaced wholesale — daisyUI's built-in themes are gone.** The
  preset now loads daisyUI with `themes: false`; pinion-ui ships **only original themes**:
  36 palettes × light/dark pairs (72 `[data-theme]` blocks) + the opt-in `reactive`.
  Naming: `<name>` = light, `<name>-dark` = dark (`payments` / `payments-dark`); the brand
  default pair is `pinion` / `pinion-dark`. With no `data-theme` set, `pinion` applies
  at `:root` — or `pinion-dark` when the OS prefers dark (`prefersdark` flag); an
  explicit `data-theme` always wins. Setting `data-theme` to a daisyUI stock
  name (`light`, `dark`, `dracula`, …) now renders **unthemed** — pick a lineup theme
  instead (migration table in [`SEMVER.md`](./SEMVER.md)).
- **BREAKING: the `pinion` theme keeps its name but gets an entirely new palette** —
  the v0.4.0 warm cream + amber colors are replaced by the Claude Verdigris brand pair
  `pinion` / `pinion-dark` (teal-green primary, terracotta secondary, warm ivory canvas).
  Existing `data-theme="pinion"` layouts keep working with the new colors. `ui:install`
  migrates `data-theme="light"` layouts to `pinion` (with confirmation; defaults to
  *yes* for that value, *keep* for anything else).
- Every lineup theme paints the page canvas via daisyUI's `--root-bg` (tinted `bg` color)
  while components sit on `base-100` (= the palette's panel, white in light mode) — tinted
  page + white cards with zero consumer CSS.
- `<x-theme-switcher>` default cycle is now `['pinion', 'pinion-dark']`; the sun/moon
  icon keys off the `-dark` suffix.
- `<x-theme-tune-switcher>` shows the grouped lineup (Brand / Mood / SaaS / Industry, from
  the new `pn_theme_groups()` helper) with a light/dark mode toggle. Passing `:themes`
  (flat literal ids) restores the ungrouped list.
- `ui:eject` default is `--theme=pinion`; `eject-table.json` now carries colors for
  the entire lineup, so any shipped theme can be ejected.

### Added
- **Theme lineup catalog + LLM selection guide** in `AGENTS.md` ("Theme lineup & selection
  guide") — app-domain → recommended-theme mapping so AI agents can pick a fitting theme
  when scaffolding. Canonical data: `src/resources/themes/lineup.json` (palettes, categories,
  triggers), which also generates the theme CSS (`npm run gen:themes` in
  `packages/pinion-ui-css`) — one source, no drift.
- `pn_theme_groups()` helper — the grouped light/dark theme ids for pickers/docs.

## [0.5.0] — 2026-07-13

### Changed
- **BREAKING: tune spacing utilities renamed to t-shirt sizes** — the magnitude-tier spacing
  utilities (`gap-element`, `space-section`, `p-compact`, `space-y-text`, …) are removed and
  replaced by tune-reactive `@theme --spacing-<size>` keys that generate the full Tailwind
  spacing namespace (`p-md`, `px-sm`, `gap-lg`, `mt-2xl`, `space-y-xl`, …) for sizes
  `3xs`–`7xl`. Public tokens renamed `--space-<tier>` → `--spacing-<size>`. Computed output is
  unchanged for every mapped tier (golden-harness verified, diff=0). Migration map and details
  in [`SEMVER.md`](./SEMVER.md). Nested `data-tune` scopes keep working — the spacing keys are
  re-declared on `[data-tune]` so nested tunes recompute them.

### Added
- **`ui:lint --spacing`** — a non-gating spacing usage census: counts rhythmic (t-shirt) vs
  optical (numeric/arbitrary) spacing tokens and lists optical locations, so tune-reactivity
  drift stays visible. Purely informational — never affects the exit code (`spacing` key in
  `--json`). The rhythmic/optical convention itself stays an authoring guideline: it encodes
  designer intent, which a token-level lint cannot judge without false-positive noise.
- **`ui:spacing-migrate`** — bulk-convert numeric spacing utilities to the nearest tune-reactive
  t-shirt size (`p-4` → `p-md`, `py-10` → `py-2xl`), making an existing static-Tailwind page
  respond to `data-tune`. Nearest is judged in log space (spacing perception is ratio-based —
  `p-5` = 20px goes to `lg`, not `md`); values with no close t-shirt (>×1.5 off, e.g. `p-64`)
  are reported, not converted. Dry-run by default, `--write` applies, `--json` for automation;
  variants / `!` / negative margins are preserved, and `*-px` / `*-0` / arbitrary values /
  `pinion-lint-ignore`-marked lines / width-family utilities are never touched.
- **`ui:eject`** — the reverse direction: freeze a theme × tune × strength rendering into
  vanilla Tailwind classes (t-shirt spacing → numeric, `tune-btn-md` → `h-9 px-3.5 text-[14px]`,
  `[var(--token)]` → measured px, `bg-primary` → hex with opacity modifiers preserved). Migrate
  to adopt, eject to leave — no lock-in. Token values are browser-measured per combo into
  `src/resources/eject-table.json` by the golden harness (`packages/pinion-ui-css/harness/
  eject-table.mjs`) rather than re-deriving tune.css math in PHP. Per-tune fonts, unknown
  `var(--…)` tokens and `<x-…>` component tags are reported for manual handling, not converted.
- **Snapshot undo for the rewrite commands** — every `ui:spacing-migrate --write` /
  `ui:eject --write` records the pre-rewrite files to `storage/pinion-ui/rewrites/<run>/`;
  `--runs` lists history, `--undo [--run=<id>]` reverts. Restores are hash-guarded: a file
  hand-edited since the rewrite is reported and left untouched, never silently clobbered.
- **`<x-terminal>`** — fake terminal window with a typewriter reveal, for demoing a CLI step
  (`artisan tinker`, a seeder run, a build command, …) without recording a real terminal (which
  steals window focus and is brittle to automate). Commands type character-by-character, output
  lines appear instantly; default slot reveals on finish, plus a `terminal-done` event. Pure
  Alpine, no opt-in JS install required. See [`reference/components/terminal.md`](./reference/components/terminal.md).

### Removed
- **`<x-tune-styles>`** — the v1-era Blade fallback that injected tune presets as inline
  `<style>` blocks. It still emitted the deprecated v1 tune names (`playful`/`elegant`/`bold`,
  renamed to `soft`/`editorial`/`luxury` in the v0.4.4 Tune v2 release) and flat token values
  that fight the v2 `base + delta × strength` system: loaded alongside `pinion-ui.css` (the
  `ui:install` default), its equal-specificity `[data-tune="…"]` blocks come later in the
  cascade and override tune.css's computed values. The zero-config path it promised is now the
  single `pinion-ui.css` import that `ui:install` wires into `app.css`. The `ui:install
  --tune-only` option (declared but never read — the CLI mirror of this component's `only`
  prop) is removed with it. **BC note**: this removal must ship in a minor (v0.5.0), per
  [`SEMVER.md`](./SEMVER.md).

### Fixed
- **t-shirt spacing keys no longer shadow Tailwind's container scale** — the `@theme
  --spacing-<size>` keys share their names (`3xs`–`7xl`) with the default `--container-*` scale,
  and the spacing namespace wins name resolution for the width-family utilities, so in host apps
  `max-w-6xl` compiled to `max-width: var(--spacing-6xl)` (72rem → 8rem, tune-reactive) and broke
  layouts (`w-<size>`, `min-w-<size>`, `basis-<size>` likewise). `tune.css` now ships a
  container-scale compensation `@theme` block pinning the per-utility namespaces (`--width-*`,
  `--min-width-*`, `--max-width-*`, `--flex-basis-*` — all of which outrank spacing) back to
  `var(--container-<size>)`, restoring stock behaviour while keeping host `--container-*`
  overrides working. Guarded by new golden-harness probes + a selfcheck container-scale gate;
  existing golden surface unchanged (diff=0 over 25,410 values). Height/size-family t-shirt
  leakage (`h-md`, `size-lg`, …) is additive-only in stock Tailwind and remains accepted.
- **tune.css** — font deltas (`--td-font-heading/body/mono`) are now reset to the base stack
  on every `[data-tune]` subtree, like every other delta. Previously they fell through to the
  resolver's `var()` fallback, so a nested `[data-tune]` element (e.g. a `data-tune="default"`
  font preview inside a `data-tune="pixel"` page) inherited the ancestor tune's font delta and
  rendered in the wrong font. Html-level tunes are unaffected.

### Internal
- **`@sparrowhawk-labs/pinion-ui-css` Phase 1 shipped** (`packages/pinion-ui-css/dist/`) — the
  standalone theme × tune CSS dist (no Tailwind preflight, no daisyUI built-in themes, no Blade
  globs; self-hosted PixelMplus + opt-in `dist/fonts.css`). Golden-gated: 27,170 computed values
  identical to the Blade reference build (110 combos × 122 probes), 91/91 tune utilities survive
  purge. Not yet published to npm (org claim pending) — `npm run dist` rebuilds + re-gates.
- **Theme definitions extracted to `src/resources/css/theme.css`** (pinion + reactive daisyUI
  theme blocks), imported by both the Blade preset and the dist build so the two can't drift.
  No-op for consumers (golden diff=0 over 27,170 values).

## [0.4.6] — 2026-07

### Added
- **`<x-lang-switcher>`** — navbar language switcher, a thin `<x-dropdown>` + `<x-menu-item>`
  composition; locale-routing-agnostic (the consumer resolves each locale's href).
- **`<x-skin-wall>`** — decorative full-bleed diagonal marquee rendering the same slot markup
  across many `data-tune` × `data-theme` skins.
- **`<x-stat-group>`** — companion to `<x-stat>`, migrated off daisyUI's `.stats` class.
- **`<x-sheet>`** — `grid-columns-changed` flush-time notification (insert/delete/convert/add/
  reorder/undo; width excluded) so Livewire hosts can persist `detail.columns`.
- **`<x-editor>`** — JS-level `opts.extensions` for consumer Tiptap extensions (Table/Markdown/
  etc.), and the raw Editor instance now exposed via a `_pnEditor` DOM property (bypasses the
  reactive Alpine proxy that was corrupting ProseMirror transactions).
- **`packages/pinion-ui-css`** (internal, not yet shipped) — Phase 0 golden computed-style
  verification harness for the planned standalone CSS package.

### Fixed
- **`<x-select>`** — custom-mode trigger label stuck on the placeholder/first-option label on
  initial mount, even though the bound value (DB, Livewire property, native `<select>`) was
  already correct — an Alpine `init()` vs. `wire:model` init-order race.
- **daisyUI structural classes removed project-wide** — avatar-group, breadcrumb, collapse,
  divider, indicator, kbd, pagination, progress, range-slider, rating, skeleton, spinner, stat,
  and timeline no longer use daisyUI's own component CSS classes (only semantic color utilities
  remain); every daisyUI component is now excluded from the compiled preset.
- 6 visual regressions found in a follow-up review of the above migration: avatar-group ring
  tracing a rectangle instead of the circle, breadcrumb icon+label wrapping to 2 lines, vertical
  divider collapsing to content height, indicator single-char badges rendering as ovals,
  pagination active-item border seam, and timeline border/shadow overlap between items.
- **`<x-indicator>`** — badge rendered as a squashed oval instead of a circle for single-character
  content; pinned to a fixed square box.
- **`<x-tooltip>`** — dropped a redundant box-shadow (border alone was already sufficient).
- **`<x-range-slider>`** — a missing space before `@endif` made Blade treat it as literal text,
  leaving the directive uncompiled and breaking `showValue`.
- **`<x-editor>`** — added `wire:ignore` to the Tiptap host; Livewire re-renders were morphing
  the client-owned ProseMirror DOM back to an empty server-rendered div.

### Docs
- Spacing-tier-by-structural-level guidance added to AGENTS.md.
- README hero GIF.

## [0.4.5] — 2026-06

### Added
- **`<x-editor>` H4 heading** — heading level 4 added to the editor. Tiptap `heading.levels`
  is now `[1, 2, 3, 4]`, an **H4** button joins H1–H3 in the floating format toolbar, and the
  `####` + space markdown input rule creates an H4. New `.pn-prose h4` style (serif display,
  sized below H3 and above body) and empty-heading placeholder. Round-trips through markdown
  as `#### ` (bare StarterKit consumers preserve level 4).

## [0.4.4] — 2026-06

### Added
- **Tune v2 system** — `base + delta × strength` token model (`--tb-*` base / `--td-*`
  per-tune delta / `--ovr-*` override, scaled by `data-tune-strength="xs…xl"`), 11 MECE
  presets, tune-driven multi-layer shadows, new tokens (`--tracking-heading`,
  `--leading-body`, `--type-scale-ratio`, `--space-micro`), a micro spacing tier +
  `space-y-{micro,text,compact,element}` utilities, and the rhythmic-vs-optical spacing
  convention. v1 tune names are deprecated/renamed: `playful`→`soft`, `elegant`→`editorial`,
  `bold`/`monumental`→`luxury`, `sketch`→`draft`, `terminal`→`tech`.
- **`<x-positioning-map>`** — generic 2-axis positioning / perceptual map (tune-independent;
  takes `points()`).
- **`<x-sheet>` S3** — column sort, row/column reorder, fill-handle drag (tile fill),
  undo (Cmd/Ctrl+Z), column resize (drag header edge), and a right-click context menu with
  column type conversion.
- **`php artisan ui:lint`** — lints Blade markup against the class-vocabulary rule
  (AGENTS.md → "Class vocabulary"): flags excluded daisyUI **component** classes
  (`.btn`, `.card`, … — silent no-ops in the build) and **fixed/hex** colors
  (`bg-blue-500`, `text-[#1d4ed8]` — ignore `data-theme`), while leaving plain
  Tailwind, daisyUI *semantic* colors, tune classes/tokens, and the kept daisyUI
  parts (`progress`, `timeline`, `range`, …) untouched. Also flags a root `<html>`
  missing **`data-theme`** / **`data-tune`** — the theme × tune cascade root; without
  them colors stop tracking the theme and tune tokens don't apply (a silent break).
  Handles static `class="…"`, dynamic `:class` / `@class([…])`, and variant prefixes;
  a `pinion-lint-ignore` comment suppresses a line. Exits non-zero, so it gates CI /
  pre-commit / a Claude Code PostToolUse hook. Pure `ClassVocabularyLinter` core
  (no Laravel) with 44 unit tests (`composer lint`).
- **`ui:install` installs a lint-after-edit hook** — copies `.claude/hooks/lint-blade.php`
  and registers a `PostToolUse` (`Edit|Write`) entry in `.claude/settings.json`. After an
  agent edits a Blade file, the hook runs `ui:lint` on it and, on violations, feeds them
  back into the agent's context via `hookSpecificOutput.additionalContext` (the hook exits
  0 and prints JSON — a non-zero exit would be dropped from the model's context). Pure PHP
  (no `jq`), shell-guarded so it's a no-op where the script is absent (shared symlinked
  `settings.json`), idempotent, and skippable with `--skip-hooks`.
- **`ui:install --git-hook`** — installs a **general, agent-agnostic** git pre-commit hook
  that runs `ui:lint` on staged Blade files and blocks the commit on violations. Works for
  any workflow (human / CI / any CLI agent), complementing the Claude-Code-specific
  PostToolUse hook. Never clobbers an existing `.git/hooks/pre-commit`. `ui:lint --json` is
  documented in AGENTS.md as the single universal interface (no per-agent adapters).
- **`reactive` theme** — GitHub-Light-adjacent light palette (pure-white canvas, cool
  gray surfaces, blue/green/purple semantic set), ported from the `/visualize` report
  tooling so HTML reports and pinion-ui apps share one color system. Opt-in via
  `data-theme="reactive"`; `pinion` remains the default.

### Fixed
- **`<x-editor>`** — task-list checkbox is centered on the first text line with a larger,
  full-line-height click target; the empty `<span>` Tiptap appends is hidden so flex
  centering stays accurate.
- **`<x-sheet>`** — sorting no longer widens the column (the ↺ button box is reserved);
  S3d column resize honors a 120px minimum and the frame hugs the table.

### Internal
- `CLAUDE.md` and the `docs/` tree are no longer shipped in the package tarball — internal
  contributor/design docs are kept local-only (gitignored).

## [0.4.1] — 2026-06

### Added
- **`<x-rating>` Livewire support** — `wire:model` is detected and forwarded onto the
  individual radio inputs (with native `value` attributes) for two-way binding; it is
  stripped from the root `<div>`. Pure Blade, works without Livewire installed, and the
  non-Livewire output stays byte-identical (opt-in, backward-compatible).
- **Tune `minimal` preset** — airy spacing, restrained type (11th preset).
- **Tune spacing utilities** — `mb-element`, `mt-section-inner`, `mb-section-inner`.

### Fixed
- **`<x-input-group>`** — joined-row collapse for custom selects, focus-seam rework, and
  a uniform border width across joined children (focus ring no longer breaks the seam).
- **`<x-stepper>`** — dotted-variant connector alignment.
- **`<x-radio-group>` accessibility** — the `<legend>` now carries the `id` that the inner
  `role="radiogroup"` references via `aria-labelledby` (previously it pointed at no element,
  so screen readers got no group label). `aria-labelledby` is omitted entirely when no
  `label` is set, avoiding a dangling reference.

### Changed
- **Dependency resolution** — `sparrowhawk-labs/pinion-icons` is now required as `^1.0`
  (the icons package ships its first stable `v1.0.0`), and `minimum-stability` is `stable`.
  A default stable-only Laravel app can now `composer require sparrowhawk-labs/pinion-ui`
  without pulling dev versions.

### Internal
- **CI** — GitHub Actions runs the Compose fixture suite on PHP 8.2 / 8.3 / 8.4.
  Added a `composer test` script. The Compose layer is dependency-free, so the suite runs
  without `composer install`.
- **Docs freshness sweep** — `AGENTS.md`, `CLAUDE.md`, `README.md`, and the
  `reference/components/*.md` set were audited against the actual Blade/Composer/fixtures
  and corrected (appearance/color counts, default values, removed-API examples, the
  implemented `config('pinion-ui.locale')` i18n mechanism). Stale `input-group` / `stepper`
  fixtures were synced to the current composer output (suite green: 299 pass / 0 fail).

## [0.4.0] — 2026-05

### Changed (BC)
- **`<x-tabs>` and `<x-accordion>`**: array-driven props (`:tabs` / `:items`) removed in
  favour of nested anonymous children (`<x-tab>` / `<x-accordion-item>`). See `SEMVER.md`
  for the migration guide.
- **`pinion-dark` theme removed** — Pinion now ships only the `pinion` (light) theme.
  Consumers pick any daisyUI standard dark theme via `<html data-theme>`.

[0.6.1]: https://github.com/sparrowhawk-labs/pinion-ui/compare/v0.6.0...v0.6.1
[0.6.0]: https://github.com/sparrowhawk-labs/pinion-ui/compare/v0.5.0...v0.6.0
[0.4.4]: https://github.com/sparrowhawk-labs/pinion-ui/compare/v0.4.3...v0.4.4
[0.4.1]: https://github.com/sparrowhawk-labs/pinion-ui/compare/v0.4.0...v0.4.1
[0.4.0]: https://github.com/sparrowhawk-labs/pinion-ui/releases/tag/v0.4.0
