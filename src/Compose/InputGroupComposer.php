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
        // Joining heterogeneous children is tricky because the visible
        // border + radius live one (or two) levels deep — not on the direct
        // child div. Three child shapes coexist:
        //   (a) bare <input>/<select>/<textarea>/<button>/<span addon> as
        //       direct children — handled by [&>*:...] rules.
        //   (b) <x-input> / native <x-select> →
        //       div.w-full > div.flex.items-stretch[border+radius] > <input|select>
        //       Inner div is targeted via :has(input,select,textarea).
        //   (c) custom <x-select> (default mode) →
        //       div.w-full > div.relative.w-full[no border] > <button>[border+radius]
        //       The visible element is a <button>, not a bordered inner div.
        //       Dedicated rules below target >button.
        return FieldVariants::join(
            'inline-flex w-full',
            // Stretch interactive children. Spans (text addons) and buttons keep natural width.
            '[&>input]:flex-1 [&>input]:min-w-0',
            '[&>select]:flex-1 [&>select]:min-w-0',
            '[&>textarea]:flex-1 [&>textarea]:min-w-0',
            '[&>div]:flex-1 [&>div]:min-w-0',
            // Zero radii on direct children AND on the inner wrappers of x-input / x-select.
            '[&>*]:rounded-none',
            '[&>div>div]:rounded-none',
            // Zero radii on the trigger <button> of custom x-select (shape (c)).
            '[&>div>button]:rounded-none',
            // Restore radii on the two ends — direct children (bare input/select/button/span).
            '[&>*:first-child]:rounded-l-[var(--radius-field)]',
            '[&>*:last-child]:rounded-r-[var(--radius-field)]',
            // Restore radii on the inner wrapper of x-input / native x-select at the ends.
            '[&>div:first-child>div:has(input,select,textarea)]:rounded-l-[var(--radius-field)]',
            '[&>div:last-child>div:has(input,select,textarea)]:rounded-r-[var(--radius-field)]',
            // Restore radii on the trigger <button> of custom x-select at the ends.
            '[&>div:first-child>button]:rounded-l-[var(--radius-field)]',
            '[&>div:last-child>button]:rounded-r-[var(--radius-field)]',
            // Collapse inner border (right edge) — direct children.
            '[&>*:not(:last-child)]:border-r-0',
            // Collapse inner border — inner wrapper of x-input / native x-select.
            '[&>div:not(:last-child)>div:has(input,select,textarea)]:border-r-0',
            // Collapse inner border — trigger <button> of custom x-select.
            '[&>div:not(:last-child)>button]:border-r-0',
            // Focus visibility: when any child is focus-within, bring it above
            // its siblings so the 1px focus ring (drawn as box-shadow on the
            // inner wrapper of x-input / native select, or on the custom-select
            // trigger button, or on bare children) isn't occluded by the
            // adjacent sibling's border on the joined edge. Without this, a
            // focused LEFT child shows no visible focus indicator on its right
            // edge (border-r is already 0 from the collapse rules above) — the
            // focused element appears 3-sided. relative + z-10 raises only the
            // focused element; idle siblings stay at z=auto so their borders
            // still form the joined divider line.
            '[&>*:focus-within]:relative',
            '[&>*:focus-within]:z-10',
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
