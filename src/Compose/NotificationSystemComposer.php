<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class NotificationSystemComposer
{
    public static function compose(array $props): array
    {
        $position   = $props['position'] ?? 'bottom-right';
        $appearance = $props['appearance'] ?? 'bordered-left';
        $size       = $props['size'] ?? 'md';

        return [
            'wrapper'        => self::wrapper($position),
            'item'           => self::item($size),
            'iconWrap'       => 'shrink-0',
            'iconSize'       => self::iconSize($size),
            'content'        => 'flex-1 min-w-0',
            'closeBtn'       => self::closeBtn($size),
            'variantInfo'    => self::variant($appearance, 'info'),
            'variantSuccess' => self::variant($appearance, 'success'),
            'variantWarning' => self::variant($appearance, 'warning'),
            'variantError'   => self::variant($appearance, 'error'),
            'iconColorInfo'    => self::iconColor($appearance, 'info'),
            'iconColorSuccess' => self::iconColor($appearance, 'success'),
            'iconColorWarning' => self::iconColor($appearance, 'warning'),
            'iconColorError'   => self::iconColor($appearance, 'error'),
        ];
    }

    private static function wrapper(string $position): string
    {
        $place = match ($position) {
            'top-right'     => 'top-0 right-0 items-end',
            'top-left'      => 'top-0 left-0 items-start',
            'top-center'    => 'top-0 left-1/2 -translate-x-1/2 items-center',
            'bottom-left'   => 'bottom-0 left-0 items-start',
            'bottom-center' => 'bottom-0 left-1/2 -translate-x-1/2 items-center',
            default         => 'bottom-0 right-0 items-end',
        };

        return FieldVariants::join(
            'fixed z-50 flex flex-col gap-3 max-w-sm w-full px-4 py-4',
            'pointer-events-none',
            $place,
        );
    }

    private static function item(string $size): string
    {
        $sizePart = match ($size) {
            'sm' => 'min-h-[var(--h-field-sm)] px-[var(--px-field-sm)] py-2 text-[length:var(--text-field-sm)] gap-2',
            'lg' => 'min-h-[var(--h-field-lg)] px-[var(--px-field-lg)] py-3 text-[length:var(--text-field-lg)] gap-4',
            default => 'min-h-[var(--h-field-md)] px-[var(--px-field-md)] py-2.5 text-[length:var(--text-field-md)] gap-3',
        };
        return FieldVariants::join(
            'pointer-events-auto flex items-start',
            'rounded-[var(--radius-box)] tune-border',
            'shadow-lg',
            $sizePart,
        );
    }

    private static function iconSize(string $size): string
    {
        return match ($size) {
            'sm' => 'w-4 h-4',
            'lg' => 'w-6 h-6',
            default => 'w-5 h-5',
        };
    }

    private static function closeBtn(string $size): string
    {
        $sizePart = match ($size) {
            'sm' => 'w-5 h-5 text-base',
            'lg' => 'w-7 h-7 text-2xl',
            default => 'w-6 h-6 text-xl',
        };
        return FieldVariants::join(
            'shrink-0 inline-flex items-center justify-center',
            'rounded-[var(--radius-field)] opacity-60 hover:opacity-100',
            'transition-opacity cursor-pointer leading-none',
            $sizePart,
        );
    }

    public static function iconColor(string $appearance, string $type): string
    {
        if ($appearance === 'solid') {
            return match ($type) {
                'success' => 'text-success-content',
                'warning' => 'text-warning-content',
                'error'   => 'text-error-content',
                default   => 'text-info-content',
            };
        }
        return match ($type) {
            'success' => 'text-success',
            'warning' => 'text-warning',
            'error'   => 'text-error',
            default   => 'text-info',
        };
    }

    public static function variant(string $appearance, string $type): string
    {
        return match ("{$appearance}-{$type}") {
            'solid-info'    => 'bg-info text-info-content border-info',
            'solid-success' => 'bg-success text-success-content border-success',
            'solid-warning' => 'bg-warning text-warning-content border-warning',
            'solid-error'   => 'bg-error text-error-content border-error',

            'outline-info'    => 'bg-base-100 text-base-content border-info',
            'outline-success' => 'bg-base-100 text-base-content border-success',
            'outline-warning' => 'bg-base-100 text-base-content border-warning',
            'outline-error'   => 'bg-base-100 text-base-content border-error',

            'soft-info'    => 'bg-info/15 text-base-content border-info/40',
            'soft-success' => 'bg-success/15 text-base-content border-success/40',
            'soft-warning' => 'bg-warning/15 text-base-content border-warning/40',
            'soft-error'   => 'bg-error/15 text-base-content border-error/40',

            'bordered-left-info'    => 'bg-base-100 text-base-content border-base-content/10 border-l-4 border-l-info',
            'bordered-left-success' => 'bg-base-100 text-base-content border-base-content/10 border-l-4 border-l-success',
            'bordered-left-warning' => 'bg-base-100 text-base-content border-base-content/10 border-l-4 border-l-warning',
            'bordered-left-error'   => 'bg-base-100 text-base-content border-base-content/10 border-l-4 border-l-error',

            default => 'bg-base-100 text-base-content border-base-content/10 border-l-4 border-l-info',
        };
    }
}
