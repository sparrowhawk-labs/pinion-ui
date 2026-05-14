<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class IndicatorComposer
{
    public static function compose(array $props): array
    {
        $position = $props['position'] ?? 'top-end';
        $dot      = array_key_exists('dot', $props) ? (bool) $props['dot'] : false;
        $color    = $props['color']    ?? 'error';

        return [
            'root' => 'indicator',
            'item' => self::item($position, $dot, $color),
        ];
    }

    private static function item(string $position, bool $dot, ?string $color): string
    {
        $parts = array_filter([
            'indicator-item',
            self::positionClass($position),
            self::badgeClasses($color, $dot),
        ], fn ($s) => $s !== '');

        return implode(' ', $parts);
    }

    private static function positionClass(string $position): string
    {
        return match ($position) {
            'top-start'      => 'indicator-top indicator-start',
            'top-center'     => 'indicator-top indicator-center',
            'middle-start'   => 'indicator-middle indicator-start',
            'middle-center'  => 'indicator-middle indicator-center',
            'middle-end'     => 'indicator-middle indicator-end',
            'bottom-start'   => 'indicator-bottom indicator-start',
            'bottom-center'  => 'indicator-bottom indicator-center',
            'bottom-end'     => 'indicator-bottom indicator-end',
            default          => 'indicator-top indicator-end',
        };
    }

    private static function badgeClasses(?string $color, bool $dot): string
    {
        $colorClass = match ($color) {
            'primary'   => 'badge-primary',
            'secondary' => 'badge-secondary',
            'accent'    => 'badge-accent',
            'neutral'   => 'badge-neutral',
            'info'      => 'badge-info',
            'success'   => 'badge-success',
            'warning'   => 'badge-warning',
            'error'     => 'badge-error',
            default     => 'badge-error',
        };

        $parts = ['badge', $colorClass];
        if ($dot) {
            $parts[] = 'badge-xs';
        }

        return implode(' ', $parts);
    }
}
