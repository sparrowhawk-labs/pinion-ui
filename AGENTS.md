# AGENTS.md — pinion-ui (Laravel Blade adapter)

> **Stack**: Laravel Blade + Tailwind v4 + daisyUI v5 + Alpine.js. This is the Blade stack adapter for Pinion UI. Vanilla / React / Vue / Web Components adapters will each ship as their own NPM package with their own `AGENTS.md` (planned v0.5+). One package = one stack adapter, self-contained.

**Read this file before writing code that uses `sparrowhawk-labs/pinion-ui`.** It captures the rules, gotchas, and lookup paths an AI agent needs to use Pinion UI correctly.

## What this package is

A Laravel Blade component library (Tailwind v4 + daisyUI v5 + Alpine.js). 46 components — see [`reference/components/index.md`](./reference/components/index.md).

## Calling convention

- **Anonymous (default)**: `<x-button>`, `<x-modal>`, `<x-tabs>`, etc. Use this in app code.
- **Namespaced (disambiguation)**: `<x-pn::button>` — use only when a consumer app has its own `<x-button>` that conflicts.
- **Never use the old `<x-pinion-ui::xxx>` prefix** — it was renamed to `pn::` in v0.2.1.

### Nested parent + children (v0.4.0+)

Two components compose as a parent with nested children rather than driven by a single array prop:

| Parent | Child | What the child carries |
|---|---|---|
| `<x-tabs>` | `<x-tab name label :icon>{{ slot }}</x-tab>` | One tab button + one panel. |
| `<x-accordion>` | `<x-accordion-item title :name>{{ slot }}</x-accordion-item>` | One header + one disclosure region. |

```blade
<x-tabs variant="boxed">
    <x-tab name="overview" label="Overview"><p>…</p></x-tab>
    <x-tab name="specs"    label="Specs"><p>…</p></x-tab>
</x-tabs>
```

