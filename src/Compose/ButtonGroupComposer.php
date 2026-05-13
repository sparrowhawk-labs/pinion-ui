<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class ButtonGroupComposer
{
    public static function compose(array $props): array
    {
        $orientation = $props['orientation'] ?? 'horizontal';

        return [
            'root' => self::root($orientation),
        ];
    }

    private static function root(string $orientation): string
    {
        $parts = array_filter([
            'join',
            self::orientationClass($orientation),
        ], fn ($s) => $s !== '');

        return implode(' ', $parts);
    }

    private static function orientationClass(string $orientation): string
    {
        // 'horizontal' is daisyUI's default direction for `.join`, so no extra class.
        // Unknown values fall back to horizontal for safety.
        return match ($orientation) {
            'vertical' => 'join-vertical',
            default    => '',
        };
    }
}
