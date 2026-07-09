<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * Wraps multiple <x-stat :wrapped="false"> into one joined card — replaces
 * daisyUI's `.stats`/`.stats-vertical`/`.stats-horizontal` container (which
 * consumers previously had to hand-write, exposing raw daisyUI component
 * classes; see StatComposer's doc comment for why that's no longer done).
 * Plain flex + `divide-x`/`divide-y` (a well-tested Tailwind primitive)
 * instead of daisyUI's own divider, which silently computed a 0px border
 * in some theme×tune combinations.
 */
class StatGroupComposer
{
    public static function compose(array $props): array
    {
        $direction = $props['direction'] ?? 'horizontal';
        $shadow    = array_key_exists('shadow', $props) ? (bool) $props['shadow'] : true;

        $direction = in_array($direction, ['horizontal', 'vertical', 'responsive'], true)
            ? $direction : 'horizontal';

        $layout = match ($direction) {
            'vertical'   => 'flex flex-col divide-y divide-base-content/10',
            'responsive' => 'flex flex-col lg:flex-row divide-y divide-base-content/10 lg:divide-y-0 lg:divide-x',
            default      => 'flex divide-x divide-base-content/10',
        };

        return [
            'root' => FieldVariants::join(
                'rounded-[var(--radius-box)] tune-border border-base-content/10 bg-base-100 overflow-hidden',
                $shadow ? 'shadow-[var(--shadow-box)]' : '',
                $layout,
            ),
        ];
    }
}
