<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class RatingComposer
{
    /**
     * Star path lifted verbatim from daisyUI 5's own `.mask-star` data URI
     * (`node_modules/daisyui/daisyui.css`) so the SVG-rendered star is pixel-
     * identical to what daisyUI used to mask. viewBox 0 0 192 180.
     */
    private const STAR_PATH = 'm96 137.263-58.779 42.024 22.163-68.389L.894 68.481l72.476-.243L96 0l22.63 68.238 72.476.243-58.49 42.417 22.163 68.389z';

    /**
     * Heart path lifted verbatim from daisyUI 5's `.mask-heart` data URI.
     * viewBox 0 0 200 185.
     */
    private const HEART_PATH = 'M100 184.606a15.384 15.384 0 0 1-8.653-2.678C53.565 156.28 37.205 138.695 28.182 127.7 8.952 104.264-.254 80.202.005 54.146.308 24.287 24.264 0 53.406 0c21.192 0 35.869 11.937 44.416 21.879a2.884 2.884 0 0 0 4.356 0C110.725 11.927 125.402 0 146.594 0c29.142 0 53.098 24.287 53.4 54.151.26 26.061-8.956 50.122-28.176 73.554-9.023 10.994-25.383 28.58-63.165 54.228a15.384 15.384 0 0 1-8.653 2.673Z';

    public static function compose(array $props): array
    {
        $size  = $props['size']  ?? 'md';
        $color = $props['color'] ?? 'warning';
        $half  = (bool) ($props['half'] ?? false);

        $fill = self::fillClass($color);
        $dims = self::dims($size);

        return [
            // No daisyUI `rating`/`rating-{size}` class — sizing now lives on the
            // per-item input/star classes below, not the root.
            'root' => 'pn-rating inline-flex items-center',

            // "Clear rating" radio (value=0). No <svg> star follows it — it's a
            // dead-zone click target only, same role as daisyUI's `rating-hidden`.
            'hidden' => 'pn-rating-hidden appearance-none cursor-pointer opacity-0 w-2 flex-none',

            // Invisible radio sized to exactly match its <svg> star footprint
            // (see .pn-rating CSS: the star is pulled back on top of it via a
            // matching negative margin — no position:absolute/wrapper needed).
            'input' => "pn-rating-input appearance-none cursor-pointer opacity-0 flex-none {$dims['fullBox']}",
            'inputHalf' => "pn-rating-input appearance-none cursor-pointer opacity-0 flex-none {$dims['halfBox']}",

            // The visible star/heart/circle, rendered as real inline SVG (see
            // rating.blade.php) — no daisyUI `mask`/`mask-star` class involved.
            'star' => "pn-rating-star flex-none {$fill} {$dims['fullBox']} {$dims['fullMl']}",
            'starHalf' => "pn-rating-star flex-none {$fill} {$dims['halfBox']} {$dims['halfMl']}",
        ];
    }

    /**
     * Shape geometry (path data + viewBox), kept OUT of compose()'s class-string
     * dict per the Compose purity invariant — mirrors the StatComposer::arrowChar
     * precedent of a small pure non-class helper the Blade view calls directly.
     *
     * Returns markup-shape data, not classes: `path`/`circle`, `viewBox`, and the
     * two half-crop viewBoxes (left/right half of the shape's own coordinate
     * space) used for `half=true` mode instead of daisyUI's `mask-half-1`/
     * `mask-half-2` (`mask-position`/`mask-size:200%`) trick.
     */
    public static function shapeGeometry(string $shape): array
    {
        return match ($shape) {
            'heart' => [
                'tag' => 'path',
                'path' => self::HEART_PATH,
                'fillRule' => 'nonzero',
                'width' => 200,
                'height' => 185,
            ],
            'circle' => [
                'tag' => 'circle',
                'cx' => 100,
                'cy' => 100,
                'r' => 100,
                'width' => 200,
                'height' => 200,
            ],
            default => [
                'tag' => 'path',
                'path' => self::STAR_PATH,
                'fillRule' => 'evenodd',
                'width' => 192,
                'height' => 180,
            ],
        };
    }

    public static function viewBox(array $geo): string
    {
        return "0 0 {$geo['width']} {$geo['height']}";
    }

    public static function halfViewBoxes(array $geo): array
    {
        $halfW = $geo['width'] / 2;

        return [
            "0 0 {$halfW} {$geo['height']}",
            "{$halfW} 0 {$halfW} {$geo['height']}",
        ];
    }

    private static function fillClass(string $color): string
    {
        return match ($color) {
            'primary'   => 'fill-primary',
            'secondary' => 'fill-secondary',
            'accent'    => 'fill-accent',
            'info'      => 'fill-info',
            'success'   => 'fill-success',
            'error'     => 'fill-error',
            default     => 'fill-warning',
        };
    }

    /**
     * Full-star and half-star box (width/height) + the matching negative
     * left-margin used to pull the <svg> back on top of its invisible <input>.
     * Values mirror daisyUI 5's own `.rating-{size}` / `.rating-half.rating-{size}`
     * width table (1rem/1.25rem/1.5rem/1.75rem/2rem, half = /2) — which happen to
     * land exactly on Tailwind's spacing scale (w-4..w-8 / w-2..w-4.5).
     */
    private static function dims(string $size): array
    {
        return match ($size) {
            'xs' => ['fullBox' => 'w-4 h-4', 'fullMl' => '-ml-4', 'halfBox' => 'w-2 h-4', 'halfMl' => '-ml-2'],
            'sm' => ['fullBox' => 'w-5 h-5', 'fullMl' => '-ml-5', 'halfBox' => 'w-2.5 h-5', 'halfMl' => '-ml-2.5'],
            'lg' => ['fullBox' => 'w-7 h-7', 'fullMl' => '-ml-7', 'halfBox' => 'w-3.5 h-7', 'halfMl' => '-ml-3.5'],
            'xl' => ['fullBox' => 'w-8 h-8', 'fullMl' => '-ml-8', 'halfBox' => 'w-4 h-8', 'halfMl' => '-ml-4'],
            default => ['fullBox' => 'w-6 h-6', 'fullMl' => '-ml-6', 'halfBox' => 'w-3 h-6', 'halfMl' => '-ml-3'],
        };
    }
}
