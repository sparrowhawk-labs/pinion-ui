<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class KbdComposer
{
    public static function compose(array $props): array
    {
        $size       = $props['size'] ?? 'md';
        $appearance = $props['appearance'] ?? 'default';

        return [
            'root' => self::root($size, $appearance),
        ];
    }

    private static function root(string $size, string $appearance): string
    {
        // Structural box shape (radius/height/width/padding/font) is plain
        // Tailwind, reproducing daisyUI's `.kbd`/`.kbd-{size}` computed values
        // (per docs/daisyui/pages/daisyui-5-components__4.md `kbd` entry +
        // demo/node_modules/daisyui/components/kbd.css): border-radius
        // var(--radius-field), height/min-width per size step, padding-inline
        // .5em, font-size per size step. `rounded-[var(--radius-field)]`
        // keeps the box tune-reactive like the rest of the repo's convention.
        $parts = array_filter([
            'inline-flex items-center justify-center align-middle',
            'rounded-[var(--radius-field)] px-[.5em]',
            self::sizeClass($size),
            self::appearanceClass($appearance),
        ], fn ($s) => $s !== '');

        return implode(' ', $parts);
    }

    private static function sizeClass(string $size): string
    {
        return match ($size) {
            'xs'    => 'h-4 min-w-4 text-[0.625rem]',
            'sm'    => 'h-5 min-w-5 text-xs',
            'lg'    => 'h-7 min-w-7 text-base',
            'xl'    => 'h-8 min-w-8 text-lg',
            default => 'h-6 min-w-6 text-sm',
        };
    }

    private static function appearanceClass(string $appearance): string
    {
        return match ($appearance) {
            // `soft` / `outline` keep their previous (pre-migration) visual
            // intent verbatim — only `default` needed a replacement for the
            // daisyUI `.kbd` chrome (bg-base-200 + hairline border + a
            // thicker bottom border to read as a physical key cap).
            'soft'    => 'bg-base-200 text-base-content border-0',
            'outline' => 'border border-base-content/30 bg-transparent',
            default   => 'bg-base-200 border border-base-content/20 border-b-2',
        };
    }
}
