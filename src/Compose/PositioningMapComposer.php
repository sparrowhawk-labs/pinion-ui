<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * PositioningMapComposer — a generic 2-axis positioning / perceptual map.
 *
 * Plots labelled points on a plane defined by two axes (e.g. price × quality,
 * effort × impact, or — one consumer of many — tune "shape × voice"). Theme-
 * agnostic: colours are daisyUI semantic tokens only, so it survives every
 * theme; the frame's radius/border read the active tune, but point COORDINATES
 * are fixed data and never shift when the tune changes.
 *
 * Per the Compose invariant the returned dict is class-strings only. Point
 * geometry (left/top %) is NOT a class — the Blade view computes it inline from
 * the `points` prop. Non-class helpers (gridStyle) live beside compose(), like
 * StatComposer::arrowChar.
 */
class PositioningMapComposer
{
    public static function compose(array $props): array
    {
        $size = $props['size'] ?? 'md';
        $grid = array_key_exists('grid', $props) ? (bool) $props['grid'] : true;

        $height = match ($size) {
            'sm' => 'min-h-[15rem]',
            'lg' => 'min-h-[30rem]',
            default => 'min-h-[22rem]',
        };

        return [
            // Frame: respects the active tune (radius + border width) but stays
            // theme-neutral (base surface + hairline base-content border).
            'root'         => "positioning-map relative w-full $height bg-base-100 text-base-content tune-border border-base-content/15 rounded-[var(--radius-box)] overflow-hidden",
            // Grid + plot are inset by a uniform margin so the whole field has
            // breathing room and edge points (e.g. 0/100) aren't clipped. The
            // inset is a uniform scale+translate of the field, so RELATIVE point
            // positions are preserved — coordinates remain fixed data.
            'grid'         => $grid ? 'absolute inset-[8%] pointer-events-none' : 'hidden',
            'plot'         => 'absolute inset-[8%]',
            // One point. The wrapper is anchored so the NODE sits exactly on the
            // coordinate (the datum is the truth); the chip + stem are absolutely
            // positioned and grow upward, so chip size never shifts the node and
            // overlapping chips don't move the underlying points.
            'point'        => 'absolute group z-10 hover:z-30',
            // Active point sits above its neighbours so a highlighted chip is
            // never buried in a dense cluster (no JS — pure stacking).
            'pointActive'  => 'absolute group z-20 hover:z-30',
            // The node = the actual datum, on the coordinate, recentered onto it.
            // A halo ring (base-100) keeps it readable over a grid line or chip.
            'node'         => 'block h-2 w-2 -translate-x-1/2 -translate-y-1/2 rounded-full bg-base-content ring-2 ring-base-100 shadow-[var(--shadow-box)]',
            'nodeActive'   => 'block h-2.5 w-2.5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary ring-[3px] ring-base-100',
            // The "ping": a concentric ring around the active node, signalling the
            // current readout without animation.
            'ping'         => 'absolute left-0 top-0 -translate-x-1/2 -translate-y-1/2 h-5 w-5 rounded-full ring-2 ring-primary/35 pointer-events-none',
            // Chip + stem stack, absolutely anchored above the node and centered.
            'flag'         => 'absolute left-0 bottom-full -translate-x-1/2 flex flex-col items-center',
            // Thin stem connecting node → chip.
            'stem'         => 'w-px h-2.5 bg-base-content/25',
            // The label chip is a small tune-aware card (not an inverted pill),
            // so it reads in a dense field and on dark themes alike. inline-flex
            // so an optional <x-i> icon can sit before the label.
            'marker'       => 'inline-flex items-center gap-1 rounded-[var(--radius-field)] bg-base-100 tune-border border-base-content/15 text-base-content text-xs font-semibold leading-none px-2 py-1 whitespace-nowrap shadow-[var(--shadow-box)] transition-colors group-hover:border-base-content/40',
            'markerActive' => 'inline-flex items-center gap-1 rounded-[var(--radius-field)] bg-primary border-transparent text-primary-content text-xs font-semibold leading-none px-2 py-1 whitespace-nowrap shadow-[var(--shadow-box)]',
            // Per-point icon (<x-i>) sizing inside the label chip.
            'icon'         => 'w-3.5 h-3.5 shrink-0',
            // Image/logo marker — a framed thumbnail. Rounding (square vs circle)
            // is applied in the view so `imageShape` can pick. Tune-aware frame.
            'markerImage'      => 'block overflow-hidden h-9 w-9 tune-border border-base-content/15 bg-base-100 shadow-[var(--shadow-box)] transition-colors group-hover:border-base-content/40',
            'markerImageActive'=> 'block overflow-hidden h-9 w-9 border-2 border-primary bg-base-100 shadow-[var(--shadow-box)] ring-2 ring-primary/30',
            'imageEl'      => 'h-full w-full object-cover',
            'sublabel'     => 'block mt-0.5 text-[10px] leading-none tracking-wide text-base-content/45 whitespace-nowrap',
            'sublabelActive' => 'block mt-0.5 text-[10px] leading-none tracking-wide text-primary-content/70 whitespace-nowrap',
            // Axis captions: a quiet tracked label with a leading tick rule, no
            // glyph arrows. Placed clear of the point field.
            'axis'         => 'absolute text-[10px] uppercase tracking-[0.14em] font-medium text-base-content/45 pointer-events-none select-none',
            // Faint quadrant background labels.
            'quadrant'     => 'absolute text-[10px] uppercase tracking-wider text-base-content/25 pointer-events-none select-none',
        ];
    }

    /**
     * Grid overlay background as an inline style value. NOT a class (keeps the
     * compose() dict class-strings-only). A faint uniform 8×8 reference grid PLUS
     * a stronger center cross at 50/50 — the meaningful origin of a perceptual
     * map (structure encodes information, not decoration).
     */
    public static function gridStyle(): string
    {
        $fine   = 'color-mix(in oklab, var(--color-base-content) 6%, transparent)';
        $center = 'color-mix(in oklab, var(--color-base-content) 16%, transparent)';

        // Layer 1: the 50% center cross (drawn last = on top, via a 1px line that
        // repeats only at the midpoint). Layers 2–3: the fine 8-cell grid.
        return implode('', [
            'background-image:',
            "linear-gradient(to right, transparent calc(50% - 0.5px), {$center} calc(50% - 0.5px), {$center} calc(50% + 0.5px), transparent calc(50% + 0.5px)),",
            "linear-gradient(to bottom, transparent calc(50% - 0.5px), {$center} calc(50% - 0.5px), {$center} calc(50% + 0.5px), transparent calc(50% + 0.5px)),",
            "linear-gradient(to right, {$fine} 1px, transparent 1px),",
            "linear-gradient(to bottom, {$fine} 1px, transparent 1px);",
            'background-size:100% 100%,100% 100%,12.5% 12.5%,12.5% 12.5%;',
            'background-position:center,center,0 0,0 0;',
        ]);
    }
}
