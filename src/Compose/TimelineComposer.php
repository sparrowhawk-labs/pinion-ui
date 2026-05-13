<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class TimelineComposer
{
    public static function compose(array $props): array
    {
        $orientation = $props['orientation'] ?? 'vertical';
        $compact     = (bool) ($props['compact'] ?? false);
        $snap        = (bool) ($props['snap'] ?? false);

        return [
            'root'        => self::root($orientation, $compact, $snap),
            'orientation' => self::orientationKey($orientation),
            'middle'      => 'timeline-middle',
            'box'         => 'timeline-box',
            'stateColors' => self::stateColors(),
            'hrColors'    => self::hrColors(),
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
     * State → middle-icon color class. `done` uses primary, `current` uses
     * full base-content (no fade), `upcoming` fades the icon. Default
     * (no state) renders as `done` so a plain item still looks complete.
     */
    private static function stateColors(): string
    {
        return implode('|', [
            'done=text-primary',
            'current=text-base-content',
            'upcoming=text-base-content/40',
            'default=text-primary',
        ]);
    }

    /**
     * State → connector (<hr>) color class. Done segments use primary;
     * everything else stays subdued.
     */
    private static function hrColors(): string
    {
        return implode('|', [
            'done=bg-primary',
            'current=bg-base-content/30',
            'upcoming=bg-base-content/15',
            'default=bg-primary',
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
