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
   - Components **without** a composer (classes embedded in the Blade): `button`, `alert`, `card`, `badge`, `avatar`, `menu-item`, `section.hero`, `theme-switcher`. These predate the pattern.
2. **Composer returns class strings only — no markup, no array values.** If you need conditional markup, branch in the Blade based on a prop.
3. **Fixture tests**: Each composer has `tests/fixtures/compose/{name}.json` covering its variant matrix. Run with `php tests/Compose/run.php`. Comparison is subset (only listed `expected` keys are checked). Add cases when you add props.
4. **Backwards compatibility**: Never rename props or silently change their defaults. New props are opt-in (default preserves previous behaviour).

## Three style layers (orthogonal)

| Layer | Where it lives | Examples |
|---|---|---|
| **Theme** (color palette) | `<html data-theme="...">` | `pinion`, `pinion-dark`, `monokai`, `payments-dark` (37 original light/dark pairs — see the theme lineup section below; daisyUI's built-in themes do **not** exist in the build) |
| **Tune** (shape / space / font) | `<html data-tune="...">` | `default`, `minimal`, `tech`, `editorial`, `soft` (11 presets) |
| **Component** (variant / size / state) | Blade props | `color="primary"`, `size="lg"`, `dismissible` |

Themes and Tunes mix freely. Both are activated by the `pinion-ui.css` preset (imported into `app.css` by `ui:install`), which bundles the `data-tune` token system from `tune.css`.

### Class vocabulary — what you actually write

Because the layers are orthogonal, every class string you author — a composer, a Blade, raw app markup, or a non-Blade adapter — is **plain Tailwind v4 by default, with exactly two exceptions**:

| Concern | Use | Not |
|---|---|---|
| **Color** | daisyUI semantic color classes — `bg-primary` `text-primary-content` `bg-base-200` `text-base-content` `border-base-300` `text-error` `bg-success/10` (these track `data-theme`) | a fixed palette/hex (`bg-blue-500`, `#1d4ed8`) — ignores the theme |
| **Shape · border · size · _rhythmic_ spacing** (must follow the tune) | pinion-ui tune classes & tune tokens — `tune-border`; `tune-btn-{xs,sm,md,lg}` / `tune-input-*` / `tune-textarea-*` / `tune-card-pad`; **t-shirt spacing utilities** — every Tailwind spacing-namespace utility with a t-shirt suffix is tune-reactive: `p-md` `px-sm` `py-4xl` `gap-lg` `gap-2xs` `mt-2xl` `mb-md` `space-y-lg` `space-x-xs` … sizes `3xs 2xs xs sm md lg xl 2xl 3xl 4xl 5xl 6xl 7xl` (ramp: `3xs`=2px `2xs`=4px `xs`=8px `sm`=12px `md`=16px `lg`=24px `xl`=32px `2xl`=48px `3xl`=64px `4xl`=80px, then 96/128/160px — bases the tune then scales); raw tokens `rounded-[var(--radius-box)]` `[var(--spacing-*)]` `[var(--h-field-md)]` (all track `data-tune`) | a fixed Tailwind value (`rounded-lg`, `h-10`, `border`, `gap-12`, `mb-10`, `space-y-4`) where the value is *rhythm* and should follow `data-tune` |
| **Everything else** | plain Tailwind v4 — layout (`flex grid items-center`), **purely-optical/fixed** spacing (a `mt-0.5` baseline nudge, a fixed `gap-2` between an icon and its label *when you do not want it to breathe*), typography (`text-sm font-semibold`), state (`hover:… disabled:…`) | — |

**Never daisyUI _component_ classes** (`.btn` `.card` `.badge` `.input` `.menu` …): excluded from the build (see gotchas) — they produce no styling. Compose the look from the three vocabularies above, or use the `<x-…>` component. The rule in one line: **plain Tailwind, except daisyUI color classes (color) and pinion-ui tune classes/tokens (shape · size · rhythmic space).**

**Rhythmic vs optical spacing.** The dividing line for spacing is *not* size — it is **purpose**, and the two scales make it self-documenting: **t-shirt = rhythmic** (tune-reactive), **numeric = optical** (fixed). *Rhythmic* spacing is the page's breathing: section padding (`py-4xl`), gaps between cards/sections (`gap-2xl`, `gap-lg`), heading→body margins (`mb-md`), list vertical rhythm (`space-y-md`), down to the smallest `gap-2xs` (icon↔label, chip gaps). Use t-shirt sizes so a tight tune (`corporate`, `tech`) reads denser and an airy one (`minimal`, `soft`) reads roomier — otherwise the page only morphs its shape/font and the spacing stays frozen. *Optical* spacing is a fixed nudge that aligns one element (a `mt-0.5` shift to sit an icon on the text baseline, a deliberate `gap-2` that must never breathe); a t-shirt value would morph it and misalign, so keep it numeric/arbitrary. This convention is **deliberately not machine-enforced** — rhythmic-vs-optical is designer *intent*, which no token-level check can recover without false-positive noise; it stays an authoring guideline. `ui:lint --spacing` prints a **non-gating census** (rhythmic vs optical counts + optical locations) so drift stays visible: when a page barely reacts to `data-tune` spacing, a high optical share is the tell. To fix it in bulk, **`php artisan ui:spacing-migrate [paths…]`** converts numeric spacing to the nearest t-shirt size (`p-4`→`p-md`, `py-10`→`py-2xl`; log-space nearest, so `p-5`→`p-lg`) — dry-run by default, `--write` applies, `--json` for automation. It leaves `*-px` / `*-0` / arbitrary values / `pinion-lint-ignore`-marked lines untouched and skips values with no close t-shirt (>×1.5 off, e.g. `p-64`); after a bulk `--write`, review the diff and re-opticalize the few genuine fixed nudges. Every `--write` auto-snapshots to `storage/pinion-ui/rewrites/` — `--runs` lists them, `--undo` (or `--undo --run=<id>`) reverts, hash-guarded so files hand-edited since the rewrite are reported and left alone.

**Ejecting — freeze theme × tune to vanilla Tailwind.** The reverse direction also exists: **`php artisan ui:eject [paths…] [--tune=default] [--strength=md] [--theme=pinion]`** rewrites pinion-ui classes to the plain Tailwind that reproduces the chosen rendering — t-shirt spacing → numeric (`p-md`→`p-4` under default/md), tune utilities expanded (`tune-btn-md`→`h-9 px-3.5 text-[14px]`), `[var(--token)]` → measured values, semantic colors → hex (`bg-primary`→`bg-[#131110]`, opacity modifiers preserved). Migrate to adopt, eject to leave — no lock-in. Dry-run by default; `--write` applies (snapshot + `--undo` as above); rebuild your CSS afterwards so Tailwind generates the new classes. Values are browser-measured per tune×strength (`src/resources/eject-table.json`). Not ejected (reported): per-tune fonts (`font-heading` …, load families manually), unknown `var(--…)` tokens, and `<x-…>` component tags — those are structural Blade, replace them by hand if you are fully leaving.

**Which size at which structural level.** The t-shirt sizes form a ladder (`3xs` 2px < `2xs` 4px < `xs` 8px < `sm` 12px < `md` 16px < `lg` 24px < `xl` 32px < `2xl` 48px < `3xl` 64px < `4xl` 80px < `5xl`–`7xl` 96–160px, base values — all scale with tune strength). Pick the size by *what two things you're spacing apart*, not by eyeballing a pixel value:

| Structural level | What it separates | Size to use |
|---|---|---|
| **Between page sections** | One `<section>`/major page block to the next (hero → features → footer) | `4xl` (`py-4xl` / `mt-4xl`) |
| **Inside a section, between its sub-blocks** | A section's heading block → its content grid; a card's header → its body | `2xl` (`p-2xl` / `gap-2xl` / `mt-2xl`) |
| **Between sibling elements/components** | Cards in a grid, stacked form fields, list items, rows in a stack | `lg` (`gap-lg` / `mt-lg` / `mb-lg` / `p-lg` / `space-y-lg`) |
| **Within one component, between paragraphs/lines of running text** | Paragraph-to-paragraph inside a card body, label → helper text | `md` (`gap-md` / `mt-md` / `mb-md` / `space-y-md`) |
| **Within one component, compact internal padding/gaps** | Dense table cells, a compact card's own padding, tightly-packed toolbar groups | `sm` (`gap-sm` / `p-sm` / `px-sm` / `py-sm` / `space-y-sm`) |
| **Between small inline items on one line/row** | Icon + label, badge + adjacent text, breadcrumb segments | `xs` (`gap-xs` / `space-x-xs`) |
| **Tightest — within a tiny cluster** | Icon↔label inside a button, chip-internal gaps, dense list bullets | `2xs` (`gap-2xs` / `space-x-2xs` / `space-y-2xs`) |

When in doubt, pick the tier one level *tighter* than you'd guess — the tune's own strength setting (`data-tune-strength`) already amplifies the gap on airier tunes, so authoring at the "natural" density and letting the tune stretch it reads better than pre-inflating the choice.

### Enforcing the rule — `ui:lint` (universal) + automation adapters

The rule is machine-checkable. **`php artisan ui:lint [paths…] [--json]`** is the **universal interface** — pure PHP (no Laravel needed at the core), exits non-zero on violations. It flags excluded daisyUI component classes, fixed/hex colors (ignore `data-theme`), and a root `<html>` missing **`data-theme` / `data-tune`** (the theme × tune cascade root). **Any** CLI agent (Claude Code, Cursor, aider, …), CI, or human runs the same command — so there is **no per-agent adapter to maintain**; agents that want feedback simply call `ui:lint --json` and read the result. Add `--spacing` for the informational rhythmic-vs-optical spacing census (never affects the exit code; in `--json` it appears as a `spacing` key).

Three ways to automate it (pick any; all call the one command):

| Automation | Scope | Install |
|---|---|---|
| **CI / manual** | any | `php artisan ui:lint` (non-zero exit fails the job) |
| **git pre-commit** | **agent-agnostic** — human, CI, any agent | `php artisan ui:install --git-hook` (blocks a commit whose staged Blade violates; never clobbers an existing hook) |
| **Claude Code PostToolUse** | Claude Code (smooth in-edit feedback) | `ui:install` installs it by default (`--skip-hooks` to opt out) — runs `ui:lint` on each edited Blade and feeds violations back into the model's context |

Suppress an intentional exception with a `pinion-lint-ignore` comment on the line (or the line above).

## Theme lineup & selection guide (v0.7.0)

pinion-ui ships **only original themes**. daisyUI's built-in themes are disabled (`themes: false`) — setting `data-theme="dracula"` or `"light"` does nothing. The lineup is 37 themes, each a **light/dark pair**:

- **Naming**: `<name>` = light, `<name>-dark` = dark (`payments` / `payments-dark`), and **all names are bare** — the v0.6 `mood-` prefix was dropped in v0.7.0 (`mood-zen` → `zen`, …; `mood-synthwave` became `outrun` to avoid colliding with daisyUI's `synthwave`). A theme's category (Brand / Mood / SaaS / Industry) is metadata: it lives in `lineup.json`, in the Group column below, and as the grouped headings + category chip in `<x-theme-tune-switcher>` — it is no longer encoded in the name.
- **Default**: `pinion` applies at `:root` automatically when no `data-theme` is set; if the OS prefers dark (`prefers-color-scheme: dark`), `pinion-dark` applies instead (it carries daisyUI's `prefersdark` flag). An explicit `data-theme` on any element always wins over both. To pin light regardless of OS setting, set `data-theme="pinion"` explicitly.
- **Page canvas vs component face**: every theme paints the page background (`:root`) with its tinted canvas color and puts components on `base-100` (white in light themes) — do **not** hardcode a body background. `bg-base-200` equals the canvas color (recessed wells, hover); `border-base-300` is the matching border tone.
- **`reactive`** — one extra opt-in, light-only theme (GitHub-Light-adjacent, for report tooling). Not part of the pairs. As of v0.6.1 the brand default `pinion` shares this palette family, so `reactive` mostly matters for the report tooling that hardcodes its name.
- **Switchers**: `<x-theme-switcher>` cycles `pinion` ↔ `pinion-dark` by default; `<x-theme-tune-switcher>` shows the whole grouped lineup with a light/dark mode toggle.

### Picking a theme (for AI agents scaffolding an app)

Match the app's domain/vibe against the trigger column; when nothing clearly matches, use `pinion`. Wire it as `<html data-theme="{theme}" data-tune="{tune}">` and offer dark mode with `{theme}-dark`.

| Theme (light / dark) | Group | Use when the app is… |
|---|---|---|
| `pinion` / `pinion-dark` | Brand | **The default — anything without a clearer match.** The pinion-ui face: clean white technical-document palette (GitHub-Primer-adjacent blue / green / purple; near-black ink). |
| `monokai` / `-dark` | Mood | Editor-like or developer-facing UI（エディタ系・開発者向け） |
| `outrun` / `-dark` | Mood | Neon, gaming, events（ネオン・ゲーム・イベント） |
| `vapor` / `-dark` | Mood | Soft retro, Gen-Z products（ソフトなレトロ・Z世代向け） |
| `bigblue` / `-dark` | Mood | Buttoned-up enterprise / B2B（エンタープライズ・B2B 堅め） |
| `neotokyo` / `-dark` | Mood | Japanese × cyber, nightlife（和×サイバー・ナイトライフ） |
| `zen` / `-dark` | Mood | Wabi-sabi, craft, ryokan, minimalist taste（和風・工芸・旅館） |
| `botanical` / `-dark` | Mood | Flowers, gardening, organic, cosmetics（花・園芸・オーガニック・コスメ） |
| `pop` / `-dark` | Mood | Entertainment, youth, campaign LPs（エンタメ・若年層・キャンペーン LP） |
| `verdigris` / `-dark` | Mood | Warm ivory × retro teal-green; calm, warm products（ウォームアイボリー×レトログリーン。旧ブランド既定） |
| `payments` / `-dark` | SaaS | Payments, fintech（決済・フィンテック） |
| `docs` / `-dark` | SaaS | Documentation, knowledge base（ドキュメント・ナレッジベース） |
| `mono` / `-dark` | SaaS | Minimal tools, portfolios（ミニマルツール・ポートフォリオ） |
| `ops` / `-dark` | SaaS | PM, operations, internal tools（PM・運用・社内ツール） |
| `finance` / `-dark` | SaaS | Finance / legal enterprise（金融・法務エンタープライズ） |
| `people` / `-dark` | SaaS | HR, community（HR・コミュニティ） |
| `health` / `-dark` | SaaS | Medical / wellness SaaS（医療・ウェルネス SaaS） |
| `analytics` / `-dark` | SaaS | BI, data visualization（BI・データ可視化） |
| `devtool` / `-dark` | SaaS | Developer tools, API products（開発者ツール・API 系） |
| `comms` / `-dark` | SaaS | Chat, collaboration（チャット・コラボレーション） |
| `growth` / `-dark` | SaaS | Marketing, growth, landing pages（マーケ・グロース・LP 系） |
| `commerce` / `-dark` | Industry | EC, retail, marketplaces（EC・リテール・マーケットプレイス） |
| `education` / `-dark` | Industry | EdTech, learning services（EdTech・学習サービス） |
| `legal` / `-dark` | Industry | Legal, professional services, contracts（法務・士業・契約管理） |
| `logistics` / `-dark` | Industry | Logistics, delivery, mobility（物流・配送・モビリティ） |
| `media` / `-dark` | Industry | Media, publishing, news（メディア・出版・ニュース） |
| `security` / `-dark` | Industry | Security, audit, auth（セキュリティ・監査・認証） |
| `kids` / `-dark` | Industry | Kids / family products（子ども・ファミリー向け） |
| `wellness` / `-dark` | Industry | Yoga, meditation, mental care（ヨガ・瞑想・メンタルケア） |
| `civic` / `-dark` | Industry | Government, public sector（行政・公共・自治体） |
| `atelier` / `-dark` | Industry | Luxury brands, D2C, jewelry（高級ブランド・D2C・ジュエリー） |
| `estate` / `-dark` | Industry | Real estate, architecture（不動産・建築・設計） |
| `food` / `-dark` | Industry | Food, delivery（飲食・フードデリバリー） |
| `travel` / `-dark` | Industry | Travel, hotels, resorts（旅行・ホテル・リゾート） |
| `creative` / `-dark` | Industry | Creative / design tools（クリエイティブツール・デザイン） |
| `agri` / `-dark` | Industry | Agriculture, greentech（農業・植物工場・グリーンテック） |
| `factory` / `-dark` | Industry | Manufacturing, heavy industry（製造・重工業・設備） |

Canonical machine-readable source: `src/resources/themes/lineup.json` (name, category, trigger, full palettes) — the same file that generates the theme CSS, so it can never drift. In PHP, `pn_theme_groups()` returns the grouped light/dark ids.

## daisyUI v5 gotchas (verified — do not "fix")

- **daisyUI component classes AND built-in themes don't exist in your build.** The pinion-ui preset loads daisyUI with `themes: false` + a full component exclude list: you get the color utility layer (`bg-primary`, `text-base-content`, `data-theme` switching) but `.btn`, `.card`, `.alert`, `.input`, `.menu`, `.modal`, etc. produce **no styling**, and daisyUI stock theme names (`light`, `dark`, `dracula`, …) set as `data-theme` render **unthemed** — only the pinion-ui lineup (previous section) exists. Never write daisyUI component markup (`<button class="btn btn-primary">`) — use the pinion-ui component (`<x-button color="primary">`). Do not "fix" this by adding `@plugin "daisyui"` to app.css; that re-enables everything and breaks the design boundary.
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
