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
        // No daisyUI `loading`/`loading-*` classes here (CLAUDE.md invariant
        // 6). `spinner` is pinion-ui's own base class — see the
        // "spinner (<x-spinner>)" section in pinion-ui.css for the
        // mask-image + SMIL mechanism that replaces daisyUI's component CSS.
        $parts = array_filter([
            'spinner',
            self::variantClass($variant),
            self::sizeClass($size),
            self::colorClass($color),
        ], fn ($s) => $s !== '');

        return implode(' ', $parts);
    }

    private static function variantClass(string $variant): string
    {
        return match ($variant) {
            'dots'     => 'spinner-dots',
            'ring'     => 'spinner-ring',
            'bars'     => 'spinner-bars',
            'ball'     => 'spinner-ball',
            'infinity' => 'spinner-infinity',
            default    => 'spinner-spinner',
        };
    }

    private static function sizeClass(string $size): string
    {
        return match ($size) {
            'xs' => 'spinner-xs',
            'sm' => 'spinner-sm',
            'lg' => 'spinner-lg',
            'xl' => 'spinner-xl',
            default => 'spinner-md',
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
