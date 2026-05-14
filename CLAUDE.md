# CLAUDE.md — pinion-ui (contributor doc)

**Audience**: AI agents and human contributors **working on this repo itself**. For consumer-side guidance (how to *use* pinion-ui in a Laravel app), read [`AGENTS.md`](./AGENTS.md) — that's the public entry point.

## Package

`sparrowhawk-labs/pinion-ui` — Blade UI component library for Laravel.

- **Stack**: Tailwind v4 + daisyUI v5 + Alpine.js
- **46 anonymous components** (`<x-button>`, `<x-modal>`, `<x-tabs>`, …); index in [`reference/components/index.md`](./reference/components/index.md)
- **Compose layer** (PHP class-string generators) keeps Blade render-only
- **Namespace**: `pn::` (e.g. `<x-pn::button>`) for disambiguation; anonymous `<x-button>` is the default. Old `<x-pinion-ui::…>` is gone (v0.2.1).
- **Theme**: ships `pinion` (warm cream + amber-550 accent + slate-indigo secondary, near-black ink) as default in v0.4.0+, plus `pinion-dark`. All 35 daisyUI v5 themes remain available.
- **Tune**: `tune.css` (855 lines) provides orthogonal shape/spacing/font/size tokens via `<html data-tune="…">` — 10 presets (`default`, `sharp`, `elegant`, `playful`, `bold`, `pixel`, etc.). Theme and tune mix freely.
- **Icons**: companion package `sparrowhawk-labs/pinion-icons` (separate repo, also stable on Packagist as of v0.1.0). Bundles Solar (1,234 names) + `solar-extra` (plain `close` / `check` / `plus` / `minus` glyphs Solar lacks).

## Architecture invariants

These are load-bearing. Do not violate without explicit discussion:

1. **Composer pattern** — class strings live in `src/Compose/{Name}Composer.php`. `compose(array $props): array` returns a **flat dict of class strings only** — no markup, no array values, no side effects.
   - This purity matters: it is the contract that lets a TS port (v0.6+) generate identical class strings for Web Component rendering (see Long-term direction).
   - Components currently without a composer (classes embedded in Blade): `button`, `alert`, `card`, `badge`, `avatar`, `menu-item`, `section.hero`, `theme-switcher`, `tune-styles`. These predate the pattern.
2. **Blade is render-only** — `@php $c = NameComposer::compose([...]) @endphp`, then `{{ $c['root'] }}`. No class-juggling in templates.
3. **Fixture tests** — `tests/fixtures/compose/{name}.json` with `props` → `expected` class dict. Run `php tests/Compose/run.php`. Comparison is **subset** (only listed `expected` keys are checked). Add cases when you add props.
4. **Backwards compatibility** — never rename props or change their defaults silently. New props are opt-in (default preserves previous behavior). `SEMVER.md` documents the policy and tracks the few intentional default flips (v0.2.0 checkbox, v0.2.1 collapse, v0.3.0 / v0.3.4 indicator+timeline soft↔solid).
5. **Three style layers stay orthogonal** — theme (color, via `data-theme`) × tune (shape/space/font, via `data-tune`) × component (variant/size, via Blade props). Never collapse axes.

## Repo layout

