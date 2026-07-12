<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class AvatarGroupComposer
{
    public static function compose(array $props): array
    {
        $spacing = $props['spacing'] ?? 'normal';

        // Replaces daisyUI's `avatar-group` structural class (see CLAUDE.md
        // invariant 6 — daisyUI classes are color-utility only now). Plain
        // Tailwind flex row + arbitrary-variant child selectors reproduce the
        // overlap-with-ring effect: negative margin via the `-space-x-*`
        // spacing scale, and a `ring-base-100` border on each child so
        // overlapping avatars stay visually separated (same ring color the
        // avatar's own status dot uses, so it matches the page background).
        return [
            'root' => 'flex ' . self::spacingClass($spacing) . ' [&>*]:ring-2 [&>*]:ring-base-100',
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
