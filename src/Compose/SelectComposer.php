<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class SelectComposer
{
    public static function compose(array $props): array
    {
        $color      = $props['color'] ?? 'neutral';
        $appearance = $props['appearance'] ?? 'outline';
        $size       = $props['size'] ?? 'md';
        $error      = $props['error'] ?? null;
        $floating   = $props['floating'] ?? false;
        $hasLabel   = $props['hasLabel'] ?? false;
        $isList     = $props['isList'] ?? false;
        $disabled   = $props['disabled'] ?? false;
        $custom     = $props['custom'] ?? false;
        $multiple   = $props['multiple'] ?? false;

        $effectiveColor = $error ? 'error' : $color;
        $variant        = FieldVariants::variant($appearance, $effectiveColor);
        $radius         = FieldVariants::radius($appearance);

        $isFloating   = $floating && $hasLabel && !$isList;
        // Floating sm bumps wrapper height one step to fit label + value.
        $isFloatingSm = $isFloating && $size === 'sm';

        return [
            'wrapper'       => self::wrapper($size, $variant, $radius, $disabled, $isList, $isFloating, $isFloatingSm),
            'selectClass'   => self::selectClass($size, $isList, $isFloating),
            'floatingLabel' => self::floatingLabel($size),
            'labelColor'    => FieldVariants::labelColor($error),
            'hintColor'     => FieldVariants::hintColor($error),

            // Custom-mode slots — used when `custom` prop is true. Native
            // <select> is sr-only (still drives form submission), and the
            // trigger / dropdown below render an Alpine-driven UI.
            'trigger'       => self::trigger($size, $variant, $radius, $disabled, $multiple),
            'triggerInner'  => 'flex-1 min-w-0 flex items-center flex-wrap gap-1',
            'triggerText'   => self::triggerText($size),
            'placeholder'   => 'text-base-content/40',
            'chip'          => self::chip($effectiveColor),
            'chipRemove'    => 'shrink-0 inline-flex text-current/60 hover:text-current cursor-pointer',
            'chevron'       => 'pointer-events-none shrink-0 size-4 text-base-content/40 transition-transform',
            'dropdown'      => 'absolute z-40 mt-1 w-full max-h-72 overflow-auto bg-base-100 tune-border border-base-300 rounded-[var(--radius-field)] shadow-lg p-1',
            'option'        => self::option($size),
            'optionSelected' => self::optionSelected($effectiveColor),
            'optionDisabled' => 'opacity-50 cursor-not-allowed pointer-events-none',
            'optionCheck'   => 'shrink-0 size-4 ' . self::checkColor($effectiveColor),
        ];
    }

    private static function wrapper(
        string $size,
        string $variant,
        string $radius,
        bool   $disabled,
        bool   $isList,
        bool   $isFloating,
        bool   $isFloatingSm
    ): string {
        $height = $isList
            ? ''
            : ($isFloatingSm
                ? 'h-[var(--h-field-md)]'
                : match ($size) {
                    'sm'    => 'h-[var(--h-field-sm)]',
                    'lg'    => 'h-[var(--h-field-lg)]',
                    default => 'h-[var(--h-field-md)]',
                });

        $layout = $isList
            ? 'block'
            : ($isFloating ? 'flex items-stretch' : 'flex items-center');

        $disabledClass = $disabled ? 'opacity-60 cursor-not-allowed' : '';
        return FieldVariants::join('relative', $layout, 'transition-colors', $radius, $variant, $disabledClass, $height);
    }

    private static function selectClass(string $size, bool $isList, bool $isFloating): string
    {
        $fill = $isList
            ? 'block w-full'
            : ($isFloating ? 'flex-1 min-w-0 w-full h-full' : 'flex-1 min-w-0 w-full');

        $text = match ($size) {
            'sm'    => 'text-[length:var(--text-field-sm)]',
            'lg'    => 'text-[length:var(--text-field-lg)]',
            default => 'text-[length:var(--text-field-md)]',
        };

        $pxLeft = match ($size) {
            'sm'    => 'pl-[var(--px-input-sm)]',
            'lg'    => 'pl-[var(--px-input-lg)]',
            default => 'pl-[var(--px-input-md)]',
        };

        $chevronPad = $isList ? '' : 'pr-9';

        $listPy = $isList ? match ($size) {
            'sm'    => 'py-[var(--py-input-sm)]',
            'lg'    => 'py-[var(--py-input-lg)]',
            default => 'py-[var(--py-input-md)]',
        } : '';

        $floatingPad = $isFloating ? match ($size) {
            'sm'    => 'pt-3.5 pb-0.5',
            'lg'    => 'pt-5 pb-1',
            default => 'pt-4 pb-0.5',
        } : '';

        $leading = $isFloating ? 'leading-none' : 'leading-tight';

        return FieldVariants::join(
            $fill,
            'bg-transparent border-0 outline-none appearance-none',
            $leading,
            $text,
            $pxLeft,
            $chevronPad,
            $listPy,
            $floatingPad
        );
    }

    private static function floatingLabel(string $size): string
    {
        $left = match ($size) {
            'sm'    => 'left-[var(--px-input-sm)]',
            'lg'    => 'left-[var(--px-input-lg)]',
            default => 'left-[var(--px-input-md)]',
        };
        return "absolute $left top-1 text-[length:var(--text-field-xs)] text-base-content/60 pointer-events-none";
    }

    /**
     * Custom-mode trigger button. Looks like the field shell so the visual
     * matches native — colour × appearance × size shared with input/textarea/
     * select. Multi mode bumps to `min-h-*` so wrapping chips don't overflow.
     */
    private static function trigger(
        string $size,
        string $variant,
        string $radius,
        bool   $disabled,
        bool   $multiple
    ): string {
        $height = $multiple
            ? match ($size) {
                'sm'    => 'min-h-[var(--h-field-sm)]',
                'lg'    => 'min-h-[var(--h-field-lg)]',
                default => 'min-h-[var(--h-field-md)]',
            }
            : match ($size) {
                'sm'    => 'h-[var(--h-field-sm)]',
                'lg'    => 'h-[var(--h-field-lg)]',
                default => 'h-[var(--h-field-md)]',
            };
        $px = match ($size) {
            'sm'    => 'px-[var(--px-input-sm)] py-1',
            'lg'    => 'px-[var(--px-input-lg)] py-1.5',
            default => 'px-[var(--px-input-md)] py-1',
        };
        $disabledClass = $disabled ? 'opacity-60 cursor-not-allowed pointer-events-none' : 'cursor-pointer';
        return FieldVariants::join(
            'relative w-full flex items-center gap-2 text-left transition-colors',
            $radius, $variant, $height, $px, $disabledClass
        );
    }

    private static function triggerText(string $size): string
    {
        return match ($size) {
            'sm'    => 'truncate text-[length:var(--text-field-sm)] text-base-content',
            'lg'    => 'truncate text-[length:var(--text-field-lg)] text-base-content',
            default => 'truncate text-[length:var(--text-field-md)] text-base-content',
        };
    }

    private static function chip(string $color): string
    {
        $tint = match ($color) {
            'primary'   => 'bg-primary/10 text-primary border-primary/30',
            'secondary' => 'bg-secondary/10 text-secondary border-secondary/30',
            'accent'    => 'bg-accent/10 text-accent border-accent/30',
            'info'      => 'bg-info/10 text-info border-info/30',
            'success'   => 'bg-success/10 text-success border-success/30',
            'warning'   => 'bg-warning/10 text-warning border-warning/30',
            'error'     => 'bg-error/10 text-error border-error/30',
            default     => 'bg-base-200 text-base-content border-base-300',
        };
        return FieldVariants::join(
            'inline-flex items-center gap-1 px-2 py-0.5 rounded-full border text-[length:var(--text-field-xs)]',
            $tint
        );
    }

    private static function option(string $size): string
    {
        $text = match ($size) {
            'sm'    => 'text-[length:var(--text-field-sm)]',
            'lg'    => 'text-[length:var(--text-field-lg)]',
            default => 'text-[length:var(--text-field-md)]',
        };
        return FieldVariants::join(
            'flex items-center justify-between gap-2 px-3 py-2 rounded-[var(--radius-field)] cursor-pointer',
            'text-base-content hover:bg-base-200 transition-colors',
            $text
        );
    }

    private static function optionSelected(string $color): string
    {
        return match ($color) {
            'primary'   => 'bg-primary/10 text-primary',
            'secondary' => 'bg-secondary/10 text-secondary',
            'accent'    => 'bg-accent/10 text-accent',
            'info'      => 'bg-info/10 text-info',
            'success'   => 'bg-success/10 text-success',
            'warning'   => 'bg-warning/10 text-warning',
            'error'     => 'bg-error/10 text-error',
            default     => 'bg-base-200 font-medium',
        };
    }

    private static function checkColor(string $color): string
    {
        return match ($color) {
            'primary'   => 'text-primary',
            'secondary' => 'text-secondary',
            'accent'    => 'text-accent',
            'info'      => 'text-info',
            'success'   => 'text-success',
            'warning'   => 'text-warning',
            'error'     => 'text-error',
            default     => 'text-base-content',
        };
    }
}