```
src/
  Commands/
    UiInstall.php         — `php artisan ui:install [--ai]` — wires preset CSS + Alpine plugins,
                            patches consumer's app.css to import pinion-ui.css preset,
                            and (with --ai) appends CLAUDE_SNIPPET.md to consumer's CLAUDE.md.
  Compose/                — PHP class-string generators (pure functions)
  resources/
    css/
      pinion-ui.css       — preset (v0.3.17 root fix). Consumers `@import` THIS, not piecemeal @source.
                            Bundles @source globs (Blade + Compose PHP), safelist, tooltip patches,
                            tune.css, and the `pinion` + `pinion-dark` theme definitions.
      tune.css            — 855-line shape/spacing/font/size token system, 10 tune presets,
                            self-hosted PixelMplus fonts.
    views/components/     — 45 Blade anonymous components (1 nested = 46 total per AGENTS.md count)
  PinionUiServiceProvider.php

reference/components/     — Per-component API docs (46 files + index.md)
AGENTS.md                 — Consumer-facing entry doc for AI agents. Read first when building
                            apps with pinion-ui. Lists calling conventions, gotchas, lookup paths.
CLAUDE_SNIPPET.md         — 1-paragraph snippet that `ui:install --ai` appends to a consumer's
                            CLAUDE.md so their Claude/AI agents are pointed at AGENTS.md.
SEMVER.md                 — Versioning policy + the audit trail of past default flips.
tests/
  Compose/run.php         — Fixture runner. Subset comparison against `fixtures/compose/*.json`.
docs/                     — Third-party UI docs copied locally (gitignored). See section below.
```

## Sibling repos

| Repo | Path | Role |
|---|---|---|
| `pinion-ui-playground` | `../pinion-ui-playground/` | Showcase Laravel app. Each component has `resources/views/pages/{slug}.blade.php` with the "default" demo first, variants below, `@verbatim`-wrapped code examples. Sidebar groups: **Static** (no Alpine), **Dynamic** (Alpine state), **Icons**, **Theme** (preview). Layout is `resources/views/layouts/playground.blade.php` (single source of truth for nav). Served at `http://pinion-ui-playground.pizza/`. |
| `pinion-icons` | `../pinion-icons/` | Solar + solar-extra icon set. Sibling Packagist release (v0.1.0+ stable). `INDEX.md` lists the 1,234 Solar names plus the solar-extra glyphs. |
| `pinion-ui-llm-test` | `~/project/pinion-ui-llm-test/` | Private, fresh Laravel app for LLM-style end-to-end verification of `ui:install --ai`. Path-repo symlinked to local pinion-ui / pinion-icons. Served at `http://pinion-ui-llm-test.pizza/`. |

## Local reference docs (gitignored)

`docs/` holds third-party UI library docs copied locally (via `site2md`) for design reference. **Not redistributed** — the entire `docs/*/` tree is gitignored.

| Path | What's there | When to grep it |
|---|---|---|
| `docs/daisyui/` | daisyUI 5 component & utility docs (11 md) | Verifying daisyUI class names / variants / size modifiers. Always check before assuming a class exists (e.g. `rating-half`, `tooltip-open`). |
| `docs/preline/` | PrelineUI Tailwind component docs (270 md) | Design patterns for complex components (drawer, combobox, advanced datepicker). Adapt to our Compose layer; do not copy verbatim. |
| `docs/penguinui/` | PenguinUI Tailwind/Alpine component docs (102 md) | Alpine-driven interaction patterns (dropdown, modal, tabs). |

### Required behavior

**Before** implementing or modifying a component:

1. **Grep `docs/daisyui/` first** when the component wraps a daisyUI class — confirm exact class names, available size modifiers, and CSS variable hooks.
2. **Grep `docs/preline/` and `docs/penguinui/`** when adding a new pattern (sidebar drawer, command palette, combobox, etc.) — borrow proven UX defaults (focus trap, escape, animations, a11y attrs). Do not copy markup; reinterpret through the Compose layer.
3. **Cite the doc path** in your reasoning so the reviewer can verify (e.g. "per `docs/daisyui/pages/rating.md`, `rating-half` requires explicit `rating-{size}` to set input width").

These docs are why an "obvious" assumption can be off (daisyUI's `divider-horizontal` actually renders *vertical* inside a flex row; `tooltip` default arrow uses `--tt-bg` mask which vanishes on `bg-base-100`; etc.). Use them.

## Required Alpine plugins (consumers)

Wired automatically by `ui:install`. Verified (grep) that **no other Alpine plugins** are used anywhere in components:

- `@alpinejs/focus` — `x-trap` for modal / sidebar / dropdown focus management (added v0.3.17)
- `@alpinejs/collapse` — `x-collapse` for accordion (added v0.3.19)

If you add a component that needs `x-intersect`, `x-anchor`, `x-mask`, `x-persist`, `x-sort`, etc., you must also extend `UiInstall.php` to wire the corresponding plugin into the consumer's `resources/js/app.js`.

## Conventions

- **daisyUI default for a prop value** (e.g. `size=md`) → emit no class **unless** another modifier requires the explicit class to be present (e.g. `rating-half` needs `rating-md`).
- **Blade pre-compiler safety**: literal `<x-...>` inside subheadings or `<pre>` blocks must be wrapped in `@verbatim` and `&lt;` entities — otherwise Blade tries to mount them. This is the recurring trap in reference docs.
- **`name`-style props** (e.g. `<x-rating name="...">`) must be unique per page demo or radio groups collapse.
- **Alpine shorthand inside Blade components**: `<x-foo :prop="...">` is Blade's PHP-eval syntax, NOT Alpine's `x-bind` shorthand. Use `x-bind:` / `x-on:` (long form) inside `<x-…>`. AGENTS.md documents this trap. ([[AGENTS.md Alpine trap section]])
- **Icon glyphs**: prefer `<x-i type="…" library="solar-extra">` for plain `close` / `check` / `plus` / `minus` (Solar only ships circle/square variants). Avoid inline SVG — they bypass icon system controls.
- **Composer purity** (load-bearing): `compose($props)` must be a pure function. No side effects, no DB / HTTP / I/O, no markup output, no array values. This guarantees the future TS port (v0.6+) can mirror it.

## Long-term direction (v0.5+)

Decided 2026-05-14. Anchor here when planning beyond v0.4.0 — do not re-litigate.

**Strategy**: ship pinion-ui as a multi-framework UI library by **converging on Web Components**, not by translating Alpine into per-framework reimplementations.

**Phased plan**:

| Phase | Artifact | Purpose |
|---|---|---|
| v0.5.0 | `pinion-ui-css` NPM package (theme + tune CSS only, no Blade) | React/Vue/Astro/vanilla all import the CSS — zero framework lock-in for the differentiator (theme × tune) |
| v0.6.0 | `pinion-ui-core` NPM (TS port of `src/Compose/*.php` — pure functions) | Class string generation becomes language-portable. PHP & TS Composers run side-by-side, CI snapshot-diff to detect drift |
| v0.7.0 | `pinion-ui-elements` NPM (Lit Web Components, **Light DOM**, internally calling `pinion-ui-core`) | Single component runtime, native in browser, consumable by every framework |
| v1.0 | Blade emits `<pn-*>` tags (Alpine removed) + framework wrappers codegen'd from manifests + cookbook docs | Source of truth = WC. Blade becomes a thin SSR wrapper. Each framework gets a `dist/{react,vue,solid,...}/` of 5-line wrappers generated from `*.manifest.ts` |

**Hard constraints (do not revisit unless these specifically become wrong)**:
- **Light DOM only** for Web Components. Shadow DOM breaks the theme × tune CSS variable cascade and forces daisyUI re-injection. Light DOM is the load-bearing decision.
- **Composer stays a pure function** — `(props) => Record<string,string>`. No side effects, no markup emission, no array values. The portability across PHP/TS depends on this.
- **Framework wrappers via rule-based codegen from manifests, not AI generation**. Wrappers are 5-line thin pass-throughs (prop → attribute, `onX` → `onx`). Determinism, audit, and cost favor codegen. AI is reserved for design-judgment work, not mechanical translation.
- **Do not** ship per-framework hand-written components, adopt Mitosis/Stencil DSLs, or write an Alpine-to-React AST translator. These were evaluated and rejected — the "translate Alpine" framing is the wrong shape; "converge on WC" is the right one.

**Why this works**: theme × tune is already pure CSS (framework-agnostic at birth). Composer is already a pure function (PHP-implementation-incidental). Web Components are a browser primitive both Blade SSR and JS frameworks consume natively. So the multi-framework story is **convergence, not translation** — no DSL, no compiler, no per-framework rewrite.

See auto-memory `project_multi_framework_strategy.md` and `feedback_codegen_over_ai_for_mechanical_translation.md` for the discussion that produced these constraints.
