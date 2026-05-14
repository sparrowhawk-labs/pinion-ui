<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * InputGroup joins arbitrary form-shaped children into a single border-shared
 * row — e.g. `[select country] [input phone]`, `[input] [button submit]`,
 * `[input first] [input last]`. The wrapper handles radii and inner-border
 * collapse via Tailwind arbitrary descendant variants (same self-contained
 * technique as <x-button-group> and <x-input-number>).
 *
 * For text addons (`$`, `https://`, `@example.com`), use the `addon` helper
 * class returned by `addonClass()` on a `<span>` child — it picks the right
 * height for the given `size` token.
 */
class InputGroupComposer
{
    public static function compose(array $props): array
    {
        $size  = $props['size']  ?? 'md';
        $error = $props['error'] ?? null;

        return [
            'wrapper'    => self::wrapperClass(),
            'addon'      => self::addonClass($size),
            'labelColor' => FieldVariants::labelColor($error),
            'hintColor'  => FieldVariants::hintColor($error),
        ];
    }

    private static function wrapperClass(): string
    {
        return FieldVariants::join(
            'inline-flex w-full',
            // zero radii on every direct child, restore on the two ends
            '[&>*]:rounded-none',
            '[&>*:first-child]:rounded-l-[var(--radius-field)]',
            '[&>*:last-child]:rounded-r-[var(--radius-field)]',
            // collapse the adjacent border so two children show one rule
            '[&>*:not(:last-child)]:border-r-0',
            // direct <input> / <select> / <textarea> children stretch
            '[&>input]:flex-1 [&>input]:min-w-0',
            '[&>select]:flex-1 [&>select]:min-w-0',
            '[&>textarea]:flex-1 [&>textarea]:min-w-0',
        );
    }

    private static function addonClass(string $size): string
    {
        $box = match ($size) {
            'xs' => 'h-[var(--h-field-xs)] px-[var(--px-field-xs)] text-[length:var(--text-field-xs)]',
            'sm' => 'h-[var(--h-field-sm)] px-[var(--px-field-sm)] text-[length:var(--text-field-sm)]',
            'lg' => 'h-[var(--h-field-lg)] px-[var(--px-field-lg)] text-[length:var(--text-field-lg)]',
            default => 'h-[var(--h-field-md)] px-[var(--px-field-md)] text-[length:var(--text-field-md)]',
        };

        return FieldVariants::join(
            'shrink-0 inline-flex items-center',
            'bg-base-200 tune-border border-base-300 text-base-content/70 whitespace-nowrap',
            $box,
        );
    }
}
