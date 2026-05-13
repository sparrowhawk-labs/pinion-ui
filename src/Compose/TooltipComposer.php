<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class TooltipComposer
{
    public static function compose(array $props): array
    {
        $position = $props['position'] ?? 'top';
        $color    = $props['color']    ?? null;
        $open     = array_key_exists('open', $props) ? (bool) $props['open'] : false;

        return [
            'root' => self::root($position, $color, $open),
        ];
    }

    private static function root(string $position, ?string $color, bool $open): string
    {
        $parts = array_filter([
            'tooltip',
            self::positionClass($position),
            self::colorClass($color),
            $open ? 'tooltip-open' : '',
        ], fn ($s) => $s !== '');

        return implode(' ', $parts);
    }

    private static function positionClass(string $position): string
    {
        return match ($position) {
            'right'  => 'tooltip-right',
            'bottom' => 'tooltip-bottom',
            'left'   => 'tooltip-left',
            default  => 'tooltip-top',
        };
    }

    private static function colorClass(?string $color): string
    {
        // null (default)             → tooltip-light: soft base-200 bubble (no border).
        // 'neutral'                  → no class: falls through to daisyUI stock dark-neutral.
        // 'base-100' / 'base-200' / 'base-300' → explicit surface-tinted variants.
        return match ($color) {
            'primary'   => 'tooltip-primary',
            'secondary' => 'tooltip-secondary',
            'accent'    => 'tooltip-accent',
            'info'      => 'tooltip-info',
            'success'   => 'tooltip-success',
            'warning'   => 'tooltip-warning',
            'error'     => 'tooltip-error',
            'neutral'   => '',
            'base-100'  => 'tooltip-base-100',
            'base-200'  => 'tooltip-base-200',
            'base-300'  => 'tooltip-base-300',
            default     => 'tooltip-light',
        };
    }
}
