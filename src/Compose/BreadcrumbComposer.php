<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class BreadcrumbComposer
{
    public static function compose(array $props): array
    {
        $separator = $props['separator'] ?? 'chevron';
        $size      = $props['size'] ?? 'md';

        return [
            'root' => self::root($size, $separator),
        ];
    }

    private static function root(string $size, string $separator): string
    {
        $parts = array_filter([
            'breadcrumbs',
            self::sizeClass($size),
            self::separatorClass($separator),
        ], fn ($s) => $s !== '');

        return implode(' ', $parts);
    }

    private static function sizeClass(string $size): string
    {
        return match ($size) {
            'sm' => 'text-sm',
            'lg' => 'text-lg',
            default => '',
        };
    }

    private static function separatorClass(string $separator): string
    {
        // chevron is the daisyUI default — no override needed.
        // slash: override the ::before pseudo-element to render '/' instead of
        // the default rotated bordered square. Reset borders, rotation, and
        // sizing; set content to '/' and tweak margins so it sits inline.
        return match ($separator) {
            'slash' => "[&_li+li]:before:content-['/'] [&_li+li]:before:border-0 [&_li+li]:before:rotate-0 [&_li+li]:before:w-auto [&_li+li]:before:h-auto [&_li+li]:before:mx-2 [&_li+li]:before:opacity-40",
            default => '',
        };
    }
}
