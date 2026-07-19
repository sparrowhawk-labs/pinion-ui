## pinion-ui (AI agents) — Laravel Blade adapter

**Stack**: Laravel Blade + Tailwind v4 + daisyUI v5 + Alpine.js. (Vanilla / React / Vue / Web Components adapters are planned as separate NPM packages for v0.5+; each will ship its own `AGENTS.md` so the consumer's LLM reads one stack adapter at a time, not a multi-stack mega-doc.)

Before writing pinion-ui code, read **`vendor/sparrowhawk-labs/pinion-ui/AGENTS.md`** — it lists calling conventions (`<x-button>` anonymous / `<x-pn::button>` namespaced), the Compose-pattern invariants, daisyUI gotchas (verified, do not "fix"), and the lookup workflow for per-component reference docs.

**Class vocabulary — the rule that always applies.** Build UI from pinion-ui components (`<x-button>`, `<x-card>`, `<x-modal>`, …) first. When you write class strings, they are **plain Tailwind v4 by default, with exactly two exceptions:**

1. **Color → daisyUI semantic color classes** — `bg-primary` `text-primary-content` `bg-base-200` `text-base-content` `border-base-300` `text-error` `bg-success/10` (these track `data-theme`).
2. **Shape · border · component sizing that must follow the tune → pinion-ui tune classes & tokens** — `tune-border` `tune-btn-{sm,md,lg}` `tune-card-pad` `tune-input-*`, and tokens `rounded-[var(--radius-box)]` `[var(--space-*)]` `[var(--h-field-md)]` (these track `data-tune`).

**Never daisyUI _component_ classes** (`.btn` `.card` `.badge` `.input` `.alert` `.menu` …) — excluded from the build, they produce no styling. **Never fixed palette/hex colors** (`bg-blue-500`, `#1d4ed8`) — they ignore `data-theme`. Full rule + examples: AGENTS.md → "Class vocabulary".

**Themes**: pinion-ui ships 37 original themes as `<name>` (light) / `<name>-dark` pairs — daisyUI's stock themes (`light`, `dark`, `dracula`, …) do **not** exist in the build. Default is `pinion`. When scaffolding a page, pick the theme whose domain matches the app (e.g. `payments`, `monokai`, `atelier`) via **AGENTS.md → "Theme lineup & selection guide"** and set `<html data-theme="…" data-tune="…">`.

This block is a *summary*. Anything not stated here is authoritative in **AGENTS.md → "Class vocabulary"** — read it when the summary doesn't cover your case. Enforcement backstop: **`php artisan ui:lint`** flags excluded daisyUI component classes and fixed/hex colors and exits non-zero, so it gates CI / pre-commit / a PostToolUse hook.

Per-component API: `vendor/sparrowhawk-labs/pinion-ui/reference/components/{name}.md` (props, slots, examples, class composition). Category-grouped index at `reference/components/index.md`.
