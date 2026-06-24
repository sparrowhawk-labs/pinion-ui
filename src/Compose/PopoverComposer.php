<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * Popover is a click-driven panel that floats next to a trigger element.
 * Distinct from <x-dropdown> (menu-item semantics) and <x-tooltip> (hover,
 * text only): popover hosts arbitrary content — info card, mini form,
 * action confirmation, etc.
 *
 * Positioning is CSS-based (no JS calculation) — choose one of four
 * placements and the composer emits the absolute-position class along with
 * arrow translation utilities. This trades freedom for simplicity:
 * panels are placed relative to the trigger, never auto-flipped on
 * viewport collision. For collision-aware placement reach for the
 * Floating UI / @alpinejs/anchor plugin and wire your own popover.
 */
class PopoverComposer
{
    public static function compose(array $props): array
    {
        $placement = $props['placement'] ?? 'bottom';
        $width     = $props['width']     ?? 'w-72';
        $arrow     = (bool) ($props['arrow'] ?? true);
        $padding   = $props['padding']   ?? 'p-element';

        return [
            'root'      => 'relative inline-block',
            'panel'     => self::panelClass($placement, $width, $padding),
            'arrow'     => self::arrowClass($placement),
            'showArrow' => $arrow,
            'placement' => self::normalisedPlacement($placement),
        ];
    }

    private static function normalisedPlacement(string $placement): string
    {
        return in_array($placement, ['top', 'right', 'bottom', 'left'], true) ? $placement : 'bottom';
    }

    private static function panelClass(string $placement, string $width, string $padding): string
    {
        $position = match (self::normalisedPlacement($placement)) {
            'top'    => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
            'right'  => 'left-full top-1/2 -translate-y-1/2 ml-2',
            'left'   => 'right-full top-1/2 -translate-y-1/2 mr-2',
            default  => 'top-full left-1/2 -translate-x-1/2 mt-2', // bottom
        };

        return FieldVariants::join(
            'absolute z-50',
            $position,
            $width,
            'rounded-[var(--radius-box)] bg-base-100 tune-border border-base-300 shadow-[var(--shadow-box)]',
            $padding,
        );
    }

    private static function arrowClass(string $placement): string
    {
        // 8px square rotated 45° gives a clean diamond. Position it at the
        // edge of the panel that faces the trigger, then nudge half its own
        // width / height into the trigger so it visually 'pierces' the border.
        $position = match (self::normalisedPlacement($placement)) {
            'top'    => 'bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 border-r border-b',
            'right'  => 'left-0 top-1/2 -translate-x-1/2 -translate-y-1/2 border-l border-b',
            'left'   => 'right-0 top-1/2 translate-x-1/2 -translate-y-1/2 border-r border-t',
            default  => 'top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 border-l border-t', // bottom
        };

        return FieldVariants::join(
            'absolute w-2 h-2 rotate-45 bg-base-100 border-base-300',
            $position,
        );
    }
}
