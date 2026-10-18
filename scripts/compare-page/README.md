# compare-page — before / after grid for theme-wide visual changes

A maintainer tool for changes that touch every theme at once (ink routing,
rims on filled faces, fills that must read as a line, …). It renders one page:
**theme sections (light / dark pairs) × 8 semantic colors × component rows**,
every cell stacking the OLD markup above the NEW markup, with a tune switcher
and a ×2 zoom so a 1px rim is visible. The classes in the NEW cells come from
*this checkout* (`src/Compose/*Composer.php`, the Blade variant maps), so the
page always shows what the package ships.

```
scripts/compare-page/
├── gen.php        spec JSON → index.html
├── build.sh       gen.php + Tailwind CLI → index.html + page.css
├── shots.sh       ×2 screenshots of chosen theme sections (playwright-cli)
├── index.php      specs/*.json `meta` → the index page (Markdown + visualize-build)
└── specs/         the pages built so far (regression inputs + examples)
    ├── ink-all.json         v0.23.0 — meta only (page predates this tool; not a build input)
    ├── solid-edge.json      v0.23.1 — solid faces lose their ink rim
    ├── ink-rest.json        v0.23.2 — ink fills, hovered outline / wire, wire checked
    ├── wire-outline.json    v0.23.3 — checked wire keeps its outline (live Composer)
    ├── toggle-neutral.json  v0.24.1 — wire toggle neutral → base-content (live Composer)
    ├── toggle-solid-soft.json v0.25.1 — solid / soft toggle neutral → base-content (live Composer)
    └── signal-toggle.json   候補 — achromatic `signal`: keep / ink fill / theme hex (+ .extra.css)
```

## Usage

```bash
# 1. page (index.html + page.css) into any directory
scripts/compare-page/build.sh scripts/compare-page/specs/wire-outline.json ~/out/pinion-wire-outline

# 2. optional ×2 evidence shots of the themes where ink ≠ color
scripts/compare-page/shots.sh ~/out/pinion-wire-outline pop pop-dark kids signal-dark
scripts/compare-page/shots.sh ~/out/pinion-wire-outline vapor --tune brutal

# 3. regenerate the index of all pages (~/claude/docs/visualized/pinion-compare-index.html)
php scripts/compare-page/index.php --write --rebuild
```

`index.php` reads the `meta` block of every spec and writes
`~/claude/docs/visualized/src/pinion-compare-index.md` (`--out` / `--pages` /
`--html` override the paths; without `--write` it prints the Markdown). Rows are
ordered 候補 first, then newest version. A spec without `meta` is skipped with a
warning; `name` > 32 or `compared` > 64 characters fails the run (content
contract). Never add a row to the Markdown by hand.

Requirements: `php` ≥ 8.1, `node_modules` in `packages/pinion-ui-css`
(`npm install` there). In a git worktree without node_modules, point
`PINION_NODE_MODULES=/path/to/main/packages/pinion-ui-css/node_modules`.
`shots.sh` needs `playwright-cli` (`PLAYWRIGHT_CLI=` to override the binary); it
serves the directory over a local http server because headless Chromium will not
load `file://` pages, and refuses to shoot a page that did not load.

`build.sh` writes a temporary `.compare-page.entry.css` at the repo root and,
when the root has no `node_modules`, a symlink to the package's — Tailwind
resolves `@plugin "daisyui"` against the entry file's directory. Both are removed
on exit, so `git status` stays clean.

## Spec format

```jsonc
{
  "meta": {                                       // index.php only — see above
    "name": "toggle wire の neutral",             // ≤ 32 chars, the link text
    "version": "v0.24.1",                         // or "候補" for an undecided comparison
    "compared": "wire の neutral を base-content に", // ≤ 64 chars
    "page": "pinion-toggle-neutral",              // output dir under ~/claude/docs/visualized
    "scale": "…"                                  // optional; default = themes × colors × rows + shots
  },
  "extra_css": "signal-toggle.extra.css",         // optional: copied to <out>/extra.css, linked
                                                  //   after page.css — candidate theme blocks
                                                  //   (`[data-theme=signal-x3]`) the preset lacks
  "title":   "<title> of the page",
  "heading": "bold text in the sticky bar",
  "old": "旧 v0.23.2", "new": "新 v0.23.3",       // column sub-headers
  "intro": ["<b>What to look at</b> — …", "…"],   // one <p> each (HTML)
  "themes": [["pinion", "brand · light"], ["pop", "yellow primary · light"], …],
  "colors": ["primary", …, "error"],              // default: all eight
  "tunes":  [["default", "default"], ["brutal", "brutal"], ["pixel", "pixel"]],
  "tone_neutral": "base-content",                 // what {tone} is for neutral (page default)
  "rewrite": [["peer-checked:border-{tone}", "peer-checked:border-transparent"]],
  "rows": [ … ]
}
```

Each row is one component state. Placeholders expanded in every string:
`{c}` the color, `{tone}` the color or the row's / page's `tone_neutral` for
neutral, `{C}` capitalised, `{id}` a unique id (same value for `id` / `for`
inside one cell variant).

| key | what it does |
|---|---|
| `label` | row header |
| `html` | NEW markup template |
| `compose` | `{"class": "Checkbox", "props": {"appearance": "wire", "color": "{c}"}}` — the `compose()` result keys are inserted into `html` as `{{visualBox}}`, `{{track}}`, … (the real classes) |
| `blade` | `{"file": "button", "key": "solid-{c}"}` — the `'key' => '…'` string from that component's variant map, inserted as `{{variant}}` |
| `rewrite` | NEW → OLD string pairs for this row (overrides the page's). `[]` = unchanged row (reference) |
| `old` | explicit OLD template instead of rewriting |
| `html_by_color` / `old_by_color` | per-color template override, e.g. `{"neutral": "…"}` when the component writes neutral differently |
| `only` | colors that have this component (others render "—") |
| `tone_neutral` | row-level override of the page's |

The OLD cell is the NEW cell with the `rewrite` pairs applied, so a page needs
only the NEW markup plus the one or two class swaps the release makes. Snapshot
pages (`ink-rest.json`) keep literal templates because the live Composer has
since moved on; live pages (`wire-outline.json`) use `compose` so they track the
code.

## Reading the page

- Sticky bar: tune buttons re-render the whole page under that tune (brutal =
  thick borders, pixel = dot fonts); **×2** toggles `zoom: 2` on `<main>`.
- Differences only show on themes where ink ≠ color: `pop` light (1.6:1 yellow
  primary), every dark theme's `neutral`, a light `warning`. On `pinion` light
  most cells look identical by design.
- Hover rows apply the hover classes flat (no pointer needed).
