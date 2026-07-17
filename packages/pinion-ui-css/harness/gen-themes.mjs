/* ============================================================================
   gen-themes.mjs — generate the v0.6.0 theme lineup from lineup.json
   ============================================================================
   Reads the canonical 36-theme palette data (src/resources/themes/lineup.json)
   and rewrites the GENERATED section of src/resources/css/theme.css with
   72 `@plugin 'daisyui/theme'` blocks (36 light + 36 dark). The hand-written
   `reactive` block above the markers is never touched.

     node packages/pinion-ui-css/harness/gen-themes.mjs        # or npm run gen:themes

   Derivation rules (full rationale: docs/design/theme-lineup-v2-implementation.md):
     base-100 = panel (daisyUI component face) · base-200 = bg (page canvas,
     recessed face) · --root-bg = bg (daisyUI v5 paints :root with
     var(--root-bg, base-100) — this is what makes "tinted page, white cards"
     work with zero consumer CSS) · base-300 = mix(bg→fg, 8% light / 14% dark)
     · base-content = fg · *-content = max-WCAG-contrast pick among
     {fg, bg, panel} · neutral = mix(fg 85%, bg 15%) light / mix(panel 85%,
     fg 15%) dark · status colors = palette extra[] hue-matched first
     (deterministic, one color one role), else per-mode OKLCH anchors mixed
     10% toward bg for theme temperature, L clamped per mode.

   Color math (sRGB ↔ OKLab/OKLCH, WCAG contrast) is self-contained — no deps.
   ============================================================================ */
import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const here = dirname(fileURLToPath(import.meta.url));
const repo = join(here, '..', '..', '..');
const lineupPath = join(repo, 'src', 'resources', 'themes', 'lineup.json');
const themeCssPath = join(repo, 'src', 'resources', 'css', 'theme.css');

const BEGIN = '/* ── BEGIN GENERATED THEME LINEUP — gen-themes.mjs (do not edit by hand) ── */';
const END = '/* ── END GENERATED THEME LINEUP ── */';

/* ---------------- color math ---------------- */
const hex2rgb = (h) => {
  const s = h.replace('#', '');
  return [0, 2, 4].map((i) => parseInt(s.slice(i, i + 2), 16) / 255);
};
const rgb2hex = (rgb) =>
  '#' + rgb.map((v) => Math.round(Math.min(1, Math.max(0, v)) * 255).toString(16).padStart(2, '0')).join('').toUpperCase();
const s2l = (c) => (c <= 0.04045 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4);
const l2s = (c) => (c <= 0.0031308 ? c * 12.92 : 1.055 * c ** (1 / 2.4) - 0.055);

function rgb2oklab([r, g, b]) {
  const [lr, lg, lb] = [s2l(r), s2l(g), s2l(b)];
  const l = Math.cbrt(0.4122214708 * lr + 0.5363325363 * lg + 0.0514459929 * lb);
  const m = Math.cbrt(0.2119034982 * lr + 0.6806995451 * lg + 0.1073969566 * lb);
  const s = Math.cbrt(0.0883024619 * lr + 0.2817188376 * lg + 0.6299787005 * lb);
  return [
    0.2104542553 * l + 0.793617785 * m - 0.0040720468 * s,
    1.9779984951 * l - 2.428592205 * m + 0.4505937099 * s,
    0.0259040371 * l + 0.7827717662 * m - 0.808675766 * s,
  ];
}
function oklab2rgbRaw([L, a, b]) {
  const l = (L + 0.3963377774 * a + 0.2158037573 * b) ** 3;
  const m = (L - 0.1055613458 * a - 0.0638541728 * b) ** 3;
  const s = (L - 0.0894841775 * a - 1.291485548 * b) ** 3;
  return [
    l2s(+4.0767416621 * l - 3.3077115913 * m + 0.2309699292 * s),
    l2s(-1.2684380046 * l + 2.6097574011 * m - 0.3413193965 * s),
    l2s(-0.0041960863 * l - 0.7034186147 * m + 1.707614701 * s),
  ];
}
const inGamut = (rgb) => rgb.every((v) => v >= -0.0005 && v <= 1.0005);
const lab2lch = ([L, a, b]) => [L, Math.hypot(a, b), ((Math.atan2(b, a) * 180) / Math.PI + 360) % 360];
const lch2lab = ([L, C, H]) => [L, C * Math.cos((H * Math.PI) / 180), C * Math.sin((H * Math.PI) / 180)];

/** OKLCH → hex, reducing chroma (binary search) until inside sRGB gamut. */
function lch2hex(lch) {
  let [L, C, H] = lch;
  let rgb = oklab2rgbRaw(lch2lab([L, C, H]));
  if (!inGamut(rgb)) {
    let lo = 0, hi = C;
    for (let i = 0; i < 24; i++) {
      const mid = (lo + hi) / 2;
      if (inGamut(oklab2rgbRaw(lch2lab([L, mid, H])))) lo = mid;
      else hi = mid;
    }
    rgb = oklab2rgbRaw(lch2lab([L, lo, H]));
  }
  return rgb2hex(rgb);
}
const hex2lch = (h) => lab2lch(rgb2oklab(hex2rgb(h)));

/** mix a→b by t in OKLab, return hex. */
function mix(hexA, hexB, t) {
  const A = rgb2oklab(hex2rgb(hexA));
  const B = rgb2oklab(hex2rgb(hexB));
  const M = A.map((v, i) => v + (B[i] - v) * t);
  const [L, C, H] = lab2lch(M);
  return lch2hex([L, C, H]);
}

