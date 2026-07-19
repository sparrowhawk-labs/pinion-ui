/* ============================================================================
   probes.mjs — the single source of truth for the golden computed-style harness
   ============================================================================
   ONE shared module consumed by BOTH gen-fixture.mjs (emits fixture.html) and
   capture.mjs (reads computed styles). Keeping the probe list here means the
   fixture markup and the capture allowlist can never drift apart.

   A "probe" is one DOM element that consumes the theme×tune surface in exactly
   ONE way, plus the precise set of computed properties we read back from it.

   Design rules (load-bearing — see Phase 0 notes):
     1. Capture ONLY the property each probe targets. The diff must be immune to
        preflight-vs-no-preflight differences on UNRELATED properties (the
        future plain-dist build ships WITHOUT Tailwind preflight; the reference
        Blade build ships WITH it). Reading only the targeted property keeps the
        comparison about the tune/theme token, nothing else.
     2. Every probe carries inline `box-sizing:border-box`. Preflight sets
        border-box on `*`; a no-preflight build defaults to content-box. For a
        probe that sets BOTH height and padding, that flips computed `height`.
        Forcing border-box inline (inline wins over everything) removes the
        confound — box-sizing is not a tune token, so neutralizing it is correct.
     3. Probes consume the SHARED surface only: the tune @utility classes, the theme-generated t-shirt spacing utilities, and
        the theme colors via `var(--color-*)`. NOT daisyUI component classes nor
        `bg-primary`-style utilities — those are OUT of the plain-dist scope and
        would make the candidate build unable to reproduce the reference.
   ============================================================================ */

/* v0.6.0 lineup: 72 original themes exist; sweeping all of them would be
   3,960 combos for no extra signal (theme blocks are structurally identical
   generated output). We sweep a representative 5: the brand pair, reactive
   (hand-maintained), one mood light, one SaaS dark. */
export const THEMES = ['pinion', 'pinion-dark', 'reactive', 'monokai', 'payments-dark'];

export const TUNES = [
  'default', 'minimal', 'sharp', 'corporate', 'tech',
  'brutal', 'editorial', 'luxury', 'soft', 'pixel', 'draft',
];

export const STRENGTHS = ['xs', 'sm', 'md', 'lg', 'xl'];

const BASE_STYLE = 'box-sizing:border-box';

/** @typedef {Object} Probe
 *  @property {string} id          unique probe id (also the data-cap value)
 *  @property {string} tag         element tag (default 'div')
 *  @property {string} className   tune utility class(es) under test ('' for inline-var probes)
 *  @property {string} style       extra inline style (merged after BASE_STYLE)
 *  @property {'self'|'firstChild'} capOn  where data-cap lives / where we read styles
 *  @property {string[]} capture   computed-style longhand properties to read back
 */

/** @type {Probe[]} */
export const PROBES = [];

let _seq = 0;
function probe(p) {
  const style = p.style ? `${BASE_STYLE};${p.style}` : BASE_STYLE;
  PROBES.push({
    id: p.id,
    tag: p.tag || 'div',
    className: p.className || '',
    style,
    capOn: p.capOn || 'self',
    capture: p.capture,
  });
  _seq++;
}

/* ---- helpers: longhand property bundles -------------------------------- */
const RADIUS = ['border-top-left-radius'];
const PAD_INLINE = ['padding-left', 'padding-right'];
const PAD_BLOCK = ['padding-top', 'padding-bottom'];
const PAD_ALL = [...PAD_BLOCK, ...PAD_INLINE];
const GAP = ['column-gap', 'row-gap'];
const FSIZE = ['font-size'];

/* =====================================================================
   1) Tune utility probes — every tune @utility plus the theme-generated
      t-shirt spacing utilities, each capturing only what it sets.
   ===================================================================== */

/* --- Shape --- */
probe({ id: 'radius-box', className: 'radius-box', capture: RADIUS });
probe({ id: 'radius-field', className: 'radius-field', capture: RADIUS });
probe({ id: 'radius-selector', className: 'radius-selector', capture: RADIUS });
/* tune-border only sets border-width; computed width is 0 unless a style is set */
probe({ id: 'tune-border', className: 'tune-border', style: 'border-style:solid', capture: ['border-top-width'] });

