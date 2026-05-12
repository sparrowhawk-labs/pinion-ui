<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class InputComposer
{
    public static function compose(array $props): array
    {
        $color        = $props['color'] ?? 'neutral';
        $appearance   = $props['appearance'] ?? 'outline';
        $size         = $props['size'] ?? 'md';
        $error        = $props['error'] ?? null;
        $floating     = $props['floating'] ?? false;
        $hasLabel     = $props['hasLabel'] ?? false;
        $hasIconLeft  = $props['hasIconLeft'] ?? false;
        $hasIconRight = $props['hasIconRight'] ?? false;
        $disabled     = $props['disabled'] ?? false;

        $effectiveColor = $error ? 'error' : $color;
        $variant        = FieldVariants::variant($appearance, $effectiveColor);
        $radius         = FieldVariants::radius($appearance);

        return [
            'wrapper'       => self::wrapper($size, $variant, $radius, $disabled),
            'inputClass'    => self::inputClass($size, $floating, $hasLabel, $hasIconLeft, $hasIconRight),
            'addonBase'     => self::addonBase($size),
            'floatingLabel' => self::floatingLabel($size, $hasIconLeft),
            'labelColor'    => FieldVariants::labelColor($error),
            'hintColor'     => FieldVariants::hintColor($error),
        ];
    }

    private static function wrapper(string $size, string $variant, string $radius, bool $disabled): string
    {
        $height = match ($size) {
            'sm'    => 'h-[var(--h-field-sm)]',
            'lg'    => 'h-[var(--h-field-lg)]',
            default => 'h-[var(--h-field-md)]',
        };
        $disabledClass = $disabled ? 'opacity-60 cursor-not-allowed' : '';
        return FieldVariants::join('flex items-stretch transition-colors', $radius, $variant, $disabledClass, $height);
    }

    private static function text(string $size): string
    {
        return match ($size) {
            'sm'    => 'text-[length:var(--text-field-sm)]',
            'lg'    => 'text-[length:var(--text-field-lg)]',
            default => 'text-[length:var(--text-field-md)]',
        };
    }

    private static function px(string $size): string
    {
        return match ($size) {
            'sm'    => 'px-[var(--px-input-sm)]',
            'lg'    => 'px-[var(--px-input-lg)]',
            default => 'px-[var(--px-input-md)]',
        };
    }

    private static function inputClass(
        string $size,
        bool   $floating,
        bool   $hasLabel,
        bool   $hasIconLeft,
        bool   $hasIconRight
    ): string {
        $bare = 'flex-1 min-w-0 w-full bg-transparent border-0 outline-none placeholder:text-base-content/40';
        $text = self::text($size);

        // When iconLeft exists, override left padding only; otherwise px-* covers both sides.
        $pxLeft  = $hasIconLeft  ? 'pl-10' : self::px($size);
        $pxRight = $hasIconRight ? 'pr-10' : '';

        $floatingPad = ($floating && $hasLabel) ? self::floatingInputPad($size) : '';

        return FieldVariants::join($bare, 'peer', $text, $pxLeft, $pxRight, $floatingPad);
    }

    private static function floatingInputPad(string $size): string
    {
        return match ($size) {
            'sm'    => 'pt-3.5 pb-0.5',
            'lg'    => 'pt-5 pb-1',
            default => 'pt-4 pb-0.5',
        };
    }

    private static function floatingLabel(string $size, bool $hasIconLeft): string
    {
        $text = self::text($size);
        $left = $hasIconLeft ? 'left-10' : match ($size) {
            'sm'    => 'left-[var(--px-input-sm)]',
            'lg'    => 'left-[var(--px-input-lg)]',
            default => 'left-[var(--px-input-md)]',
        };

        // Full literal class strings — JIT cannot compose variant prefixes like
        // `peer-focus:` with interpolated CSS custom property references.
        $state = match ($size) {
            'sm'    => 'peer-focus:top-0.5 peer-focus:translate-y-0 peer-focus:text-[length:var(--text-field-xs)] peer-focus:text-base-content/60 peer-[:not(:placeholder-shown)]:top-0.5 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-[length:var(--text-field-xs)] peer-[:not(:placeholder-shown)]:text-base-content/60',
            'lg'    => 'peer-focus:top-2 peer-focus:translate-y-0 peer-focus:text-[length:var(--text-field-xs)] peer-focus:text-base-content/60 peer-[:not(:placeholder-shown)]:top-2 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-[length:var(--text-field-xs)] peer-[:not(:placeholder-shown)]:text-base-content/60',
            default => 'peer-focus:top-1 peer-focus:translate-y-0 peer-focus:text-[length:var(--text-field-xs)] peer-focus:text-base-content/60 peer-[:not(:placeholder-shown)]:top-1 peer-[:not(:placeholder-shown)]:translate-y-0 peer-[:not(:placeholder-shown)]:text-[length:var(--text-field-xs)] peer-[:not(:placeholder-shown)]:text-base-content/60',
        };

        return "absolute $left top-1/2 -translate-y-1/2 text-base-content/50 pointer-events-none origin-[0_0] transition-all duration-150 $text $state";
    }

    private static function addonBase(string $size): string
    {
        $px   = self::px($size);
        $text = self::text($size);
        return "flex items-center shrink-0 bg-base-200 text-base-content/60 $px $text";
    }
}
