<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * PinInput is an OTP / verification-code input — N separate single-character
 * boxes with auto-advance focus, backspace-back, arrow nav, and paste-to-fill.
 * Behaviour lives in the Blade's Alpine x-data; this composer returns
 * class strings for the wrapper and each box.
 *
 * Sizing note: each box is square-ish (`w-{}` paired with `h-[var(--h-field-{})]`)
 * so the row reads as a clean grid regardless of the active tune.
 */
class PinInputComposer
{
    public static function compose(array $props): array
    {
        $size  = $props['size']  ?? 'md';
        $error = $props['error'] ?? null;

        return [
            'wrapper'    => 'flex items-center gap-2',
            'box'        => self::boxClass($size, $error),
            'labelColor' => FieldVariants::labelColor($error),
            'hintColor'  => FieldVariants::hintColor($error),
        ];
    }

    private static function boxClass(string $size, ?string $error): string
    {
        $box = match ($size) {
            'xs' => 'w-8 h-[var(--h-field-xs)] text-base',
            'sm' => 'w-10 h-[var(--h-field-sm)] text-lg',
            'lg' => 'w-14 h-[var(--h-field-lg)] text-2xl',
            default => 'w-12 h-[var(--h-field-md)] text-xl',
        };

        $border = $error
            ? 'tune-border border-error focus:border-error focus:ring-2 focus:ring-error/30'
            : 'tune-border border-base-300 focus:border-primary focus:ring-2 focus:ring-primary/30';

        return FieldVariants::join(
            'rounded-[var(--radius-field)] bg-base-100 text-center font-semibold tabular-nums',
            'focus:outline-none transition-all',
            'disabled:opacity-50 disabled:cursor-not-allowed',
            $box,
            $border,
        );
    }
}
