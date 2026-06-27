/* ============================================================================
   diff.mjs — compare two golden computed-style snapshots.
   ============================================================================
   The core Phase 0+ gate: capture the reference (Blade build) and a candidate
   (plain-dist build), then assert ZERO computed-style differences across the
   whole theme×tune×strength surface. Any mismatch is a parity break.

   Usage: node harness/diff.mjs <reference.json> <candidate.json> [--max N]
   Exit 0 = identical, 1 = differences found, 2 = bad input.
   ============================================================================ */
import { readFileSync } from 'node:fs';

const [, , refPath, candPath] = process.argv;
if (!refPath || !candPath) {
  console.error('usage: node harness/diff.mjs <reference.json> <candidate.json> [--max N]');
  process.exit(2);
}
const maxShow = (() => {
  const i = process.argv.indexOf('--max');
  return i >= 0 ? Number(process.argv[i + 1]) : 40;
})();

const ref = JSON.parse(readFileSync(refPath, 'utf8'));
const cand = JSON.parse(readFileSync(candPath, 'utf8'));

const diffs = [];
let compared = 0;
for (const key of Object.keys(ref.data)) {
  const rp = ref.data[key];
  const cp = cand.data?.[key] ?? {};
  for (const id of Object.keys(rp)) {
    const rv = rp[id];
    const cv = cp[id] ?? {};
    for (const prop of Object.keys(rv)) {
      compared++;
      if (rv[prop] !== cv[prop]) {
        diffs.push({ key, id, prop, ref: rv[prop], cand: cv[prop] });
      }
    }
  }
}

console.log(`compared ${compared} values across ${Object.keys(ref.data).length} combos`);
if (diffs.length === 0) {
  console.log('✓ identical — zero computed-style differences');
  process.exit(0);
}

console.error(`✗ ${diffs.length} difference(s):`);
for (const d of diffs.slice(0, maxShow)) {
  console.error(`  [${d.key}] ${d.id}.${d.prop}: ref=${JSON.stringify(d.ref)} cand=${JSON.stringify(d.cand)}`);
}
if (diffs.length > maxShow) console.error(`  … and ${diffs.length - maxShow} more`);
process.exit(1);
