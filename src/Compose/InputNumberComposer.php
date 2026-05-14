<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * InputNumber wraps a numeric `<input type="number">` between a decrement and
 * increment button. Layout is `[−] [   42   ] [＋]` — three adjacent items
 * sharing borders, with the middle radius zeroed and the inner borders
 * collapsed (same technique as ButtonGroup).
 *
 * Increment/decrement logic lives in a small Alpine `x-data` block in the
 * Blade — the composer returns class strings only.
 */
class InputNumberComposer
{
    public static function compose(array $props): array
    {
        $size  = $props['size']  ?? 'md';
        $error = $props['error'] ?? null;

        return [
            'wrapper'    => self::wrapperClass(),
            'button'     => self::buttonClass($size),
            'input'      => self::inputClass($size),
            'labelColor' => FieldVariants::labelColor($error),
            'hintColor'  => FieldVariants::hintColor($error),
        ];
    }

    private static function wrapperClass(): string
    {
        return FieldVariants::join(
            'inline-flex',
            // zero radii on every child, restore on the two ends
            '[&>*]:rounded-none',
            '[&>*:first-child]:rounded-l-[var(--radius-field)]',
            '[&>*:last-child]:rounded-r-[var(--radius-field)]',
            // collapse inner border so adjacent items show a single rule
            '[&>*:not(:last-child)]:border-r-0',
        );
    }

    private static function buttonClass(string $size): string
    {
        $box = match ($size) {
            'xs' => 'w-[var(--h-field-xs)] h-[var(--h-field-xs)]',
            'sm' => 'w-[var(--h-field-sm)] h-[var(--h-field-sm)]',
            'lg' => 'w-[var(--h-field-lg)] h-[var(--h-field-lg)]',
            default => 'w-[var(--h-field-md)] h-[var(--h-field-md)]',
        };

        return FieldVariants::join(
            'shrink-0 inline-flex items-center justify-center',
            'bg-base-100 tune-border border-base-300 text-base-content/70',
            'hover:bg-base-200 hover:text-base-content transition-colors cursor-pointer',
            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:z-10',
            'disabled:opacity-50 disabled:cursor-not-allowed',
            $box,
        );
    }

    private static function inputClass(string $size): string
    {
        $box = match ($size) {
            'xs' => 'h-[var(--h-field-xs)] text-[length:var(--text-field-xs)]',
            'sm' => 'h-[var(--h-field-sm)] text-[length:var(--text-field-sm)]',
            'lg' => 'h-[var(--h-field-lg)] text-[length:var(--text-field-lg)]',
            default => 'h-[var(--h-field-md)] text-[length:var(--text-field-md)]',
        };

        return FieldVariants::join(
            // Width comes from the `size` HTML attribute (computed from
            // max/min/value digit count) — let the input render at its
            // natural size and the surrounding `w-fit` wrapper hugs it.
            'min-w-0 text-center tabular-nums',
            'bg-base-100 tune-border border-base-300',
            'focus:outline-none focus:ring-2 focus:ring-primary focus:z-10',
            // strip the native spinner arrows in WebKit + Firefox
            '[&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none',
            '[appearance:textfield]',
            'disabled:opacity-50 disabled:cursor-not-allowed',
            $box,
        );
    }
}
