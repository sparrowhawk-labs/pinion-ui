<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class TabsComposer
{
    public static function compose(array $props): array
    {
        $variant = $props['variant'] ?? 'underline';
        $size    = $props['size']    ?? 'md';

        return [
            'root'      => 'w-full',
            'tabList'   => self::tabList($variant),
            'tabBase'   => self::tabBase($size),
            'tabActive' => self::tabActive($variant),
            'tabIdle'   => self::tabIdle($variant),
            'iconWrap'  => 'inline-flex items-center gap-inline',
            'panels'    => 'mt-[var(--space-compact)]',
        ];
    }

    private static function tabList(string $variant): string
    {
        $list = match ($variant) {
            'boxed' => 'bg-base-200/50 p-1 rounded-[var(--radius-box)] gap-1',
            'pill'  => 'gap-1',
            default => 'border-b border-base-300 gap-0',
        };
        return 'flex '.$list;
    }

    private static function tabBase(string $size): string
    {
        $base = 'relative inline-flex items-center font-medium transition-colors cursor-pointer whitespace-nowrap';
        return FieldVariants::join($base, self::sizeClasses($size));
    }

    private static function sizeClasses(string $size): string
    {
        return match ($size) {
            'sm' => 'h-[var(--h-field-sm)] px-[var(--px-field-sm)] text-[length:var(--text-field-sm)]',
            'lg' => 'h-[var(--h-field-lg)] px-[var(--px-field-lg)] text-[length:var(--text-field-lg)]',
            default => 'h-[var(--h-field-md)] px-[var(--px-field-md)] text-[length:var(--text-field-md)]',
        };
    }

    private static function tabActive(string $variant): string
    {
        return match ($variant) {
            'boxed' => 'bg-primary text-primary-content rounded-[var(--radius-field)]',
            'pill'  => 'bg-base-200 text-base-content rounded-[var(--radius-selector)]',
            default => 'text-primary border-b-2 border-primary',
        };
    }

    private static function tabIdle(string $variant): string
    {
        return match ($variant) {
            'boxed' => 'text-base-content/60 hover:text-base-content hover:bg-base-200/50 rounded-[var(--radius-field)]',
            'pill'  => 'text-base-content/60 hover:text-base-content hover:bg-base-200/50 rounded-[var(--radius-selector)]',
            default => 'text-base-content/60 hover:text-base-content border-b-2 border-transparent',
        };
    }
}
