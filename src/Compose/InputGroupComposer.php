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
        // border + radius live at varying depths.  Three child shapes coexist:
        //   (a) bare <input>/<select>/<textarea>/<button>/<span addon> as
        //       direct children (depth 1) — handled by [&>*:...] rules.
        //   (b) <x-input> / native <x-select> →
        //         div.w-full > div.flex.items-stretch[border+radius] > <input|select>
        //       depth 2 inner div. Targeted via :has(input,select,textarea).
        //   (c) custom <x-select> (default mode) →
        //         div.w-full > div.relative.w-full[no border] > <button>[border+radius]
        //       depth 3 button. The visible element is a <button>, not a
        //       bordered inner div. Targeted via dedicated >div>div>button rules.
        return FieldVariants::join(
            'inline-flex w-full',
            // Stretch interactive children. Spans (text addons) and buttons keep natural width.
            '[&>input]:flex-1 [&>input]:min-w-0',
            '[&>select]:flex-1 [&>select]:min-w-0',
            '[&>textarea]:flex-1 [&>textarea]:min-w-0',
            '[&>div]:flex-1 [&>div]:min-w-0',
            // (a/b) Zero radii on direct children + the depth-2 inner wrapper.
            '[&>*]:rounded-none',
            '[&>div>div]:rounded-none',
            // (c) Zero radii on the depth-3 trigger <button> of custom x-select.
            '[&>div>div>button]:rounded-none',
            // (a) Restore radii on the two ends — direct children.
            '[&>*:first-child]:rounded-l-[var(--radius-field)]',
            '[&>*:last-child]:rounded-r-[var(--radius-field)]',
            // (b) Restore radii on the depth-2 inner wrapper at the ends.
            '[&>div:first-child>div:has(input,select,textarea)]:rounded-l-[var(--radius-field)]',
            '[&>div:last-child>div:has(input,select,textarea)]:rounded-r-[var(--radius-field)]',
            // (c) Restore radii on the depth-3 custom-select <button> at the ends.
            '[&>div:first-child>div>button]:rounded-l-[var(--radius-field)]',
            '[&>div:last-child>div>button]:rounded-r-[var(--radius-field)]',
            // Collapse inner border (right edge) — direct children.
            '[&>*:not(:last-child)]:border-r-0',
            // (b) Collapse — depth-2 inner wrapper of x-input / native x-select.
            '[&>div:not(:last-child)>div:has(input,select,textarea)]:border-r-0',
            // (c) Collapse — depth-3 custom-select <button>.
            '[&>div:not(:last-child)>div>button]:border-r-0',
            // Focus visibility — three layered fixes are required.
            //
            // 1. Raise the focused outer wrapper above its siblings so any
            //    box-shadow on its descendant can paint over the adjacent
            //    border at the joined seam.
            '[&>*:focus-within]:relative',
            '[&>*:focus-within]:z-10',
            //
            // 2. Force the ring colour to FULL primary inside this group.
            //    Tailwind v4's default --tw-ring-color is
            //      color-mix(in oklab, var(--color-primary) 30%, transparent)
            //    a 30 %-opacity tint that vanishes when overlaid on the
            //    neighbour's grey 1px border at the seam. Override it so the
            //    seam reads as solid primary when focused.
            '[&_*:focus-within]:[--tw-ring-color:var(--color-primary)]',
            //
            // 3. Cover the seam with a one-sided right shadow on the depth-2
            //    inner wrapper AND the depth-3 trigger button when they sit
            //    in a non-last-child position. The shadow is layout-neutral
            //    (does not push siblings) and paints the missing right edge
            //    in solid primary so the joined row reads as a single bordered
            //    field with the focused field highlighted on all four sides.
            '[&>div:not(:last-child):focus-within>div:has(input,select,textarea)]:[box-shadow:1px_0_0_0_var(--color-primary)]',
            '[&>div:not(:last-child):focus-within>div>button]:[box-shadow:1px_0_0_0_var(--color-primary)]',
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
