<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class BreadcrumbComposer
{
    public static function compose(array $props): array
    {
        $separator = $props['separator'] ?? 'chevron';
        $size      = $props['size'] ?? 'md';

        return [
            'root' => self::root($size),
            'list' => self::list($separator),
        ];
    }

    private static function root(string $size): string
    {
        // Plain Tailwind: horizontal scroll for long trails on narrow
        // viewports is the only behavior daisyUI's `breadcrumbs` class
        // contributed to the outer wrapper — everything else (flex row,
        // separators) now lives on the `<ul>` (see list()).
        $parts = array_filter([
            'overflow-x-auto',
            self::sizeClass($size),
        ], fn ($s) => $s !== '');

        return implode(' ', $parts);
    }

    private static function list(string $separator): string
    {
        $parts = array_filter([
            'flex items-center flex-nowrap list-none',
            // Tailwind's preflight sets `svg { display: block }`, so an
            // icon dropped straight into a plain (inline) `<a>`/`<span>`
            // — the documented slot-API pattern for icon+label crumbs —
            // forces the label text onto its own line (a block-level SVG
            // child breaks the inline formatting context). Forcing every
            // `<li>`'s direct `<a>`/`<span>` to `inline-flex` keeps icon +
            // label on one row regardless of whether the crumb has an
            // icon at all; plain-text crumbs are unaffected.
            '[&_li>a]:inline-flex [&_li>a]:items-center [&_li>a]:gap-1 [&_li>span]:inline-flex [&_li>span]:items-center [&_li>span]:gap-1',
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
        // No daisyUI `breadcrumbs` class left to draw the default separator,
        // so both variants are explicit Tailwind arbitrary-variant content
        // on the `::before` pseudo-element of every `li` after the first.
        return match ($separator) {
            'slash' => "[&_li+li]:before:content-['/'] [&_li+li]:before:mx-2 [&_li+li]:before:opacity-40",
            default => "[&_li+li]:before:content-['›'] [&_li+li]:before:mx-2 [&_li+li]:before:opacity-40",
        };
    }
}
