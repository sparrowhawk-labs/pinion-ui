<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class IndicatorComposer
{
    public static function compose(array $props): array
    {
        $position   = $props['position']   ?? 'top-end';
        $dot        = array_key_exists('dot', $props) ? (bool) $props['dot'] : false;
        $color      = $props['color']      ?? 'error';
        $appearance = $props['appearance'] ?? 'solid';

        return [
            'root' => 'relative inline-flex w-fit',
            'item' => self::item($position, $dot, $color, $appearance),
        ];
    }

    private static function item(string $position, bool $dot, ?string $color, string $appearance): string
    {
        $parts = array_filter([
            'absolute z-10',
            self::positionClass($position),
            self::badgeClasses($color, $dot, $appearance),
        ], fn ($s) => $s !== '');

        return implode(' ', $parts);
    }

    /**
     * Plain-Tailwind corner anchoring, mirroring daisyUI's `indicator`/
     * `indicator-item`/`indicator-{position}` grammar: `top`/`bottom`/
     * `start`/`end` set the anchor edge (or the 50% midpoint for
     * `center`/`middle`), and a matching half-size translate straddles the
     * chip across that edge.
     */
    private static function positionClass(string $position): string
    {
        return match ($position) {
            'top-start'      => 'top-0 start-0 -translate-y-1/2 -translate-x-1/2',
            'top-center'     => 'top-0 start-1/2 -translate-y-1/2 -translate-x-1/2',
            'middle-start'   => 'top-1/2 start-0 -translate-y-1/2 -translate-x-1/2',
            'middle-center'  => 'top-1/2 start-1/2 -translate-y-1/2 -translate-x-1/2',
            'middle-end'     => 'top-1/2 end-0 -translate-y-1/2 translate-x-1/2',
            'bottom-start'   => 'bottom-0 start-0 translate-y-1/2 -translate-x-1/2',
            'bottom-center'  => 'bottom-0 start-1/2 translate-y-1/2 -translate-x-1/2',
            'bottom-end'     => 'bottom-0 end-0 translate-y-1/2 translate-x-1/2',
            default          => 'top-0 end-0 -translate-y-1/2 translate-x-1/2',
        };
    }

    /**
     * Utility-composed chip, mirroring badge.blade.php's appearance × color
     * grammar (v0.4.2 — replaced daisyUI's `badge badge-*` classes so the
     * `badge` component could join the preset's daisyUI exclude list; hosts
     * no longer get `.badge` at all). Match arms stay fully literal so the
     * preset's @source scan over Compose PHP picks every class up.
     */
    private static function badgeClasses(?string $color, bool $dot, string $appearance): string
    {
        $color = in_array($color, ['primary', 'secondary', 'accent', 'neutral', 'info', 'success', 'warning', 'error'], true)
            ? $color : 'error';
        $appearance = in_array($appearance, ['solid', 'soft', 'outline', 'ghost', 'dash'], true)
            ? $appearance : 'solid';

        // Non-dot: h-5 + min-w-[1.25rem] (both 20px) locks the height so short
        // content (a single digit/glyph) renders as a true circle instead of
        // a squashed oval — px-2 alone (no explicit height) let line-height/
        // flex-sizing interact unpredictably and produced a ~22×10px oval.
        // Longer content (e.g. "99+") still grows the width via padding while
        // height stays pinned, giving the expected pill shape for that case.
        $base = $dot
            ? 'block h-3 w-3 p-0 rounded-full tune-border'
            : 'inline-flex items-center justify-center whitespace-nowrap text-xs leading-none font-medium h-5 min-w-[1.25rem] px-1.5 rounded-full tune-border';

        $variant = match ("{$appearance}-{$color}") {
            // solid — filled chip, alert-strength (default)
            'solid-primary'   => 'bg-primary text-primary-content border-primary',
            'solid-secondary' => 'bg-secondary text-secondary-content border-secondary',
            'solid-accent'    => 'bg-accent text-accent-content border-accent',
            'solid-neutral'   => 'bg-neutral text-neutral-content border-neutral',
            'solid-info'      => 'bg-info text-info-content border-info',
            'solid-success'   => 'bg-success text-success-content border-success',
            'solid-warning'   => 'bg-warning text-warning-content border-warning',
            'solid-error'     => 'bg-error text-error-content border-error',

            // soft — tinted bubble
            'soft-primary'   => 'bg-primary/15 text-primary border-transparent',
            'soft-secondary' => 'bg-secondary/15 text-secondary border-transparent',
            'soft-accent'    => 'bg-accent/15 text-accent border-transparent',
            'soft-neutral'   => 'bg-base-content/15 text-base-content border-transparent',
            'soft-info'      => 'bg-info/15 text-info border-transparent',
            'soft-success'   => 'bg-success/15 text-success border-transparent',
            'soft-warning'   => 'bg-warning/15 text-warning border-transparent',
            'soft-error'     => 'bg-error/15 text-error border-transparent',

            // outline — opaque surface bg so the chip stays readable while
            // floating over arbitrary child content (an indicator is always
            // overlaid, unlike an inline <x-badge> where transparent works)
            'outline-primary'   => 'bg-base-100 text-primary border-primary',
            'outline-secondary' => 'bg-base-100 text-secondary border-secondary',
            'outline-accent'    => 'bg-base-100 text-accent border-accent',
            'outline-neutral'   => 'bg-base-100 text-base-content border-base-content/30',
            'outline-info'      => 'bg-base-100 text-info border-info',
            'outline-success'   => 'bg-base-100 text-success border-success',
            'outline-warning'   => 'bg-base-100 text-warning border-warning',
            'outline-error'     => 'bg-base-100 text-error border-error',

            // dash — outline with a dashed border
            'dash-primary'   => 'bg-base-100 text-primary border-primary border-dashed',
            'dash-secondary' => 'bg-base-100 text-secondary border-secondary border-dashed',
            'dash-accent'    => 'bg-base-100 text-accent border-accent border-dashed',
            'dash-neutral'   => 'bg-base-100 text-base-content border-base-content/30 border-dashed',
            'dash-info'      => 'bg-base-100 text-info border-info border-dashed',
            'dash-success'   => 'bg-base-100 text-success border-success border-dashed',
            'dash-warning'   => 'bg-base-100 text-warning border-warning border-dashed',
            'dash-error'     => 'bg-base-100 text-error border-error border-dashed',

            // ghost — neutral surface chip; color is ignored (daisyUI parity)
            'ghost-primary', 'ghost-secondary', 'ghost-accent', 'ghost-neutral',
            'ghost-info', 'ghost-success', 'ghost-warning', 'ghost-error'
                => 'bg-base-200 text-base-content border-transparent',
        };

        return $base . ' ' . $variant;
    }
}
