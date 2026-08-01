<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * ButtonGroup wraps a row (or column) of buttons / links so adjacent items
 * share a continuous border and rounded ends.
 *
 * Implementation note: daisyUI 5's `.join` + `.join-item` system relies on
 * `:where()` selectors whose specificity (0,0,0 inside :where) gets beaten
 * by Tailwind utility classes that the child buttons emit (rounded-…,
 * border-…). Rather than fight that cascade, we use Tailwind arbitrary
 * descendant variants on the wrapper to:
 *   1. zero radii on every direct child, then re-apply on first/last
 *   2. collapse the inner border between adjacent children
 *   3. soften hover so a segmented control reads as a calm tint rather
 *      than a saturated full inversion
 *
 * Children no longer need `class="join-item"` — the wrapper handles
 * everything. The class is still harmless if passed for BC.
 */
class ButtonGroupComposer
{
    public static function compose(array $props): array
    {
        $orientation = $props['orientation'] ?? 'horizontal';

        return [
            'root' => self::root($orientation),
        ];
    }

    private static function root(string $orientation): string
    {
        $vertical = $orientation === 'vertical';

        $layout = $vertical ? 'inline-flex flex-col' : 'inline-flex';

        // Radii: zero everywhere, then restore on the two end children.
        $radii = $vertical
            ? '[&>*]:rounded-none [&>*:first-child]:rounded-t-[var(--radius-field)] [&>*:last-child]:rounded-b-[var(--radius-field)]'
            : '[&>*]:rounded-none [&>*:first-child]:rounded-l-[var(--radius-field)] [&>*:last-child]:rounded-r-[var(--radius-field)]';

        // Collapse the adjacent border so two children don't show a double rule.
        $borderCollapse = $vertical
            ? '[&>*:not(:last-child)]:border-b-0'
            : '[&>*:not(:last-child)]:border-r-0';

        // Active-item seam ownership (segmented controls). The collapse above
        // hands every seam to the FOLLOWING child's leading border — correct
        // for uniform items, wrong for a highlighted one: mark the active
        // segment `aria-pressed="true"` (the string, e.g. Alpine
        // `x-bind:aria-pressed="(active === id) ? 'true' : 'false'"`) and its
        // trailing edge would still be painted grey by its neighbor while its
        // other three edges show the active color (found in the 2026-08-01
        // playground seam audit). Fix: the active item takes back its trailing
        // border, and the item after it drops its leading one — every edge of
        // the active segment is then painted by the active segment itself.
        // Doubles as the correct a11y semantics for a toggle group.
        $activeSeam = $vertical
            ? '[&>[aria-pressed=true]:not(:last-child)]:border-b-[length:var(--border)] [&>[aria-pressed=true]+*]:border-t-0'
            : '[&>[aria-pressed=true]:not(:last-child)]:border-r-[length:var(--border)] [&>[aria-pressed=true]+*]:border-l-0';

        // Soft hover for the group context — overrides each child's own
        // appearance hover (outline-neutral's bg-base-content full invert
        // reads too heavy when several buttons sit shoulder-to-shoulder).
        $softHover = '[&>*]:hover:bg-base-200 [&>*]:hover:text-base-content [&>*]:hover:border-base-300';

        return FieldVariants::join($layout, $radii, $borderCollapse, $activeSeam, $softHover);
    }
}
