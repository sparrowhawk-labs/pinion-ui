<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class StatComposer
{
    public static function compose(array $props): array
    {
        $valueColor = $props['valueColor'] ?? null;
        $trend      = $props['trend']      ?? null;
        $wrapped    = array_key_exists('wrapped', $props) ? (bool) $props['wrapped'] : true;

        return [
            'root'   => $wrapped ? 'stats shadow' : '',
            'inner'  => 'stat',
            'figure' => self::figureClass($valueColor),
            'title'  => 'stat-title',
            'value'  => self::valueClass($valueColor),
            'desc'   => self::descClass($trend),
            'arrow'  => self::arrowChar($trend),
        ];
    }

    private static function valueClass(?string $color): string
    {
        $base = 'stat-value';

        return match ($color) {
            'primary'   => $base.' text-primary',
            'secondary' => $base.' text-secondary',
            'accent'    => $base.' text-accent',
            'info'      => $base.' text-info',
            'success'   => $base.' text-success',
            'warning'   => $base.' text-warning',
            'error'     => $base.' text-error',
            default     => $base,
        };
    }

    private static function figureClass(?string $color): string
    {
        $base = 'stat-figure';

        return match ($color) {
            'primary'   => $base.' text-primary',
            'secondary' => $base.' text-secondary',
            'accent'    => $base.' text-accent',
            'info'      => $base.' text-info',
            'success'   => $base.' text-success',
            'warning'   => $base.' text-warning',
            'error'     => $base.' text-error',
            default     => $base.' text-base-content/60',
        };
    }

    private static function descClass(?string $trend): string
    {
        $base = 'stat-desc';

        return match ($trend) {
            'up'   => $base.' text-success',
            'down' => $base.' text-error',
            'flat' => $base.' text-base-content/50',
            default => $base,
        };
    }

    private static function arrowChar(?string $trend): string
    {
        return match ($trend) {
            'up'   => '↑',
            'down' => '↓',
            'flat' => '→',
            default => '',
        };
    }
}
