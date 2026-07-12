<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class DividerComposer
{
    public static function compose(array $props): array
    {
        $direction = $props['direction'] ?? 'horizontal';
        $color     = $props['color'] ?? null;
        $position  = $props['position'] ?? 'center';

        $vertical = $direction === 'vertical';

        // Plain-Tailwind reimplementation of daisyUI's `divider` (see
        // CLAUDE.md invariant 6 — no daisyUI structural classes). The visual
        // shape is a flex row (or column, for `direction=vertical`) split
        // into two line segments with an optional centered label between
        // them: line - label - line. daisyUI's own `divider-horizontal`
        // class name is the *vertical*-bar variant (kept only as prose in
        // reference/components/divider.md); this composer keeps our
        // normalized prop naming (`direction=vertical` → vertical bar).
        $borderSide = $vertical ? 'border-l' : 'border-t';
        $borderColor = self::borderColorClass($color);

        // `h-full` (height:100%) fails to resolve against the outer flex
        // row's indefinite height (it's sized by its tallest content child,
        // not an explicit height), which computes to an effectively 'auto'
        // used height that *also* short-circuits the default
        // `align-items:stretch` the row would otherwise apply — the root
        // collapses to its own content height (~40px) instead of stretching
        // to match its siblings (~72px), pushing the label above the true
        // vertical midpoint. Omitting an explicit height lets the outer
        // row's default `align-items:stretch` size the root to match its
        // siblings, so `flex-1` on lineStart/lineEnd then has a real height
        // to distribute and the label lands on the actual center.
        $rootBase = $vertical
            ? 'flex flex-col items-center self-stretch'
            : 'flex items-center w-full';

        // position=start/end shrink the line segment nearest the label's
        // edge to a fixed size (was daisyUI's `divider-start`/`divider-end`),
        // leaving the far segment to grow and fill the remaining space.
        $shortSize = $vertical ? 'h-4' : 'w-4';
        $shortLine = "flex-none {$shortSize} {$borderSide} {$borderColor}";
        $growLine  = "flex-1 {$borderSide} {$borderColor}";

        [$lineStart, $lineEnd] = match ($position) {
            'start' => [$shortLine, $growLine],
            'end'   => [$growLine, $shortLine],
            default => [$growLine, $growLine],
        };

        $labelSpacing = $vertical ? 'py-3' : 'px-3';
        $label = trim("{$labelSpacing} text-xs font-medium shrink-0 ".self::labelColorClass($color));

        return [
            'root'      => $rootBase,
            'lineStart' => $lineStart,
            'lineEnd'   => $lineEnd,
            'label'     => $label,
        ];
    }

    private static function borderColorClass(?string $color): string
    {
        return match ($color) {
            'primary'   => 'border-primary/30',
            'secondary' => 'border-secondary/30',
            'accent'    => 'border-accent/30',
            'neutral'   => 'border-neutral/30',
            'info'      => 'border-info/30',
            'success'   => 'border-success/30',
            'warning'   => 'border-warning/30',
            'error'     => 'border-error/30',
            default     => 'border-base-content/10',
        };
    }

    private static function labelColorClass(?string $color): string
    {
        return match ($color) {
            'primary'   => 'text-primary',
            'secondary' => 'text-secondary',
            'accent'    => 'text-accent',
            'neutral'   => 'text-neutral',
            'info'      => 'text-info',
            'success'   => 'text-success',
            'warning'   => 'text-warning',
            'error'     => 'text-error',
            default     => 'text-base-content/60',
        };
    }
}
