<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class AvatarGroupComposer
{
    public static function compose(array $props): array
    {
        $spacing = $props['spacing'] ?? 'normal';

        return [
            'root' => 'avatar-group ' . self::spacingClass($spacing),
        ];
    }

    private static function spacingClass(string $spacing): string
    {
        return match ($spacing) {
            'tight' => '-space-x-6',
            'loose' => '-space-x-2',
            default => '-space-x-4',
        };
    }
}