/* --- Compound component sizes (height + padding-inline + font-size) --- */
for (const s of ['xs', 'sm', 'md', 'lg']) {
  probe({ id: `tune-btn-${s}`, className: `tune-btn-${s}`, capture: ['height', ...PAD_INLINE, ...FSIZE] });
}
for (const s of ['sm', 'md', 'lg']) {
  probe({ id: `tune-input-${s}`, className: `tune-input-${s}`, capture: ['height', ...PAD_INLINE, ...FSIZE] });
  probe({ id: `tune-textarea-${s}`, className: `tune-textarea-${s}`, capture: [...PAD_ALL, ...FSIZE] });
  probe({ id: `tune-tab-${s}`, className: `tune-tab-${s}`, capture: ['height', ...PAD_INLINE, ...FSIZE] });
}
probe({ id: 'tune-alert-md', className: 'tune-alert-md', capture: ['min-height', ...PAD_ALL, ...FSIZE] });
probe({ id: 'tune-accordion-header', className: 'tune-accordion-header', capture: ['min-height', ...PAD_INLINE, ...FSIZE] });
probe({ id: 'tune-accordion-body', className: 'tune-accordion-body', capture: [...PAD_ALL, ...FSIZE] });
probe({ id: 'tune-dropdown-trigger', className: 'tune-dropdown-trigger', capture: ['height', ...PAD_INLINE, ...FSIZE] });
probe({ id: 'tune-menu-item', className: 'tune-menu-item', capture: ['min-height', ...PAD_INLINE, ...FSIZE] });
probe({ id: 'tune-card-pad', className: 'tune-card-pad', capture: PAD_ALL });
probe({ id: 'tune-modal-body', className: 'tune-modal-body', capture: FSIZE });
probe({ id: 'tune-modal-title', className: 'tune-modal-title', capture: FSIZE });

/* --- Spacing (t-shirt — v0.5 rename, docs/design/spacing-v0.5-tshirt.md) ---
   These are Tailwind spacing-namespace utilities generated from the @theme
   --spacing-<size> keys in tune.css (NOT tune @utility definitions). The
   probe set mirrors the pre-rename tiers 1:1 so the rename can be verified
   as computed-identical (old golden ↔ new golden via the tier→t-shirt map),
   plus one PAD_ALL probe per NEW bucket for dist-diff coverage. */
probe({ id: 'py-4xl', className: 'py-4xl', capture: PAD_BLOCK });      /* was space-section */
probe({ id: 'p-2xl', className: 'p-2xl', capture: PAD_ALL });          /* was space-section-inner */
for (const u of ['gap-2xl', 'gap-lg', 'gap-sm', 'gap-md', 'gap-xs', 'gap-2xs']) {
  probe({ id: u, className: u, style: 'display:flex', capture: GAP });
}
/* descendant-combinator stacks: utility sets margin on every child but the last,
   so we give the probe 2 children and read the FIRST child's margin. */
for (const u of ['space-x-xs', 'space-x-2xs']) {
  probe({ id: u, className: u, capOn: 'firstChild', capture: ['margin-right'] });
}
for (const u of ['space-y-2xs', 'space-y-md', 'space-y-sm', 'space-y-lg']) {
  probe({ id: u, className: u, capOn: 'firstChild', capture: ['margin-bottom'] });
}
probe({ id: 'mt-md', className: 'mt-md', capture: ['margin-top'] });
probe({ id: 'mb-md', className: 'mb-md', capture: ['margin-bottom'] });
probe({ id: 'mt-lg', className: 'mt-lg', capture: ['margin-top'] });
probe({ id: 'mb-lg', className: 'mb-lg', capture: ['margin-bottom'] });
probe({ id: 'mt-2xl', className: 'mt-2xl', capture: ['margin-top'] });
probe({ id: 'mb-2xl', className: 'mb-2xl', capture: ['margin-bottom'] });
probe({ id: 'p-lg', className: 'p-lg', capture: PAD_ALL });            /* was p-element */
probe({ id: 'p-sm', className: 'p-sm', capture: PAD_ALL });            /* was p-compact */
probe({ id: 'px-sm', className: 'px-sm', capture: PAD_INLINE });
probe({ id: 'py-sm', className: 'py-sm', capture: PAD_BLOCK });
/* NEW buckets (no legacy counterpart — coverage for the v0.5 dist diff). */
for (const u of ['p-3xs', 'p-2xs', 'p-xs', 'p-md', 'p-xl', 'p-3xl', 'p-4xl', 'p-5xl', 'p-6xl', 'p-7xl']) {
  probe({ id: u, className: u, capture: PAD_ALL });
}

/* --- Font --- */
probe({ id: 'font-heading', className: 'font-heading', capture: ['font-family'] });
probe({ id: 'font-body', className: 'font-body', capture: ['font-family'] });
probe({ id: 'font-mono-tune', className: 'font-mono-tune', capture: ['font-family'] });
probe({ id: 'font-weight-heading', className: 'font-weight-heading', capture: ['font-weight'] });

/* --- Component-size singletons --- */
for (const s of ['xs', 'sm', 'md', 'lg']) {
  probe({ id: `h-field-${s}`, className: `h-field-${s}`, capture: ['height'] });
  probe({ id: `px-field-${s}`, className: `px-field-${s}`, capture: PAD_INLINE });
  probe({ id: `px-input-${s}`, className: `px-input-${s}`, capture: PAD_INLINE });
  probe({ id: `py-input-${s}`, className: `py-input-${s}`, capture: PAD_BLOCK });
  probe({ id: `text-field-${s}`, className: `text-field-${s}`, capture: FSIZE });
}

