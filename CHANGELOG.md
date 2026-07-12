# Changelog

All notable changes to `sparrowhawk-labs/pinion-ui` are documented here. The format
is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this
project adheres to the versioning policy in [`SEMVER.md`](./SEMVER.md) (which also
carries the authoritative audit trail of intentional default flips during `0.x`).

For releases before `v0.4.0`, see the per-tag GitHub release notes and `SEMVER.md`.

## [Unreleased]

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
- **`<x-terminal>`** — fake terminal window with a typewriter reveal, for demoing a CLI step
  (`artisan tinker`, a seeder run, a build command, …) without recording a real terminal (which
  steals window focus and is brittle to automate). Commands type character-by-character, output
  lines appear instantly; default slot reveals on finish, plus a `terminal-done` event. Pure
  Alpine, no opt-in JS install required. See [`reference/components/terminal.md`](./reference/components/terminal.md).

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
