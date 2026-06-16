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

### Class vocabulary — what you actually write

Because the layers are orthogonal, every class string you author — a composer, a Blade, raw app markup, or a non-Blade adapter — is **plain Tailwind v4 by default, with exactly two exceptions**:

| Concern | Use | Not |
|---|---|---|
| **Color** | daisyUI semantic color classes — `bg-primary` `text-primary-content` `bg-base-200` `text-base-content` `border-base-300` `text-error` `bg-success/10` (these track `data-theme`) | a fixed palette/hex (`bg-blue-500`, `#1d4ed8`) — ignores the theme |
| **Shape · border · component size** (must follow the tune) | pinion-ui tune classes & tune tokens — `tune-border`; `tune-btn-{xs,sm,md,lg}` / `tune-input-*` / `tune-textarea-*` / `tune-card-pad`; raw tokens `rounded-[var(--radius-box)]` `[var(--space-*)]` `[var(--h-field-md)]` (these track `data-tune`) | a fixed Tailwind value (`rounded-lg`, `h-10`, `border`) where it should follow `data-tune` |
| **Everything else** | plain Tailwind v4 — layout (`flex grid items-center gap-2`), generic spacing (`px-3 py-1.5`), typography (`text-sm font-semibold`), state (`hover:… disabled:…`) | — |

**Never daisyUI _component_ classes** (`.btn` `.card` `.badge` `.input` `.menu` …): excluded from the build (see gotchas) — they produce no styling. Compose the look from the three vocabularies above, or use the `<x-…>` component. The rule in one line: **plain Tailwind, except daisyUI color classes (color) and pinion-ui tune classes/tokens (shape · space · size).**

## daisyUI v5 gotchas (verified — do not "fix")

- **daisyUI component classes don't exist in your build.** The pinion-ui preset loads daisyUI with an exclude list: you get the full color/theme layer (`bg-primary`, `text-base-content`, `data-theme`, all 35 themes) but `.btn`, `.card`, `.alert`, `.input`, `.menu`, `.modal`, etc. produce **no styling**. Never write daisyUI component markup (`<button class="btn btn-primary">`) — use the pinion-ui component (`<x-button color="primary">`). Do not "fix" this by adding `@plugin "daisyui"` to app.css; that re-enables everything and breaks the design boundary.
- `divider-horizontal` renders a **vertical** line inside a flex row (daisyUI naming is inverted). The `<x-divider>` wrapper normalizes: `direction="vertical"` does what you expect.
- `rating-half` requires the explicit `rating-{size}` class even at default size, or half-star widths collapse. `RatingComposer` always emits it.
- `<x-collapse>` defaults to **no icon** since v0.2.1 — opt in with `icon="arrow"` or `icon="plus"`.
- `<x-tooltip>` no longer uses daisyUI's CSS `tooltip` / `data-tip` system (dropped in v0.3.11 for an Alpine + custom-arrow approach; daisyUI's `tooltip` CSS is excluded from the build). The `text` / `position` / `color` / `open` props are unchanged.
- Several components ship **locale-aware aria/label defaults** resolved through `pn_trans()` → `config('pinion-ui.locale')` (default `ja`; an `en` bucket also ships). Components that pull defaults this way:
  - `<x-notification-system>` — toast dismiss button (`notification.close`)
  - `<x-rating>` — clear-radio aria-label (`rating.none`)
  - `<x-pagination>` / `<x-pagination-simple>` — `prev` / `next` / info template / aria (`pagination.*`)
  - `<x-table-scroll>` — scroll buttons aria-label (`table_scroll.prev` / `.next`)
  - `<x-select>` — placeholder (`select.placeholder`)

  **To switch the whole app's component strings to English**: set `PINION_UI_LOCALE=en` (or `config('pinion-ui.locale')` directly). This is intentionally independent of Laravel's `config('app.locale')`. **Per-call override still wins**: pass the prop explicitly (`<x-pagination prevLabel="Older" nextLabel="Newer" />`). **Add a locale**: extend the `translations` array in the published `config/pinion-ui.php`. Missing locale/key falls back to the literal Japanese string baked into the component as `pn_trans()`'s second argument.

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

## Livewire integration

pinion-ui is a **Blade-only library** (no Livewire components inside). Components work inside Livewire component Blade trees without any special setup. The notes below document what works, what doesn't, and why.

### wire:model compatibility matrix

| Component(s) | wire:model support | Notes |
|---|---|---|
| `input`, `textarea`, `select`, `checkbox`, `radio`, `toggle`, `range-slider`, `input-number`, `file-upload` | ✅ Full — direct passthrough | `wire:` attrs forwarded via `$attributes->whereStartsWith('wire:')` to the native form element |
| `rating` | ✅ Full — native radio | `wire:model` is forwarded to each `<input type="radio">` with the correct `value=` attr. `.live` works. |
| `pin-input` | ✅ Supported (dispatch pattern) | `wire:model` goes on a dedicated hidden `<input>`. An Alpine `$watch` on `combined` dispatches a native `input` event → Livewire is notified on every digit change. |
| `radio-group` | ❌ Not supported on wrapper | Do **not** put `wire:model` on `<x-radio-group>`. Apply it to each child `<x-radio wire:model="field" value="x">` directly. |
| `input-number` | ⚠️ Partial | User-typed changes notify Livewire (native `input` event). Clicking the **+/−** buttons changes Alpine's `v` programmatically — no native `input` event fires, so Livewire is **not** notified. Add an `x-effect` / `$watch` at the call site if you need button-click sync. |

### Detecting wire:model in Blade (UI library pattern)

Always use `$attributes->whereStartsWith('wire:model')` — **not** `$attributes->wire('model')`. The `wire()` macro is registered by Livewire at runtime; pinion-ui must work in apps without Livewire installed.

### Loops: always add wire:key

Morphdom needs a stable key on every repeated Livewire-rendered component:

```blade
@foreach($items as $item)
    <x-card wire:key="item-{{ $item->id }}">…</x-card>
@endforeach
```

### wire:loading on buttons

```blade
{{-- spinner while any Livewire request is in-flight --}}
<x-button wire:loading.attr="disabled" wire:target="save">Save</x-button>
```

`$attributes` on `button` is merged at the root `<button>` element, so `wire:loading` / `wire:target` / `wire:click` all work as expected.

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
