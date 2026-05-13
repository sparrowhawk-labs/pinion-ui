<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class CollapseComposer
{
    public static function compose(array $props): array
    {
        $icon     = array_key_exists('icon', $props) ? $props['icon'] : 'arrow';
        $bordered = array_key_exists('bordered', $props) ? (bool) $props['bordered'] : true;

        return [
            'root'    => self::root($icon, $bordered),
            'title'   => 'collapse-title font-semibold',
            'content' => 'collapse-content text-sm',
        ];
    }

    private static function root(?string $icon, bool $bordered): string
    {
        $parts = array_filter([
            'collapse',
            self::iconClass($icon),
            $bordered ? 'border border-base-300' : '',
            'bg-base-100',
        ], fn ($s) => $s !== '');

        return implode(' ', $parts);
    }

    private static function iconClass(?string $icon): string
    {
        return match ($icon) {
            'plus'  => 'collapse-plus',
            'arrow' => 'collapse-arrow',
            default => '', // null or unknown → no icon modifier
        };
    }
}
