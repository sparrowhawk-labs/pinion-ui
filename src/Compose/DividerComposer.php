<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class DividerComposer
{
    public static function compose(array $props): array
    {
        $direction = $props['direction'] ?? 'horizontal';
        $color     = $props['color'] ?? null;
        $position  = $props['position'] ?? 'center';

        $classes = ['divider'];

        // direction
        // daisyUI uses `divider-horizontal` for a *vertical* line inside flex row.
        // The default (no modifier) is a horizontal line stacked between blocks.
        // We expose this through our `direction` prop where:
        //   direction=horizontal → no class (the default daisyUI behaviour)
        //   direction=vertical   → `divider-horizontal` (daisyUI's name for an in-flex vertical bar)
        if ($direction === 'vertical') {
            $classes[] = 'divider-horizontal';
        }

        // color
        if ($color !== null && $color !== '') {
            $classes[] = "divider-{$color}";
        }

        // position
        if ($position === 'start') {
            $classes[] = 'divider-start';
        } elseif ($position === 'end') {
            $classes[] = 'divider-end';
        }

        return [
            'root' => implode(' ', $classes),
        ];
    }
}
