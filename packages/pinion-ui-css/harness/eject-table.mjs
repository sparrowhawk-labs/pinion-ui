/* ============================================================================
   eject-table.mjs — capture the tune/theme TOKEN values that back `ui:eject`.
   ============================================================================
   `ui:eject` freezes a theme×tune rendering into vanilla Tailwind classes.
   Instead of re-implementing tune.css's base+delta×strength cascade in PHP
   (fragile), the token values are measured here in a real browser — same
   trust anchor as the golden harness — and baked into the package as
   src/resources/eject-table.json. Regenerate whenever tune.css or the theme
   definitions change:

     cd packages/pinion-ui-css && npm run build:ref && node harness/eject-table.mjs

   Probes are inline styles referencing the tokens directly (height:
   var(--h-field-md), …) — no utility classes, so JIT purge is irrelevant.
   Colors are normalized to 8-bit hex via canvas (browser does the oklch→sRGB
   conversion; 1/255 rounding is invisible and gives Tailwind-idiomatic
   arbitrary values).

   Tune tokens are captured under data-theme="pinion" and asserted
   theme-invariant against "reactive" on sample combos — shape/space must
   never depend on the theme axis (architecture invariant: axes stay
   orthogonal).
   ============================================================================ */
import { chromium } from 'playwright';
import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join, resolve } from 'node:path';

const here = dirname(fileURLToPath(import.meta.url));
const root = join(here, '..');

function arg(name, def) {
  const i = process.argv.indexOf(`--${name}`);
  return i >= 0 ? process.argv[i + 1] : def;
}
const cssPath = resolve(root, arg('css', 'build/reference.css'));
const outPath = resolve(root, arg('out', '../../src/resources/eject-table.json'));

const TUNES = ['default', 'minimal', 'sharp', 'corporate', 'tech', 'brutal', 'editorial', 'luxury', 'soft', 'pixel', 'draft'];
const STRENGTHS = ['xs', 'sm', 'md', 'lg', 'xl'];
const THEMES = ['pinion', 'reactive'];

