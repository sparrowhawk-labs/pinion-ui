<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class SpinnerComposer
{
    public static function compose(array $props): array
    {
        $variant = $props['variant'] ?? 'spinner';
        $size    = $props['size']    ?? 'md';
        $color   = $props['color']   ?? null;

        return [
            'root' => self::root($variant, $size, $color),
        ];
    }

    private static function root(string $variant, string $size, ?string $color): string
    {
        $parts = array_filter([
            'loading',
            self::variantClass($variant),
            self::sizeClass($size),
            self::colorClass($color),
        ], fn ($s) => $s !== '');

        return implode(' ', $parts);
    }

    private static function variantClass(string $variant): string
    {
        return match ($variant) {
            'dots'     => 'loading-dots',
            'ring'     => 'loading-ring',
            'bars'     => 'loading-bars',
            'ball'     => 'loading-ball',
            'infinity' => 'loading-infinity',
            default    => 'loading-spinner',
        };
    }

    private static function sizeClass(string $size): string
    {
        return match ($size) {
            'xs' => 'loading-xs',
            'sm' => 'loading-sm',
            'lg' => 'loading-lg',
            'xl' => 'loading-xl',
            default => 'loading-md',
        };
    }

    private static function colorClass(?string $color): string
    {
        return match ($color) {
            'primary'   => 'text-primary',
            'secondary' => 'text-secondary',
            'accent'    => 'text-accent',
            'info'      => 'text-info',
            'success'   => 'text-success',
            'warning'   => 'text-warning',
            'error'     => 'text-error',
            default     => '',
        };
    }
}
