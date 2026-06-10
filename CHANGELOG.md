# Changelog

All notable changes to `sparrowhawk-labs/pinion-ui` are documented here. The format
is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this
project adheres to the versioning policy in [`SEMVER.md`](./SEMVER.md) (which also
carries the authoritative audit trail of intentional default flips during `0.x`).

For releases before `v0.4.0`, see the per-tag GitHub release notes and `SEMVER.md`.

## [Unreleased]

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

[Unreleased]: https://github.com/sparrowhawk-labs/pinion-ui/compare/v0.4.1...HEAD
[0.4.1]: https://github.com/sparrowhawk-labs/pinion-ui/compare/v0.4.0...v0.4.1
[0.4.0]: https://github.com/sparrowhawk-labs/pinion-ui/releases/tag/v0.4.0
