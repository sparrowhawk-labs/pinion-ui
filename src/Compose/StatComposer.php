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
            // daisyUI's bare `shadow` utility is a fixed depth box-shadow; swap
            // it for the tune-driven token so the stat card's elevation takes
            // on each tune's shadow character (hairline / flat / hard / soft).
            'root'   => $wrapped ? 'stats shadow-[var(--shadow-box)]' : '',
            'inner'  => 'stat',
            'figure' => self::figureClass($valueColor),
            'title'  => 'stat-title',
            'value'  => self::valueClass($valueColor),
            'desc'   => self::descClass($trend),
        ];
    }

    /**
     * Trend → arrow character. NOT part of the compose() dict — kept as a
     * separate helper so the dict stays class-strings-only per the Compose
     * invariant (see CLAUDE.md). The Blade view calls this directly.
     */
    public static function arrowChar(?string $trend): string
    {
        return match ($trend) {
            'up'    => '↑',
            'down'  => '↓',
            'flat'  => '→',
            default => '',
        };
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

}
