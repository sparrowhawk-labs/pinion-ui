<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class DropdownComposer
{
    public static function compose(array $props): array
    {
        $position = $props['position'] ?? 'bottom-end';
        $size     = $props['size']     ?? 'md';
        $width    = $props['width']    ?? 'w-52';

        return [
            // w-fit (width: fit-content): a grid item's display is
            // blockified (inline-block -> block), so the default
            // `justify-items: stretch` silently stretches this wrapper to
            // the full grid-cell width — `right-0` on the menu then anchors
            // to that stretched edge instead of the trigger's real right
            // edge, drifting the panel sideways in any grid layout (and a
            // column-direction flex parent would do the same via
            // align-items: stretch on the cross axis). w-fit gives the
            // wrapper a definite (non-auto) size, which is what actually
            // suppresses stretch per the CSS box-alignment spec — unlike
            // `justify-self-start`/`self-start`, it does NOT hard-code
            // 'start' and so still honours an explicit `place-items-center`
            // (or any other alignment) a consumer's grid/flex sets.
            'root'    => 'relative inline-block w-fit',
            'trigger' => self::trigger($size),
            'menu'    => self::menu($position, $width),
            'icon'    => 'w-4 h-4 transition-transform',
        ];
    }

    private static function trigger(string $size): string
    {
        return FieldVariants::join(
            'inline-flex items-center justify-center gap-1.5 font-medium transition-colors',
            'rounded-[var(--radius-field)] border-[length:var(--border)] border-base-300',
            'bg-base-100 text-base-content hover:bg-base-200 cursor-pointer',
            self::triggerSize($size),
        );
    }

    private static function triggerSize(string $size): string
    {
        return match ($size) {
            'sm' => 'h-[var(--h-field-sm)] px-[var(--px-field-sm)] text-[length:var(--text-field-sm)]',
            'lg' => 'h-[var(--h-field-lg)] px-[var(--px-field-lg)] text-[length:var(--text-field-lg)]',
            default => 'h-[var(--h-field-md)] px-[var(--px-field-md)] text-[length:var(--text-field-md)]',
        };
    }

    private static function menu(string $position, string $width): string
    {
        return FieldVariants::join(
            'absolute z-40',
            self::positionClasses($position),
            $width,
            'bg-base-100 border-[length:var(--border)] border-base-300',
            'rounded-[var(--radius-box)] shadow-[var(--shadow-box)] py-1 overflow-hidden',
        );
    }

    private static function positionClasses(string $position): string
    {
        return match ($position) {
            'bottom-start' => 'left-0 top-full mt-1',
            'top-end'      => 'right-0 bottom-full mb-1',
            'top-start'    => 'left-0 bottom-full mb-1',
            default        => 'right-0 top-full mt-1',
        };
    }
}
