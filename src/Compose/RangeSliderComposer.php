<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class RangeSliderComposer
{
    public static function compose(array $props): array
    {
        $color = $props['color'] ?? 'primary';
        $size  = $props['size']  ?? 'md';
        $error = $props['error'] ?? null;

        return [
            'input'      => self::inputClass($color, $size),
            'labelColor' => FieldVariants::labelColor($error),
            'hintColor'  => FieldVariants::hintColor($error),
        ];
    }

    private static function inputClass(string $color, string $size): string
    {
        return FieldVariants::join(
            'range w-full',
            self::colorClass($color),
            self::sizeClass($size),
        );
    }

    private static function colorClass(string $color): string
    {
        return match ($color) {
            'primary'   => 'range-primary',
            'secondary' => 'range-secondary',
            'accent'    => 'range-accent',
            'neutral'   => 'range-neutral',
            'info'      => 'range-info',
            'success'   => 'range-success',
            'warning'   => 'range-warning',
            'error'     => 'range-error',
            default     => 'range-primary',
        };
    }

    private static function sizeClass(string $size): string
    {
        return match ($size) {
            'xs' => 'range-xs',
            'sm' => 'range-sm',
            'lg' => 'range-lg',
            'xl' => 'range-xl',
            default => '', // 'md' is daisyUI's stock size — no modifier needed
        };
    }
}