/* =====================================================================
   2) Theme-color probes — read the daisyUI theme custom-property cascade
      via var(--color-*). These live in BOTH the reference and the future
      plain-dist build (the [data-theme] blocks are in scope for dist).
   ===================================================================== */
const COLORS = [
  'base-100', 'base-200', 'base-300', 'base-content',
  'primary', 'primary-content', 'secondary', 'secondary-content',
  'accent', 'accent-content', 'neutral', 'neutral-content',
  'info', 'info-content', 'success', 'success-content',
  'warning', 'warning-content', 'error', 'error-content',
];
for (const c of COLORS) {
  probe({ id: `color-${c}`, style: `color:var(--color-${c})`, capture: ['color'] });
}

/* =====================================================================
   3) Tune token probes that no @utility exposes directly — read them
      through an inline property so the resolved value is observable.
   ===================================================================== */
probe({ id: 'shadow-box', style: 'box-shadow:var(--shadow-box)', capture: ['box-shadow'] });
probe({ id: 'shadow-field', style: 'box-shadow:var(--shadow-field)', capture: ['box-shadow'] });
probe({ id: 'tracking-heading', style: 'letter-spacing:var(--tracking-heading)', capture: ['letter-spacing'] });
probe({ id: 'leading-body', style: 'line-height:var(--leading-body)', capture: ['line-height'] });
/* type-scale-ratio is a unitless multiplier used inside calc()s; observe it by
   multiplying a known base so the resolved px reflects the token. */
probe({ id: 'type-scale-ratio', style: 'font-size:calc(1rem * var(--type-scale-ratio))', capture: ['font-size'] });

/* =====================================================================
   [SPIKE S2 · docs/design/spacing-v0.5-tshirt.md] t-shirt spacing probes.
   Prove the Tailwind-idiom t-shirt utilities (aliased to tune-reactive
   tokens in reference.entry.css) still vary per tune. Additive — existing
   probes are untouched, so a diff vs the pre-spike baseline over the old
   probes must be 0. Replace when S3 renames spacing for real.
   ===================================================================== */
probe({ id: 'p-md', className: 'p-md', capture: PAD_ALL });
probe({ id: 'p-lg', className: 'p-lg', capture: PAD_ALL });
probe({ id: 'gap-lg', className: 'gap-lg', style: 'display:flex', capture: GAP });
probe({ id: 'space-y-lg', className: 'space-y-lg', capOn: 'firstChild', capture: ['margin-bottom'] });

/* =====================================================================
   4) Host-app container-scale guard — the @theme --spacing-<size> keys
      share their key names (3xs-7xl) with Tailwind's default --container-*
      scale, and the spacing namespace WINS name resolution for the
      width-family utilities (w-*, min-w-*, max-w-*, basis-*). Without the
      per-utility compensation keys in tune.css these collapse to spacing
      values in every host app (max-w-6xl: 72rem -> 8rem = layout break).
      These probes pin the Tailwind default container values so any future
      spacing-key change that re-shadows the scale fails the golden diff.
      h-*, min-h-*, max-h-*, size-* have no container-named values in stock
      Tailwind, so their t-shirt leakage stays additive-only (O1) and needs
      no probe.
      Expected values are rem-relative (some tunes scale the page font-size:
      minimal .9375 / pixel 1.125), so each guarded size gets a CONTROL probe
      carrying the literal container rem value inline; selfcheck.mjs asserts
      utility === control per combo instead of hardcoded px. */
export const CONTAINER_GUARD = [
  { size: 'md', rem: '28rem' },  /* --container-md */
  { size: '6xl', rem: '72rem' }, /* --container-6xl */
];
export const WIDTH_FAMILY = [
  ['max-w', 'max-width'],
  ['w', 'width'],
  ['min-w', 'min-width'],
  ['basis', 'flex-basis'],
];
for (const { size, rem } of CONTAINER_GUARD) {
  probe({
    id: `ctrl-container-${size}`,
    style: `width:${rem};min-width:${rem};max-width:${rem};flex-basis:${rem}`,
    capture: WIDTH_FAMILY.map(([, prop]) => prop),
  });
  for (const [prefix, prop] of WIDTH_FAMILY) {
    probe({ id: `${prefix}-${size}`, className: `${prefix}-${size}`, capture: [prop] });
  }
}

/* Every combination of theme × tune × strength the harness sweeps. */
export function combos() {
  const out = [];
  for (const theme of THEMES)
    for (const tune of TUNES)
      for (const strength of STRENGTHS)
        out.push({ theme, tune, strength, key: `${theme}|${tune}|${strength}` });
  return out;
}

/* The exact list of tune @utility class names (for the purge-guard grep gate). */
export const TUNE_UTILITIES = PROBES
  .filter((p) => p.className)
  .flatMap((p) => p.className.split(/\s+/));
