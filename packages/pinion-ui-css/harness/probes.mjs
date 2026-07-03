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
     3. Probes consume the SHARED surface only: the 73 tune @utility classes and
        the theme colors via `var(--color-*)`. NOT daisyUI component classes nor
        `bg-primary`-style utilities — those are OUT of the plain-dist scope and
        would make the candidate build unable to reproduce the reference.
   ============================================================================ */

export const THEMES = ['pinion', 'reactive'];

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
   1) Tune @utility probes — all 73, each capturing only what it sets.
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

/* --- Spacing --- */
probe({ id: 'space-section', className: 'space-section', capture: PAD_BLOCK });
probe({ id: 'space-section-inner', className: 'space-section-inner', capture: PAD_ALL });
for (const u of ['gap-section-inner', 'gap-element', 'gap-compact', 'gap-text', 'gap-inline', 'gap-micro']) {
  probe({ id: u, className: u, style: 'display:flex', capture: GAP });
}
/* descendant-combinator stacks: utility sets margin on every child but the last,
   so we give the probe 2 children and read the FIRST child's margin. */
for (const u of ['space-x-inline', 'space-x-micro']) {
  probe({ id: u, className: u, capOn: 'firstChild', capture: ['margin-right'] });
}
for (const u of ['space-y-micro', 'space-y-text', 'space-y-compact', 'space-y-element']) {
  probe({ id: u, className: u, capOn: 'firstChild', capture: ['margin-bottom'] });
}
probe({ id: 'mt-text', className: 'mt-text', capture: ['margin-top'] });
probe({ id: 'mb-text', className: 'mb-text', capture: ['margin-bottom'] });
probe({ id: 'mt-element', className: 'mt-element', capture: ['margin-top'] });
probe({ id: 'mb-element', className: 'mb-element', capture: ['margin-bottom'] });
probe({ id: 'mt-section-inner', className: 'mt-section-inner', capture: ['margin-top'] });
probe({ id: 'mb-section-inner', className: 'mb-section-inner', capture: ['margin-bottom'] });
probe({ id: 'p-element', className: 'p-element', capture: PAD_ALL });
probe({ id: 'p-compact', className: 'p-compact', capture: PAD_ALL });
probe({ id: 'px-compact', className: 'px-compact', capture: PAD_INLINE });
probe({ id: 'py-compact', className: 'py-compact', capture: PAD_BLOCK });

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
