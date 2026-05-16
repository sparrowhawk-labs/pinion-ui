## pinion-ui (AI agents) — Laravel Blade adapter

**Stack**: Laravel Blade + Tailwind v4 + daisyUI v5 + Alpine.js. (Vanilla / React / Vue / Web Components adapters are planned as separate NPM packages for v0.5+; each will ship its own `AGENTS.md` so the consumer's LLM reads one stack adapter at a time, not a multi-stack mega-doc.)

Before writing pinion-ui code, read **`vendor/sparrowhawk-labs/pinion-ui/AGENTS.md`** — it lists calling conventions (`<x-button>` anonymous / `<x-pn::button>` namespaced), the Compose-pattern invariants, daisyUI gotchas (verified, do not "fix"), and the lookup workflow for per-component reference docs.

Per-component API: `vendor/sparrowhawk-labs/pinion-ui/reference/components/{name}.md` (props, slots, examples, class composition). Category-grouped index at `reference/components/index.md`.
