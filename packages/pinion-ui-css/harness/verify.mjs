/* ============================================================================
   verify.mjs — Phase 0 purge-guard gate.
   ============================================================================
   Asserts that the reference build kept the WHOLE theme×tune surface the
   harness needs to observe: all tune @utility classes (none JIT-purged), both
   theme blocks, and the self-hosted PixelMplus @font-face set. Exits non-zero
   on any miss so it can gate CI. Run AFTER `npm run build:ref`.
   ============================================================================ */
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { TUNE_UTILITIES, THEMES } from './probes.mjs';

const here = dirname(fileURLToPath(import.meta.url));
const cssPath = join(here, '..', 'build', 'reference.css');

let css;
try {
  css = readFileSync(cssPath, 'utf8');
} catch {
  console.error(`✗ ${cssPath} not found — run \`npm run build:ref\` first.`);
  process.exit(2);
}

const utilities = [...new Set(TUNE_UTILITIES)];
const missing = utilities.filter((u) => !css.includes(`.${u}`));

const structural = [
  /* THEMES from probes.mjs — the representative sweep; verifies the lineup
     blocks (light + dark + a non-brand pair member) AND reactive survived. */
  ...THEMES.map((t) => [`theme: ${t}`, css.includes(`[data-theme="${t}"]`) || css.includes(`[data-theme=${t}]`)]),
  ['font: PixelMplus10', css.includes('PixelMplus10')],
  ['font: PixelMplus12 dropped (v0.11.0 unification)', !css.includes('PixelMplus12')],
  ['@font-face present', css.includes('@font-face')],
];

/* --- tune-fonts.json manifest ⇄ tune-core.css cross-check (v0.11.0) ---
   The manifest is what consumers use to build per-tune Google Fonts links,
   so every webfont family named in a --td-font-* stack must be declared in
   it, and vice versa (a manifest family no stack references is dead weight
   or a stale rename). Generic keywords / system stacks are not webfonts. */
const manifest = JSON.parse(readFileSync(
  join(here, '..', '..', '..', 'src', 'resources', 'tune-fonts.json'), 'utf8'));
const GENERIC = new Set(['SFMono-Regular']); // quoted but never a webfont
const cssFamilies = new Set(
  [...css.matchAll(/--td-font-(?:heading|body|mono):\s*([^;]+);/g)]
    .flatMap(([, stack]) => [...stack.matchAll(/"([^"]+)"/g)].map((m) => m[1]))
    .filter((f) => !GENERIC.has(f)),
);
const manifestFamilies = new Set(Object.keys(manifest.families));
const notInManifest = [...cssFamilies].filter((f) => !manifestFamilies.has(f));
const notInCss = [...manifestFamilies].filter((f) => !cssFamilies.has(f));
structural.push(
  [`tune-fonts.json covers all CSS families${notInManifest.length ? ` (missing: ${notInManifest.join(', ')})` : ''}`, notInManifest.length === 0],
  [`tune-fonts.json has no stale families${notInCss.length ? ` (stale: ${notInCss.join(', ')})` : ''}`, notInCss.length === 0],
);

let ok = true;

console.log(`tune utilities: ${utilities.length - missing.length}/${utilities.length} present`);
if (missing.length) {
  ok = false;
  console.error(`✗ MISSING utilities (purged): ${missing.join(', ')}`);
}

for (const [label, pass] of structural) {
  console.log(`${pass ? '✓' : '✗'} ${label}`);
  if (!pass) ok = false;
}

if (!ok) {
  console.error('\n✗ purge-guard gate FAILED');
  process.exit(1);
}
console.log('\n✓ purge-guard gate passed');