How it works under the hood (so the pattern doesn't surprise you):

- **Shared props via `@aware`**: parent props (`variant`, `size`, `multiple`) flow into children through Blade `@aware`. Don't declare them again on the child call site — they're inherited.
- **Shared state via Alpine scope**: the parent owns the only `x-data` (`activeTab` / `open`); children read and write it via Alpine's normal scope chain. Don't add a new `x-data` on a child.
- **Composer is called from both Blades**: parent and child each call the same `Composer::compose()` so the class strings line up. This stays consistent with the rest of the architecture rules below.

Per-component docs cover the full prop tables and slot contracts: [`reference/components/tabs.md`](./reference/components/tabs.md), [`reference/components/accordion.md`](./reference/components/accordion.md). The previous array-driven shape (`:tabs="[…]"` / `:items="[…]"`) was removed in v0.4.0 — see [`SEMVER.md`](./SEMVER.md).

## Architecture rules (do not violate)

1. **Compose pattern**: For most components, class strings live in `src/Compose/{Name}Composer.php`, not the Blade. Each composer has a static `compose(array $props): array` that returns a flat dict of class strings. The Blade reads `$c['root']`, `$c['title']`, etc. — it is render-only.
   - Components **without** a composer (classes embedded in the Blade): `button`, `alert`, `card`, `badge`, `avatar`, `menu-item`, `section.hero`, `theme-switcher`, `tune-styles`. These predate the pattern.
2. **Composer returns class strings only — no markup, no array values.** If you need conditional markup, branch in the Blade based on a prop.
3. **Fixture tests**: Each composer has `tests/fixtures/compose/{name}.json` covering its variant matrix. Run with `php tests/Compose/run.php`. Comparison is subset (only listed `expected` keys are checked). Add cases when you add props.
4. **Backwards compatibility**: Never rename props or silently change their defaults. New props are opt-in (default preserves previous behaviour).

## Three style layers (orthogonal)

| Layer | Where it lives | Examples |
|---|---|---|
| **Theme** (color palette) | `<html data-theme="...">` | `light`, `dark`, `dracula`, any daisyUI theme |
| **Tune** (shape / space / font) | `<html data-tune="...">` | `default`, `minimal`, `tech`, `elegant`, `playful` (11 presets) |
| **Component** (variant / size / state) | Blade props | `color="primary"`, `size="lg"`, `dismissible` |

Themes and Tunes mix freely. Both are activated by `<x-tune-styles />` injecting the `data-tune="*"` CSS once into the layout `<head>`.

## daisyUI v5 gotchas (verified — do not "fix")

- `divider-horizontal` renders a **vertical** line inside a flex row (daisyUI naming is inverted). The `<x-divider>` wrapper normalizes: `direction="vertical"` does what you expect.
- `rating-half` requires the explicit `rating-{size}` class even at default size, or half-star widths collapse. `RatingComposer` always emits it.
- `<x-collapse>` defaults to **no icon** since v0.2.1 — opt in with `icon="arrow"` or `icon="plus"`.
- `tooltip`'s stock daisyUI bubble is heavy on light surfaces; the default is `tooltip-light` (base-200 soft fill, arrow same colour to avoid the border-on-arrow problem). Use `color="<semantic>"` to opt back into daisyUI's native dark bubble.
- Several components ship **Japanese aria/label defaults** that you should override for non-Japanese UI. Known hardcodes today:
  - `<x-notification-system>` — `closeLabel='閉じる'` (toast dismiss button)
  - `<x-rating>` — clear-radio `aria-label='評価なし'`
  - `<x-pagination>` / `<x-pagination-simple>` — `prev='前へ'`, `next='次へ'`
  - `<x-table-scroll>` — scroll buttons `aria-label='前へスクロール'` / `'次へスクロール'`

  Override pattern: pass the prop explicitly per call site (`<x-pagination :prev="__('Previous')" :next="__('Next')" />`), or wrap each component in your own thin shim that pulls from `__()` / `config()`. A package-wide locale config is on the roadmap; for now treat the defaults as Japanese.

For class names not covered above, always grep `docs/{daisyui,preline,penguinui}/` (local third-party docs, gitignored) before claiming a class behaviour.

## Lookup workflow

For any component, read `reference/components/{name}.md`:
- Props (table with types and defaults)
- Slots
- Examples (basic + variants + edge cases)
- Class composition (link to the composer source)
- Related components and gotchas

`reference/components/index.md` is the category-grouped table of contents.

## Required peer dependency

`sparrowhawk-labs/pinion-icons` — every component that renders icons (`icon=`, `iconRight=`, internal `<x-i>`) needs it installed. The `pinion-ui` package hard-requires it.

## Alpine inside `<x-...>` components — use the full prefix

Blade's anonymous-component compiler treats `:prop="..."` and `@event="..."` as **PHP expressions** ("pass this PHP value to the prop / event"). Alpine's shorthand `:class` / `:value` / `@click` therefore breaks at runtime when used on `<x-button>`, `<x-input>`, or any other `<x-...>` element — typically with `Undefined constant "..."`.

Use the full Alpine prefix on `<x-...>` elements:

```blade
{{-- ✗ breaks: Blade evaluates `active === 'left'` as PHP --}}
<x-button :class="active === 'left' && '!bg-primary'" @click="active = 'left'">Left</x-button>

{{-- ✓ works: full Alpine prefix bypasses Blade --}}
<x-button x-bind:class="active === 'left' && '!bg-primary'" x-on:click="active = 'left'">Left</x-button>
```

Shorthand `:class` / `@click` is fine on plain HTML elements (`<button>`, `<div>`, `<input>`) — Blade only intercepts when the tag starts with `<x-`.

## When in doubt

1. Read the component's reference doc under `reference/components/`.
2. Read the composer source (if any) — class strings there are authoritative.
3. Grep `docs/{daisyui,preline,penguinui}/` for upstream behaviour.
4. Add a fixture case to capture new behaviour; do not just edit code.

## Reporting changes back

When you add a prop or change behaviour:
- Update the component's `reference/components/{name}.md`.
- Update `tests/fixtures/compose/{name}.json` with new cases.
- Update `reference/components/index.md` if the one-line summary still fits; replace it if it no longer does.
- Update this file only when an architecture rule or gotcha changes.
