<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class TimelineComposer
{
    public static function compose(array $props): array
    {
        $orientation = $props['orientation'] ?? 'vertical';
        $compact     = (bool) ($props['compact'] ?? false);
        $snap        = (bool) ($props['snap'] ?? false);
        $appearance  = $props['appearance'] ?? 'solid';

        return [
            'root'        => self::root($orientation, $compact, $snap),
            'orientation' => self::orientationKey($orientation),
            'middle'      => 'timeline-middle',
            'box'         => 'timeline-box',
            'stateColors' => self::stateColors($appearance),
            'hrColors'    => self::hrColors($appearance),
        ];
    }

    private static function root(string $orientation, bool $compact, bool $snap): string
    {
        $parts = array_filter([
            'timeline',
            self::orientationClass($orientation),
            $compact ? 'timeline-compact' : '',
            $snap ? 'timeline-snap-icon' : '',
        ], fn ($s) => $s !== '');

        return implode(' ', $parts);
    }

    private static function orientationClass(string $orientation): string
    {
        return match ($orientation) {
            'horizontal' => 'timeline-horizontal',
            default      => 'timeline-vertical',
        };
    }

    /**
     * Normalised orientation key — unknown values fall back to 'vertical'.
     */
    private static function orientationKey(string $orientation): string
    {
        return $orientation === 'horizontal' ? 'horizontal' : 'vertical';
    }

    /**
     * State → middle-icon color class.
     *
     * `appearance='soft'` (default since v0.3.0): done/default segments use
     * `text-primary/70` so a list of many done items reads as a calm gradient
     * rather than a saturated wall of colour.
     * `appearance='solid'`: pre-v0.3 behaviour, done/default use full `text-primary`.
     * `current` and `upcoming` are independent of appearance.
     */
    private static function stateColors(string $appearance): string
    {
        $done = $appearance === 'solid' ? 'text-primary' : 'text-primary/70';

        return implode('|', [
            "done={$done}",
            'current=text-base-content',
            'upcoming=text-base-content/40',
            "default={$done}",
        ]);
    }

    /**
     * State → connector (<hr>) color class.
     *
     * `appearance='soft'` (default since v0.3.0): done segments use
     * `bg-primary/30` for a muted connector. `appearance='solid'` keeps
     * the pre-v0.3 full `bg-primary` line.
     */
    private static function hrColors(string $appearance): string
    {
        $done = $appearance === 'solid' ? 'bg-primary' : 'bg-primary/30';

        return implode('|', [
            "done={$done}",
            'current=bg-base-content/30',
            'upcoming=bg-base-content/15',
            "default={$done}",
        ]);
    }

    /**
     * Helper for blade: pick a class out of the pipe-joined map.
     */
    public static function pick(string $map, ?string $key): string
    {
        $pairs = [];
        foreach (explode('|', $map) as $kv) {
            [$k, $v] = array_pad(explode('=', $kv, 2), 2, '');
            $pairs[$k] = $v;
        }
        return $pairs[$key] ?? ($pairs['default'] ?? '');
    }
}
