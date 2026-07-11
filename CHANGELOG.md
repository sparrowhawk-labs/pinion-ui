# Changelog

All notable changes to `sparrowhawk-labs/pinion-ui` are documented here. The format
is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this
project adheres to the versioning policy in [`SEMVER.md`](./SEMVER.md) (which also
carries the authoritative audit trail of intentional default flips during `0.x`).

For releases before `v0.4.0`, see the per-tag GitHub release notes and `SEMVER.md`.

## [Unreleased]

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

[Unreleased]: https://github.com/sparrowhawk-labs/pinion-ui/compare/v0.4.4...HEAD
[0.4.4]: https://github.com/sparrowhawk-labs/pinion-ui/compare/v0.4.3...v0.4.4
[0.4.1]: https://github.com/sparrowhawk-labs/pinion-ui/compare/v0.4.0...v0.4.1
[0.4.0]: https://github.com/sparrowhawk-labs/pinion-ui/releases/tag/v0.4.0
