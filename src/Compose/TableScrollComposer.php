<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class TableScrollComposer
{
    public static function compose(array $props): array
    {
        $fadeColor    = $props['fadeColor'] ?? 'base-100';
        $buttonStyle  = $props['buttonStyle'] ?? 'circle';

        return [
            'wrapper'        => 'relative',
            'scrollContainer'=> 'overflow-x-auto',
            'leftFade'       => self::fade('left', $fadeColor),
            'rightFade'      => self::fade('right', $fadeColor),
            'buttonOuter'    => 'absolute top-0 bottom-0 z-10 flex items-center justify-center w-10 cursor-pointer focus:outline-none',
            'buttonOuterLeft'  => 'absolute top-0 bottom-0 left-0 z-10 flex items-center justify-center w-10 cursor-pointer focus:outline-none',
            'buttonOuterRight' => 'absolute top-0 bottom-0 right-0 z-10 flex items-center justify-center w-10 cursor-pointer focus:outline-none',
            'buttonInner'    => self::buttonInner($buttonStyle),
            'iconSize'       => 'w-4 h-4 text-base-content/70',
        ];
    }

    private static function fade(string $direction, string $fadeColor): string
    {
        $gradient = $direction === 'left'
            ? "bg-gradient-to-r from-{$fadeColor} via-{$fadeColor}/90 to-transparent"
            : "bg-gradient-to-l from-{$fadeColor} via-{$fadeColor}/90 to-transparent";
        return "absolute top-0 bottom-0 w-10 pointer-events-none ".$gradient;
    }

    private static function buttonInner(string $style): string
    {
        return match ($style) {
            'flat' => 'inline-flex items-center justify-center w-7 h-7 rounded-[var(--radius-field)] bg-base-200 hover:bg-base-300 transition-colors shadow-sm',
            default => 'inline-flex items-center justify-center w-7 h-7 rounded-full bg-base-100 hover:bg-base-200 transition-colors shadow border border-base-content/10',
        };
    }
}
