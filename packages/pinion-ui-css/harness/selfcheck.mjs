/* ============================================================================
   selfcheck.mjs — prove the captured golden is COMPLETE and the harness is
   SENSITIVE (not blind). Run against the reference golden after capture.
   ============================================================================
   Three gates:
     completeness — every probe resolved (no querySelector miss) and no empty
                    resolved value (an empty string means a var() didn't resolve).
     sensitivity  — a token that MUST differ across tunes actually does, so we
                    know the harness can detect a parity break (a blind harness
                    that returned constants would diff clean while hiding bugs).
     container-scale guard — width-family named sizes (max-w-6xl, w-md, …)
                    stay on Tailwind's default --container-* values in every
                    combo, i.e. the @theme --spacing-<size> keys are not
                    shadowing them (see CONTAINER_GUARD in probes.mjs).

   Usage: node harness/selfcheck.mjs [golden.json]
   ============================================================================ */
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join, resolve } from 'node:path';
import { CONTAINER_GUARD, WIDTH_FAMILY } from './probes.mjs';

const here = dirname(fileURLToPath(import.meta.url));
const root = join(here, '..');
const path = resolve(root, process.argv[2] || 'harness/golden/reference.json');
const g = JSON.parse(readFileSync(path, 'utf8'));

let ok = true;
const fail = (m) => { console.error(`✗ ${m}`); ok = false; };

/* --- completeness --- */
let missing = 0, empty = 0;
for (const key of Object.keys(g.data)) {
  for (const id of Object.keys(g.data[key])) {
    const v = g.data[key][id];
    if (v.__missing) { missing++; continue; }
    for (const prop of Object.keys(v)) {
      if (v[prop] === '' || v[prop] == null) empty++;
    }
  }
}
if (missing) fail(`${missing} probe(s) not found in the DOM`);
if (empty) fail(`${empty} empty resolved value(s) — a var() did not resolve`);
console.log(`${missing || empty ? '✗' : '✓'} completeness: missing=${missing} empty=${empty}`);

/* --- sensitivity --- */
function distinctAcrossTunes(theme, strength, id, prop) {
  const vals = new Set();
  for (const key of Object.keys(g.data)) {
    const [t, , s] = key.split('|');
    if (t === theme && s === strength) vals.add(g.data[key]?.[id]?.[prop]);
  }
  return vals;
}
/* radius and font are guaranteed to differ across the 11 tunes (sharp r=0 vs
   soft/luxury large; pixel font vs editorial serif). Treat these as HARD. */
const HARD = [['radius-box', 'border-top-left-radius'], ['font-heading', 'font-family']];
const SOFT = [['shadow-box', 'box-shadow'], ['py-4xl', 'padding-top'], ['tune-btn-md', 'height']];

for (const [id, prop] of HARD) {
  const n = distinctAcrossTunes('pinion', 'xl', id, prop).size;
  if (n < 2) fail(`sensitivity(HARD): ${id}.${prop} has ${n} distinct value across tunes — harness may be blind`);
  console.log(`${n < 2 ? '✗' : '✓'} sensitivity: ${id}.${prop} → ${n} distinct across 11 tunes`);
}
for (const [id, prop] of SOFT) {
  const n = distinctAcrossTunes('pinion', 'xl', id, prop).size;
  console.log(`  · ${id}.${prop} → ${n} distinct across 11 tunes`);
}

/* --- container-scale guard (host-app view) ---
   The width-family utilities (w/min-w/max-w/basis + named size) must resolve
   to Tailwind's DEFAULT --container-* values in every combo — never to the
   @theme --spacing-<size> keys that share the same key names. A mismatch here
   means the spacing keys are shadowing the container scale again (max-w-6xl
   collapsing 72rem → 8rem broke real host apps). Expected values come from
   the per-size CONTROL probe (literal container rem inline) so tunes that
   scale the page font-size (minimal/pixel) compare rem-to-rem, not to a
   hardcoded px. Spec: probes.mjs CONTAINER_GUARD. */
let guardBad = 0;
for (const { size } of CONTAINER_GUARD) {
  for (const [prefix, prop] of WIDTH_FAMILY) {
    for (const key of Object.keys(g.data)) {
      const expected = g.data[key]?.[`ctrl-container-${size}`]?.[prop];
      const v = g.data[key]?.[`${prefix}-${size}`]?.[prop];
      if (v !== expected) {
        if (guardBad < 8) console.error(`  ✗ [${key}] ${prefix}-${size}.${prop} = ${JSON.stringify(v)} (expected ${JSON.stringify(expected)} from control)`);
        guardBad++;
      }
    }
  }
}
if (guardBad) fail(`container-scale guard: ${guardBad} value(s) off the default container scale — --spacing-<size> is shadowing width-family utilities`);
console.log(`${guardBad ? '✗' : '✓'} container-scale guard: width-family named sizes pinned to Tailwind defaults`);

process.exit(ok ? 0 : 1);
