# Versioning & Deprecation Policy

`sparrowhawk-labs/pinion-ui` follows [Semantic Versioning](https://semver.org/) with the conventions below. Tagged releases on GitHub (`v0.X.Y`) carry release notes that call out anything not in this document.

## 0.x — pre-1.0 (current)

While in `0.x`, the API surface is stable in spirit but **not yet under a 1.0 contract**:

- **Patch (`0.X.Y` → `0.X.Y+1`)** — bug fixes, doc updates, internal refactors, and additive props whose default preserves previous behaviour. Safe to upgrade.
- **Minor (`0.X.0` → `0.X+1.0`)** — new components, new props, default-behaviour changes that improve the common case. May break consumers who relied on a specific default value or class string. Read the release notes before upgrading.
- **Major (`0.x` → `1.0`)** — declared once the API is considered stable. After 1.0, breaking changes live in major versions only.

In `0.x`, we err toward **adding new opt-in props** rather than silently flipping defaults. When we do flip a default — e.g. `<x-checkbox appearance>` `'solid'` → `'soft'` in v0.2.0, `<x-collapse icon>` `'arrow'` → `null` in v0.2.1 — it is called out in the release notes and the previous behaviour remains opt-in via the original value.

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

## What's stable today, even pre-1.0

- The set of components and their prop names — additions only, no silent renames.
- The Compose-pattern contract: `static compose(array $props): array` returning a flat `string => string` dict.
- The namespace prefix (`<x-pn::xxx>`) and the anonymous form (`<x-button>`).
- The three style layers: `data-theme` (color), `data-tune` (shape / space / font), Blade props (variant / size / state).
- `AGENTS.md` and the per-component reference docs as the canonical machine-readable description of behaviour.
