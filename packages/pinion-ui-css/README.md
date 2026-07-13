# @sparrowhawk-labs/pinion-ui-css (v0.5.0 — in development)

Standalone **theme × tune** CSS for pinion-ui: plain pre-compiled `dist/*.css` that
React / Vue / Astro / vanilla consumers import with zero build tooling, generated
from the SAME `src/resources/css/*` the Blade preset uses (so it can't drift).

> **Status: Phase 1 SHIPPED (2026-07-13).** `dist/pinion-ui.css` exists and passes
> the golden gate: **27,170 computed values (110 combos × 122 probes) identical**
> to the Blade reference build, with 91/91 tune utilities surviving purge, both
> theme blocks, self-hosted PixelMplus, no preflight, no daisyUI built-in themes.
> Design is LOCKED in the repo `CLAUDE.md` ("v0.5.0 design — LOCKED 2026-06-25").
>
> - Rebuild + re-gate: `npm run dist` (after `npm run ref` if source CSS changed)
> - `dist/fonts.css` = opt-in Google Fonts loader (core self-hosts only PixelMplus)
> - `harness/eject-table.mjs` regenerates `src/resources/eject-table.json` for
>   `ui:eject` — rerun whenever tune.css or theme colors change
> - **npm publish is pending**: claim the `@sparrowhawk-labs` npm org, `npm login`,
>   flip `"private": true` off, then `npm publish --access public`.

## The harness (Phase 0)

**North star:** the future plain-`dist` CSS must produce **computed-style-identical**
theme × tune output to the current Blade build. The harness measures that.

```
src/reference.entry.css   the REFERENCE build = the current Blade preset, as
                          consumers receive it (imports the real src CSS, WITH
                          Tailwind preflight). Its computed-style snapshot is the
                          golden baseline.
harness/probes.mjs        single source of truth: 98 probes (73 tune @utility +
                          20 theme colours + 5 token probes), each capturing ONLY
                          the property it sets. THEMES × TUNES × STRENGTHS lists.
harness/gen-fixture.mjs   emits fixture.html (the shared @source purge-guard AND
                          the DOM the browser renders). Generated, never hand-edited.
harness/verify.mjs        purge-guard gate: all 73 utilities + both themes + the
                          PixelMplus @font-face survived the build.
harness/capture.mjs       renders the fixture under every theme×tune×strength,
                          reads getComputedStyle → golden JSON.
harness/diff.mjs          compares two goldens (reference vs candidate). The gate.
harness/selfcheck.mjs     completeness (nothing missing/empty) + sensitivity
                          (tokens vary across tunes → the harness isn't blind).
```

### Run

```bash
npm install
npx playwright install chromium      # one-time browser download
npm run ref                          # gen → build → verify → capture → selfcheck
```

`npm run ref` regenerates the reference golden at `harness/golden/reference.json`.
Once a candidate `dist` build exists (Phase 1+):

```bash
node harness/capture.mjs --css dist/pinion-ui.css --label dist --out harness/golden/dist.json
node harness/diff.mjs harness/golden/reference.json harness/golden/dist.json   # must be 0
```

### Phase 0 result (verified)

- 110 combos × 98 probes = **21,010 computed values** captured.
- **Determinism:** re-capture diffs to **0** (no non-determinism → no false positives).
- **Completeness:** 0 missing probes, 0 unresolved `var()`s.
- **Sensitivity:** radius/font/shadow/space/size tokens each take 5–10 distinct
  values across the 11 tunes → a real parity break WILL surface (no false negatives).

## Two load-bearing design notes

1. **Capture only the property each probe targets.** The plain-dist build ships
   WITHOUT Tailwind preflight; the reference ships WITH it. Reading only the
   targeted property keeps the diff about the tune/theme token, immune to
   preflight-vs-UA differences on unrelated properties.
2. **Every probe forces `box-sizing:border-box` inline.** Preflight sets border-box
   on `*`; a no-preflight build defaults to content-box, which would flip computed
   `height` on padded probes. Inline wins everywhere → confound removed.

Both are why the diff can be trusted to report *only* genuine theme×tune drift.
