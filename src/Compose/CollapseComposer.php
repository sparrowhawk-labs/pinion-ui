<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class CollapseComposer
{
    public static function compose(array $props): array
    {
        $icon     = array_key_exists('icon', $props) ? $props['icon'] : null;
        $bordered = array_key_exists('bordered', $props) ? (bool) $props['bordered'] : true;

        return [
            'root'    => self::root($bordered),
            // Label wrapping the hidden checkbox's header text. Toggle affordance
            // (chevron / plus-minus) lives inside as a nested element, so the
            // rotate/scale on it is driven via an arbitrary peer-checked
            // descendant selector here (peer-checked only reaches direct
            // siblings of the checkbox — the label is that sibling, the icon
            // is a descendant of the label).
            'title'   => self::title($icon),
            // Grid-rows trick: 0fr→1fr on the checkbox's :checked state gives a
            // pure-CSS height transition with no JS and no fixed max-height.
            'panel'   => 'grid grid-rows-[0fr] peer-checked:grid-rows-[1fr] transition-[grid-template-rows] duration-300 ease-in-out',
            'wrap'    => 'overflow-hidden',
            'content' => 'px-4 pb-4 text-sm',
            'icon'    => self::iconClass($icon),
        ];
    }

    private static function root(bool $bordered): string
    {
        $parts = array_filter([
            'radius-box overflow-hidden',
            $bordered ? 'border border-base-300' : '',
            'bg-base-100',
        ], fn ($s) => $s !== '');

        return implode(' ', $parts);
    }

    private static function title(?string $icon): string
    {
        $base = 'flex w-full cursor-pointer select-none items-center justify-between gap-2 px-4 py-3 font-semibold';

        return match ($icon) {
            'arrow' => $base.' peer-checked:[&>svg]:rotate-180',
            'plus'  => $base.' peer-checked:[&_.cc-bar-v]:scale-y-0',
            default => $base,
        };
    }

    private static function iconClass(?string $icon): string
    {
        return match ($icon) {
            'plus'  => 'relative inline-block h-4 w-4 shrink-0',
            'arrow' => 'w-4 h-4 shrink-0 transition-transform duration-300',
            default => '', // null or unknown → no icon modifier
        };
    }
}
