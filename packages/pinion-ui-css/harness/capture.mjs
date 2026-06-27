/* ============================================================================
   capture.mjs — render the fixture under every theme×tune×strength and record
   the computed style of each probe's targeted properties.
   ============================================================================
   Output: a golden JSON { meta, data: { "theme|tune|strength": { probeId:
   { prop: value } } } }. The reference golden (from build/reference.css) is the
   baseline; a future plain-dist build is captured the same way and diffed
   against it (diff.mjs). Setting the data-* attributes then reading
   getComputedStyle in one evaluate() forces a style flush, so values are
   always up to date for the active combo.

   Usage: node harness/capture.mjs [--css <path>] [--out <path>] [--label <s>]
   ============================================================================ */
import { chromium } from 'playwright';
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join, resolve } from 'node:path';
import { PROBES, combos } from './probes.mjs';

const here = dirname(fileURLToPath(import.meta.url));
const root = join(here, '..');

function arg(name, def) {
  const i = process.argv.indexOf(`--${name}`);
  return i >= 0 ? process.argv[i + 1] : def;
}
const cssPath = resolve(root, arg('css', 'build/reference.css'));
const outPath = resolve(root, arg('out', 'harness/golden/reference.json'));
const label = arg('label', 'reference');

const css = readFileSync(cssPath, 'utf8');
const fixture = readFileSync(join(here, 'fixture.html'), 'utf8');
const html = fixture.replace('</head>', `<style>${css}</style></head>`);

const probeMeta = PROBES.map((p) => ({ id: p.id, capture: p.capture }));
const allCombos = combos();

const browser = await chromium.launch();
const page = await browser.newPage();
await page.setContent(html, { waitUntil: 'load' });

const data = {};
for (const c of allCombos) {
  data[c.key] = await page.evaluate(
    ({ theme, tune, strength, probeMeta }) => {
      const h = document.documentElement;
      h.setAttribute('data-theme', theme);
      h.setAttribute('data-tune', tune);
      h.setAttribute('data-tune-strength', strength);
      const out = {};
      for (const { id, capture } of probeMeta) {
        const el = document.querySelector(`[data-cap="${id}"]`);
        if (!el) { out[id] = { __missing: true }; continue; }
        const cs = getComputedStyle(el);
        const vals = {};
        for (const prop of capture) vals[prop] = cs.getPropertyValue(prop).trim();
        out[id] = vals;
      }
      return out;
    },
    { theme: c.theme, tune: c.tune, strength: c.strength, probeMeta },
  );
}

await browser.close();

mkdirSync(dirname(outPath), { recursive: true });
const result = {
  meta: { label, css: cssPath, probes: PROBES.length, combos: allCombos.length },
  data,
};
writeFileSync(outPath, JSON.stringify(result));
console.log(`captured ${label}: ${allCombos.length} combos × ${PROBES.length} probes → ${outPath}`);
