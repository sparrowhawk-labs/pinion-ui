<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class AccordionComposer
{
    public static function compose(array $props): array
    {
        $size = $props['size'] ?? 'md';

        $sizeClasses = self::sizeClasses($size);

        return [
            'root'      => 'w-full divide-y divide-base-300 radius-box tune-border border-base-300 overflow-hidden',
            'item'      => '',
            'header'    => FieldVariants::join(
                'w-full flex items-center justify-between',
                $sizeClasses,
                'py-0 font-medium text-base-content',
                'hover:bg-base-200/50 transition-colors cursor-pointer',
            ),
            'sizeClasses' => $sizeClasses,
            'icon'      => 'w-4 h-4 shrink-0 transition-transform duration-200',
            'content'   => FieldVariants::join(
                $sizeClasses,
                'py-sm text-base-content/70',
            ),
        ];
    }

    private static function sizeClasses(string $size): string
    {
        return match ($size) {
            'sm' => 'min-h-[var(--h-field-sm)] px-[var(--px-field-sm)] text-[length:var(--text-field-sm)]',
            'lg' => 'min-h-[var(--h-field-lg)] px-[var(--px-field-lg)] text-[length:var(--text-field-lg)]',
            default => 'min-h-[var(--h-field-md)] px-[var(--px-field-md)] text-[length:var(--text-field-md)]',
        };
    }
}
