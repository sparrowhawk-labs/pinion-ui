# CLAUDE.md — pinion-ui

Project context for AI agents working in this repository.

## Package

`sparrowhawk-labs/pinion-ui` — Blade UI components for Laravel built on **Tailwind v4 + daisyUI v5 + Alpine.js**. Anonymous components (`<x-button>`, `<x-modal>`, …) with a typed Compose layer for class strings.

## Architecture invariants

- **Composer pattern**: class strings live in `src/Compose/{Name}Composer.php` as PHP, not scattered in Blade. `compose($props)` returns a dict of class strings only — no array values, no markup.
- **Blade is render-only**: `@php $c = NameComposer::compose([...]) @endphp`, then `{{ $c['root'] }}` etc.
- **Fixture tests**: `tests/fixtures/compose/{name}.json` — each case has `props` → `expected` class dict. Run with `php tests/Compose/run.php`. Comparison is subset (only listed `expected` keys are checked).
- **Backwards compatibility**: never rename props or change their defaults silently. New props are opt-in (default = previous behavior).

## Local reference docs (gitignored)

`docs/` holds third-party UI library docs copied locally (via `site2md`) for design reference. **Not redistributed** — the entire `docs/*/` tree is gitignored.

| Path | What's there | When to grep it |
|---|---|---|
| `docs/daisyui/` | daisyUI 5 component & utility docs (11 md) | Verifying daisyUI class names / variants / size modifiers. Always check here before assuming a class exists (e.g. `rating-half`, `tooltip-open`). |
| `docs/preline/` | PrelineUI Tailwind component docs (270 md) | Design patterns for complex components (drawer, combobox, advanced datepicker). Adapt to our Compose layer; do not copy verbatim. |
| `docs/penguinui/` | PenguinUI Tailwind/Alpine component docs (102 md) | Alpine-driven interaction patterns (dropdown, modal, tabs). |

### Required behavior

**Before** implementing or modifying a component:

1. **Grep `docs/daisyui/` first** when the component wraps a daisyUI class — confirm exact class names, available size modifiers, and CSS variable hooks.
2. **Grep `docs/preline/` and `docs/penguinui/`** when adding a new pattern (sidebar drawer, command palette, combobox, etc.) — borrow proven UX defaults (focus trap, escape, animations, a11y attrs). Do not copy markup; reinterpret through our Compose layer.
3. **Cite the doc path** in your reasoning so the reviewer can verify (e.g. "per `docs/daisyui/pages/rating.md`, `rating-half` requires explicit `rating-{size}` to set input width").

These docs are why an "obvious" assumption can be off (e.g. daisyUI's `divider-horizontal` actually renders *vertical* inside a flex row). Use them.

## Playground

`pinion-ui-playground` (sibling repo at `../pinion-ui-playground/`) is the showcase Laravel app. Each component has a page at `resources/views/pages/{slug}.blade.php` with the "default" demo first (DEFAULT pill markup), variants below, and a `@verbatim`-wrapped code example. The playground sidebar groups components into **Static** (no Alpine) vs **Dynamic** (Alpine state).

## Conventions

- daisyUI default for a prop value (e.g. `size=md`) → emit no class **unless** another modifier requires the explicit class to be present (e.g. `rating-half` needs `rating-md`).
- For Blade pre-compiler safety: literal `<x-...>` inside subheadings or `<pre>` blocks must be wrapped in `@verbatim` and `&lt;` entities — otherwise Blade tries to mount them.
- `name`-style props (e.g. `<x-rating name=>`) must be unique per page demo or radio groups collapse.
