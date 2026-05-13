<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class RatingComposer
{
    public static function compose(array $props): array
    {
        $size  = $props['size']  ?? 'md';
        $color = $props['color'] ?? 'warning';
        $shape = $props['shape'] ?? 'star';
        $half  = (bool) ($props['half'] ?? false);

        $sizeClass  = self::sizeClass($size);
        $shapeClass = self::shapeClass($shape);
        $bgClass    = self::bgColorClass($color);
        $halfClass  = $half ? 'rating-half' : '';

        return [
            'root'       => self::join('rating', $sizeClass, $halfClass),
            'item'       => self::join('mask', $shapeClass, $bgClass),
            'itemHalf1'  => self::join('mask', $shapeClass, 'mask-half-1', $bgClass),
            'itemHalf2'  => self::join('mask', $shapeClass, 'mask-half-2', $bgClass),
            'hidden'     => 'rating-hidden',
        ];
    }

    private static function sizeClass(string $size): string
    {
        // daisyUI 5: `rating-half` only works combined with an explicit size class
        // (`.rating-half.rating-md :not(.rating-hidden){width:.75rem}` etc.). Without
        // it, half inputs fall back to the 1.5rem default width and overlap each other,
        // collapsing the row height. So always emit the size class — including md.
        return match ($size) {
            'xs' => 'rating-xs',
            'sm' => 'rating-sm',
            'lg' => 'rating-lg',
            'xl' => 'rating-xl',
            default => 'rating-md',
        };
    }

    private static function shapeClass(string $shape): string
    {
        return match ($shape) {
            'heart'  => 'mask-heart',
            'circle' => 'mask-circle',
            default  => 'mask-star',
        };
    }

    private static function bgColorClass(string $color): string
    {
        return match ($color) {
            'primary'   => 'bg-primary',
            'secondary' => 'bg-secondary',
            'accent'    => 'bg-accent',
            'info'      => 'bg-info',
            'success'   => 'bg-success',
            'error'     => 'bg-error',
            default     => 'bg-warning',
        };
    }

    private static function join(string ...$parts): string
    {
        $filtered = array_filter($parts, fn ($p) => $p !== '');
        return implode(' ', $filtered);
    }
}