/* WCAG 2.x contrast */
function luminance(hex) {
  const [r, g, b] = hex2rgb(hex).map(s2l);
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}
function contrast(a, b) {
  const [hi, lo] = [luminance(a), luminance(b)].sort((x, y) => y - x);
  return (hi + 0.05) / (lo + 0.05);
}
/** Highest-contrast neutral from the palette itself. */
function contentFor(color, { fg, bg, panel }) {
  return [fg, bg, panel].reduce((best, c) => (contrast(color, c) > contrast(color, best) ? c : best), fg);
}

/* ---------------- status derivation ---------------- */
const STATUS_ORDER = ['error', 'warning', 'success', 'info'];
const STATUS_HUE = { error: 27, warning: 90, success: 150, info: 245 };
const STATUS_TOL = { error: 25, warning: 30, success: 40, info: 35 };
const ANCHORS = {
  light: { info: [0.58, 0.12, 245], success: [0.58, 0.13, 150], warning: [0.7, 0.13, 90], error: [0.55, 0.19, 27] },
  dark: { info: [0.72, 0.11, 245], success: [0.72, 0.12, 150], warning: [0.8, 0.12, 90], error: [0.68, 0.16, 25] },
};
const L_CLAMP = { light: [0.5, 0.75], dark: [0.62, 0.85] };
const hueDist = (a, b) => Math.min(Math.abs(a - b), 360 - Math.abs(a - b));

function statusColors(mode, palette) {
  const out = {};
  const pool = (palette.extra ?? []).map((hex) => ({ hex, lch: hex2lch(hex), used: false }));
  for (const status of STATUS_ORDER) {
    let best = null;
    for (const cand of pool) {
      if (cand.used || cand.lch[1] < 0.05) continue;
      const d = hueDist(cand.lch[2], STATUS_HUE[status]);
      if (d <= STATUS_TOL[status] && (!best || d < best.d)) best = { cand, d };
    }
    let lch;
    if (best) {
      best.cand.used = true;
      lch = [...best.cand.lch];
    } else {
      lch = hex2lch(mix(lch2hex(ANCHORS[mode][status]), palette.bg, 0.1));
    }
    const [lo, hi] = L_CLAMP[mode];
    lch[0] = Math.min(hi, Math.max(lo, lch[0]));
    out[status] = lch2hex(lch);
  }
  return out;
}

/* ---------------- theme block ---------------- */
function themeId(name, mode) {
  const light = name === 'pinion' ? 'pinion-light' : name;
  return mode === 'light' ? light : `${name}-dark`;
}

function block(theme, mode) {
  const p = theme[mode];
  const id = themeId(theme.name, mode);
  const isDefault = theme.brandDefault === true && mode === 'light';
  const base300 = mix(p.bg, p.fg, mode === 'light' ? 0.08 : 0.14);
  const neutral = mode === 'light' ? mix(p.fg, p.bg, 0.15) : mix(p.panel, p.fg, 0.15);
  const status = statusColors(mode, p);
  const line = (k, v) => `    ${(k + ':').padEnd(27)}${v};`;
  const colorPair = (k, v) => [line(`--color-${k}`, v), line(`--color-${k}-content`, contentFor(v, p))];

  const lines = [
    '@plugin \'daisyui/theme\' {',
    line('name', `'${id}'`),
    line('default', isDefault ? 'true' : 'false'),
    line('color-scheme', `'${mode}'`),
    line('--color-base-100', p.panel),
    line('--color-base-200', p.bg),
    line('--color-base-300', base300),
    line('--color-base-content', p.fg),
    ...colorPair('primary', p.primary),
    ...colorPair('secondary', p.secondary),
    ...colorPair('accent', p.accent),
    ...colorPair('neutral', neutral),
    ...colorPair('info', status.info),
    ...colorPair('success', status.success),
    ...colorPair('warning', status.warning),
    ...colorPair('error', status.error),
    line('--root-bg', p.bg),
    line('--radius-selector', '0.5rem'),
    line('--radius-field', '0.375rem'),
    line('--radius-box', '0.5rem'),
    line('--size-selector', '0.25rem'),
    line('--size-field', '0.25rem'),
    line('--border', '1px'),
    line('--depth', '1'),
    line('--noise', '0'),
    '}',
  ];
  return lines.join('\n');
}

/* ---------------- main ---------------- */
const lineup = JSON.parse(readFileSync(lineupPath, 'utf8'));
const blocks = [];
for (const theme of lineup.themes) {
  blocks.push(
    `/* ${themeId(theme.name, 'light')} / ${theme.name}-dark — ${theme.category} · ${theme.llmTrigger} (${theme.source}) */`,
    block(theme, 'light'),
    block(theme, 'dark'),
  );
}
const generated = [BEGIN,
  `/* ${lineup.themes.length} themes × light/dark = ${lineup.themes.length * 2} blocks, generated from`,
  `   src/resources/themes/lineup.json (locked ${lineup.lockedAt}). Regenerate with`,
  '   `npm run gen:themes` in packages/pinion-ui-css after editing lineup.json.',
  '   Derivation rules: docs/design/theme-lineup-v2-implementation.md (internal). */',
  '',
  blocks.join('\n\n'),
  END].join('\n');

const css = readFileSync(themeCssPath, 'utf8');
const start = css.indexOf(BEGIN);
const end = css.indexOf(END);
if (start === -1 || end === -1) {
  console.error(`theme.css is missing the generated-section markers:\n  ${BEGIN}\n  ${END}`);
  process.exit(1);
}
const next = css.slice(0, start) + generated + css.slice(end + END.length);
writeFileSync(themeCssPath, next);
console.log(`gen-themes: ${lineup.themes.length} themes → ${lineup.themes.length * 2} blocks written to ${themeCssPath}`);