/* token -> [cssProperty to read, extra inline css needed] */
const SIZES13 = ['3xs', '2xs', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl'];
const FIELD4 = ['xs', 'sm', 'md', 'lg'];
const TOKEN_PROBES = [
  ...SIZES13.map((s) => [`spacing-${s}`, 'padding-top', `padding-top:var(--spacing-${s})`]),
  ...FIELD4.map((s) => [`h-field-${s}`, 'height', `height:var(--h-field-${s})`]),
  ...FIELD4.map((s) => [`px-field-${s}`, 'padding-left', `padding-left:var(--px-field-${s})`]),
  ...FIELD4.map((s) => [`px-input-${s}`, 'padding-left', `padding-left:var(--px-input-${s})`]),
  ...FIELD4.map((s) => [`py-input-${s}`, 'padding-top', `padding-top:var(--py-input-${s})`]),
  ...FIELD4.map((s) => [`text-field-${s}`, 'font-size', `font-size:var(--text-field-${s})`]),
  ['border', 'border-top-width', 'border-top-style:solid;border-top-width:var(--border)'],
  ['radius-box', 'border-top-left-radius', 'border-top-left-radius:var(--radius-box)'],
  ['radius-field', 'border-top-left-radius', 'border-top-left-radius:var(--radius-field)'],
  ['radius-selector', 'border-top-left-radius', 'border-top-left-radius:var(--radius-selector)'],
  ['shadow-box', 'box-shadow', 'box-shadow:var(--shadow-box)'],
  ['shadow-field', 'box-shadow', 'box-shadow:var(--shadow-field)'],
];

const COLOR_NAMES = [
  'primary', 'primary-content', 'secondary', 'secondary-content',
  'accent', 'accent-content', 'neutral', 'neutral-content',
  'base-100', 'base-200', 'base-300', 'base-content',
  'info', 'info-content', 'success', 'success-content',
  'warning', 'warning-content', 'error', 'error-content',
];

const css = readFileSync(cssPath, 'utf8');
const tokenEls = TOKEN_PROBES.map(([id, , style]) => `<div data-tok="${id}" style="${style}">·</div>`).join('\n');
const colorEls = COLOR_NAMES.map((n) => `<div data-col="${n}" style="background-color:var(--color-${n})">·</div>`).join('\n');
const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><style>${css}</style></head>
<body>${tokenEls}\n${colorEls}<canvas id="cv" width="1" height="1"></canvas></body></html>`;

const browser = await chromium.launch();
const page = await browser.newPage();
await page.setContent(html, { waitUntil: 'load' });

const probeMeta = TOKEN_PROBES.map(([id, prop]) => ({ id, prop }));

async function captureTokens(theme, tune, strength) {
  return page.evaluate(({ theme, tune, strength, probeMeta }) => {
    const h = document.documentElement;
    h.setAttribute('data-theme', theme);
    h.setAttribute('data-tune', tune);
    h.setAttribute('data-tune-strength', strength);
    const out = {};
    for (const { id, prop } of probeMeta) {
      const raw = getComputedStyle(document.querySelector(`[data-tok="${id}"]`)).getPropertyValue(prop).trim();
      out[id] = /^-?[\d.]+px$/.test(raw) ? parseFloat(raw) : raw;
    }
    return out;
  }, { theme, tune, strength, probeMeta });
}

async function captureColors(theme) {
  return page.evaluate(({ theme, names }) => {
    document.documentElement.setAttribute('data-theme', theme);
    const cv = document.getElementById('cv');
    const ctx = cv.getContext('2d', { willReadFrequently: true });
    const out = {};
    for (const n of names) {
      const c = getComputedStyle(document.querySelector(`[data-col="${n}"]`)).backgroundColor;
      ctx.clearRect(0, 0, 1, 1);
      ctx.fillStyle = '#000';
      ctx.fillStyle = c; /* invalid values keep #000; theme colors are opaque */
      ctx.fillRect(0, 0, 1, 1);
      const [r, g, b] = ctx.getImageData(0, 0, 1, 1).data;
      out[n] = '#' + [r, g, b].map((v) => v.toString(16).padStart(2, '0')).join('');
    }
    return out;
  }, { theme, names: COLOR_NAMES });
}

const tokens = {};
for (const tune of TUNES) {
  for (const strength of STRENGTHS) {
    tokens[`${tune}|${strength}`] = await captureTokens('pinion', tune, strength);
  }
}

/* orthogonality guard: tune tokens must not vary by theme */
for (const tune of ['default', 'tech', 'minimal']) {
  const underReactive = await captureTokens('reactive', tune, 'md');
  const underPinion = tokens[`${tune}|md`];
  for (const [k, v] of Object.entries(underPinion)) {
    if (JSON.stringify(underReactive[k]) !== JSON.stringify(v)) {
      console.error(`THEME-VARIANT TOKEN: ${k} @ ${tune}|md — pinion=${JSON.stringify(v)} reactive=${JSON.stringify(underReactive[k])}`);
      process.exit(1);
    }
  }
}

const colors = {};
for (const theme of THEMES) {
  colors[theme] = await captureColors(theme);
}

await browser.close();

const result = {
  meta: {
    generator: 'packages/pinion-ui-css/harness/eject-table.mjs',
    css: 'build/reference.css',
    tunes: TUNES.length, strengths: STRENGTHS.length, themes: THEMES.length,
    tokens: TOKEN_PROBES.length, colors: COLOR_NAMES.length,
  },
  tokens,
  colors,
};
writeFileSync(outPath, JSON.stringify(result, null, 1));
console.log(`eject table: ${Object.keys(tokens).length} tune|strength combos × ${TOKEN_PROBES.length} tokens + ${THEMES.length} themes × ${COLOR_NAMES.length} colors → ${outPath}`);
