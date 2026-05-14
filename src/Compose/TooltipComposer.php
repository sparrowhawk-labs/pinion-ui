<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * Tooltip is a hover- / focus-driven label that floats next to its trigger.
 *
 * v0.3.11 rewrite: dropped daisyUI's CSS-only `tooltip` + `data-tip` system
 * in favour of an Alpine + custom-arrow approach mirroring <x-popover>.
 * Reason: daisyUI's pseudo-element arrow uses a mask-image whose fill is
 * `--tt-bg` only — when the bg is `base-100` (page-coloured), the arrow
 * vanishes (no border, no contrast). The custom diamond arrow used by
 * popover renders cleanly for every bg, including `base-100`.
 *
 * BC: surface props (`text`, `position`, `color`, `open`) unchanged.
 * Internal rendering, classes, and DOM all changed.
 */
class TooltipComposer
{
    public static function compose(array $props): array
    {
        $position = $props['position'] ?? 'top';
        $color    = $props['color']    ?? null;
        $open     = array_key_exists('open', $props) ? (bool) $props['open'] : false;

        $position = self::normalisedPosition($position);

        return [
            'root'      => 'relative inline-block',
            'bubble'    => self::bubbleClass($position, $color),
            'arrow'     => self::arrowClass($position, $color),
            'placement' => $position,
            'forceOpen' => $open,
        ];
    }

    private static function normalisedPosition(string $position): string
    {
        return in_array($position, ['top', 'right', 'bottom', 'left'], true) ? $position : 'top';
    }

    private static function bubbleClass(string $position, ?string $color): string
    {
        $placement = match ($position) {
            'right'  => 'left-full top-1/2 -translate-y-1/2 ml-2',
            'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
            'left'   => 'right-full top-1/2 -translate-y-1/2 mr-2',
            default  => 'bottom-full left-1/2 -translate-x-1/2 mb-2', // top
        };

        return FieldVariants::join(
            'absolute z-50 pointer-events-none',
            'whitespace-nowrap max-w-xs',
            'px-2 py-1 text-xs',
            'rounded-[var(--radius-field)] shadow-md tune-border',
            $placement,
            self::bubbleColorClass($color),
        );
    }

    private static function bubbleColorClass(?string $color): string
    {
        // bg + text-content + border on the bubble. The arrow shares the
        // same bg and border colour so the join is seamless.
        return match ($color) {
            'primary'   => 'bg-primary text-primary-content border-primary',
            'secondary' => 'bg-secondary text-secondary-content border-secondary',
            'accent'    => 'bg-accent text-accent-content border-accent',
            'info'      => 'bg-info text-info-content border-info',
            'success'   => 'bg-success text-success-content border-success',
            'warning'   => 'bg-warning text-warning-content border-warning',
            'error'     => 'bg-error text-error-content border-error',
            'neutral'   => 'bg-neutral text-neutral-content border-neutral',
            'base-100'  => 'bg-base-100 text-base-content border-base-300',
            'base-200'  => 'bg-base-200 text-base-content border-base-300',
            'base-300'  => 'bg-base-300 text-base-content border-base-content/20',
            default     => 'bg-base-200 text-base-content border-base-300', // 'light' default
        };
    }

    private static function arrowClass(string $position, ?string $color): string
    {
        // 8px square rotated 45° -> diamond. Position at the bubble edge facing
        // the trigger, nudged half its size INTO the trigger so its two visible
        // edges read as the bubble's pointing tip. Border on those two edges
        // matches the bubble border colour.
        [$pos, $borders] = match ($position) {
            'right'  => ['left-0 top-1/2 -translate-x-1/2 -translate-y-1/2', 'border-l border-b'],
            'bottom' => ['top-0 left-1/2 -translate-x-1/2 -translate-y-1/2',  'border-l border-t'],
            'left'   => ['right-0 top-1/2 translate-x-1/2 -translate-y-1/2',  'border-r border-t'],
            default  => ['bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2','border-r border-b'], // top
        };

        return FieldVariants::join(
            'absolute w-2 h-2 rotate-45',
            $pos,
            $borders,
            self::arrowColorClass($color),
        );
    }

    private static function arrowColorClass(?string $color): string
    {
        // bg + border colour for the arrow. Mirrors the bubble.
        return match ($color) {
            'primary'   => 'bg-primary border-primary',
            'secondary' => 'bg-secondary border-secondary',
            'accent'    => 'bg-accent border-accent',
            'info'      => 'bg-info border-info',
            'success'   => 'bg-success border-success',
            'warning'   => 'bg-warning border-warning',
            'error'     => 'bg-error border-error',
            'neutral'   => 'bg-neutral border-neutral',
            'base-100'  => 'bg-base-100 border-base-300',
            'base-200'  => 'bg-base-200 border-base-300',
            'base-300'  => 'bg-base-300 border-base-content/20',
            default     => 'bg-base-200 border-base-300', // 'light' default
        };
    }
}
