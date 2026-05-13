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
        $parts = array_filter([
            'kbd',
            self::sizeClass($size),
            self::appearanceClass($appearance),
        ], fn ($s) => $s !== '');

        return implode(' ', $parts);
    }

    private static function sizeClass(string $size): string
    {
        return match ($size) {
            'xs' => 'kbd-xs',
            'sm' => 'kbd-sm',
            'lg' => 'kbd-lg',
            'xl' => 'kbd-xl',
            default => '',
        };
    }

    private static function appearanceClass(string $appearance): string
    {
        return match ($appearance) {
            'soft'    => 'bg-base-200 text-base-content border-0',
            'outline' => 'border border-base-content/30 bg-transparent',
            default   => '',
        };
    }
}
