<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class TextareaComposer
{
    public static function compose(array $props): array
    {
        $color      = $props['color'] ?? 'neutral';
        $appearance = $props['appearance'] ?? 'outline';
        $size       = $props['size'] ?? 'md';
        $error      = $props['error'] ?? null;
        $autoresize = $props['autoresize'] ?? false;
        $disabled   = $props['disabled'] ?? false;

        $effectiveColor = $error ? 'error' : $color;
        $variant        = FieldVariants::variant($appearance, $effectiveColor);
        $radius         = FieldVariants::radius($appearance);

        return [
            'wrapper'       => self::wrapper($variant, $radius, $disabled),
            'textareaClass' => self::textareaClass($size, $autoresize),
            'labelColor'    => FieldVariants::labelColor($error),
            'hintColor'     => FieldVariants::hintColor($error),
        ];
    }

    private static function wrapper(string $variant, string $radius, bool $disabled): string
    {
        $disabledClass = $disabled ? 'opacity-60 cursor-not-allowed' : '';
        return FieldVariants::join('block transition-colors', $radius, $variant, $disabledClass);
    }

    private static function textareaClass(string $size, bool $autoresize): string
    {
        $sizeUtil = match ($size) {
            'sm'    => 'tune-textarea-sm',
            'lg'    => 'tune-textarea-lg',
            default => 'tune-textarea-md',
        };
        $resize = $autoresize ? 'resize-none overflow-hidden' : 'resize-y';
        return "block w-full bg-transparent border-0 outline-none placeholder:text-base-content/40 $sizeUtil $resize";
    }
}
