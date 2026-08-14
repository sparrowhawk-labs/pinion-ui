<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class ModalComposer
{
    public static function compose(array $props): array
    {
        $size = $props['size'] ?? 'md';

        return [
            'overlay'   => self::overlay(),
            'backdrop'  => self::backdrop(),
            'panel'     => self::panel($size),
            'sizeClass' => self::sizeClass($size),
            'header'    => self::header(),
            'title'     => self::title(),
            'closeBtn'       => self::closeBtn(),
            'closeBtnFloat'  => self::closeBtnFloat(),
            'closeIcon'      => self::closeIcon(),
            'closeIconFloat' => self::closeIconFloat(),
            'body'           => self::body(),
            'actions'        => self::actions(),
        ];
    }

    private static function sizeClass(string $size): string
    {
        return match ($size) {
            'sm'   => 'max-w-sm',
            'lg'   => 'max-w-2xl',
            'xl'   => 'max-w-4xl',
            'full' => 'max-w-full mx-4',
            default => 'max-w-lg',
        };
    }

    private static function overlay(): string
    {
        return 'fixed inset-0 z-50 flex items-center justify-center p-4';
    }

    private static function backdrop(): string
    {
        return 'absolute inset-0 bg-black/50';
    }

    private static function panel(string $size): string
    {
        return FieldVariants::join(
            'relative w-full',
            self::sizeClass($size),
            // Cap the panel to the viewport (matching the overlay's p-4 inset) and
            // lay out header / body / actions as a column so only the body scrolls
            // when content is taller than the screen — actions stay reachable.
            'flex flex-col max-h-[calc(100dvh-2rem)]',
            'bg-base-100 text-base-content text-[length:var(--text-field-md)]',
            'rounded-[var(--radius-box)] border-[length:var(--border)] border-base-300',
            // Functional overlay: route the decorative character through the
            // tune shadow, layered over a quiet base elevation so the dialog
            // stays lifted off the scrim even in flat tunes (minimal/corporate
            // set --shadow-box: none). Border carries the perimeter regardless.
            'shadow-[0_10px_30px_-8px_rgb(0_0_0_/_0.18),var(--shadow-box)] p-lg',
        );
    }

    private static function header(): string
    {
        return 'flex shrink-0 items-center justify-between mb-sm';
    }

    private static function title(): string
    {
        return 'text-[length:var(--text-field-lg)] font-semibold text-base-content';
    }

    private static function closeBtn(): string
    {
        return FieldVariants::join(
            'text-base-content/50 hover:text-base-content transition-colors',
            'rounded-[var(--radius-field)] p-1 hover:bg-base-200',
        );
    }

    /**
     * Floating close button — used when `title` is omitted. Anchors the × to the
     * panel's top-right corner so the body content can start at the top padding
     * instead of leaving an empty header row.
     *
     * Sizing note: in-header `closeBtn` is paired with a `text-field-lg` title and
     * needs the visual weight of `p-1 + w-5 h-5` to balance. In the floating
     * variant there is no neighboring title to anchor to, so we shrink to
     * `p-0.5 + w-4 h-4` (~20px square) — quieter, less "stamp pasted on the
     * corner" feel. See `closeIconFloat`.
     */
    private static function closeBtnFloat(): string
    {
        return FieldVariants::join(
            'absolute top-lg right-lg z-10',
            'text-base-content/50 hover:text-base-content transition-colors',
            'rounded-[var(--radius-field)] p-0.5 hover:bg-base-200',
        );
    }

    private static function closeIcon(): string
    {
        return 'w-5 h-5';
    }

    private static function closeIconFloat(): string
    {
        return 'w-4 h-4';
    }

    /**
     * Body wrapper — the only flex child allowed to shrink, so when the panel
     * hits its viewport cap the body scrolls while header and actions stay
     * visible. When content fits, the wrapper is inert (no visual change).
     */
    private static function body(): string
    {
        return 'min-h-0 overflow-y-auto';
    }

    private static function actions(): string
    {
        return 'flex shrink-0 items-center justify-end gap-xs mt-lg';
    }
}
