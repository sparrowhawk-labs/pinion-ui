/* ============================================================================
   selfcheck.mjs — prove the captured golden is COMPLETE and the harness is
   SENSITIVE (not blind). Run against the reference golden after capture.
   ============================================================================
   Two gates:
     completeness — every probe resolved (no querySelector miss) and no empty
                    resolved value (an empty string means a var() didn't resolve).
     sensitivity  — a token that MUST differ across tunes actually does, so we
                    know the harness can detect a parity break (a blind harness
                    that returned constants would diff clean while hiding bugs).

   Usage: node harness/selfcheck.mjs [golden.json]
   ============================================================================ */
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join, resolve } from 'node:path';

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

process.exit(ok ? 0 : 1);
