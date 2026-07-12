<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class TabsComposer
{
    public static function compose(array $props): array
    {
        $variant = $props['variant'] ?? 'underline';
        $size    = $props['size']    ?? 'md';

        return [
            'root'      => self::root($variant),
            'tabBase'   => self::tabBase($size),
            'tabActive' => self::tabActive($variant),
            'tabIdle'   => self::tabIdle($variant),
            'iconWrap'  => 'inline-flex items-center gap-xs',
            'panel'     => 'order-1 basis-full mt-sm',
        ];
    }

    private static function root(string $variant): string
    {
        $base = 'w-full flex flex-wrap items-stretch';
        return match ($variant) {
            'boxed' => $base.' gap-1',
            'pill'  => $base.' gap-1',
            default => $base.' gap-0 after:content-[""] after:order-0 after:flex-1 after:border-b after:border-base-300 after:self-stretch',
        };
    }

    private static function tabBase(string $size): string
    {
        $base = 'order-0 relative inline-flex items-center font-medium transition-colors cursor-pointer whitespace-nowrap';
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
