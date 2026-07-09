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
        // 'pn-range' + modifiers — daisyUI-free (CLAUDE.md invariant 6). See
        // the ".pn-range" block in pinion-ui.css for the native <input
        // type=range> pseudo-element styling this keys off.
        return FieldVariants::join(
            'pn-range w-full',
            self::colorClass($color),
            self::sizeClass($size),
        );
    }

    private static function colorClass(string $color): string
    {
        return match ($color) {
            'primary'   => 'pn-range-primary',
            'secondary' => 'pn-range-secondary',
            'accent'    => 'pn-range-accent',
            'neutral'   => 'pn-range-neutral',
            'info'      => 'pn-range-info',
            'success'   => 'pn-range-success',
            'warning'   => 'pn-range-warning',
            'error'     => 'pn-range-error',
            default     => 'pn-range-primary',
        };
    }

    private static function sizeClass(string $size): string
    {
        return match ($size) {
            'xs' => 'pn-range-xs',
            'sm' => 'pn-range-sm',
            'lg' => 'pn-range-lg',
            'xl' => 'pn-range-xl',
            default => '', // 'md' is the stock size — no modifier needed
        };
    }
}
