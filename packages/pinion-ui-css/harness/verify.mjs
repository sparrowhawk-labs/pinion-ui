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
import { TUNE_UTILITIES } from './probes.mjs';

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
  ['theme: pinion', css.includes('[data-theme="pinion"]')],
  ['theme: reactive', css.includes('[data-theme="reactive"]')],
  ['font: PixelMplus10', css.includes('PixelMplus10')],
  ['font: PixelMplus12', css.includes('PixelMplus12')],
  ['@font-face present', css.includes('@font-face')],
];

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
