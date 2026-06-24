<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class SidebarComposer
{
    public static function compose(array $props): array
    {
        $side     = ($props['side']     ?? 'left')   === 'right' ? 'right' : 'left';
        $size     = $props['size']      ?? 'md';
        $backdrop = (bool)($props['backdrop'] ?? true);

        return [
            'overlay'       => self::overlay($backdrop),
            'backdrop'      => self::backdrop(),
            'panel'         => self::panel($side, $size, $backdrop),
            'sizeWidth'     => self::sizeWidth($size),
            'sideAnchor'    => self::sideAnchor($side),
            'enterFrom'     => self::enterFrom($side),
            'enterTo'       => self::enterTo(),
            'closeBtnFloat' => self::closeBtnFloat(),
            'closeIcon'     => self::closeIcon(),
        ];
    }

    private static function sizeWidth(string $size): string
    {
        return match ($size) {
            'sm' => 'w-64',
            'lg' => 'w-96',
            default => 'w-80',
        };
    }

    private static function sideAnchor(string $side): string
    {
        return $side === 'right' ? 'right-0' : 'left-0';
    }

    private static function enterFrom(string $side): string
    {
        return $side === 'right' ? 'translate-x-[100%]' : 'translate-x-[-100%]';
    }

    private static function enterTo(): string
    {
        return 'translate-x-0';
    }

    /**
     * Overlay layer.
     *
     * - backdrop=true  → captures pointer events so the dim layer behaves like
     *   a real modal scrim (click on backdrop can close).
     * - backdrop=false → set the whole overlay to `pointer-events-none` so the
     *   underlying page remains fully interactive while the drawer is open.
     *   The panel below re-enables pointer events for itself only.
     */
    private static function overlay(bool $backdrop): string
    {
        return $backdrop
            ? 'fixed inset-0 z-50'
            : 'fixed inset-0 z-50 pointer-events-none';
    }

    private static function backdrop(): string
    {
        return 'absolute inset-0 bg-black/50';
    }

    private static function panel(string $side, string $size, bool $backdrop): string
    {
        $borderSide = $side === 'right' ? 'border-l' : 'border-r';
        $parts = [
            'absolute top-0',
            self::sideAnchor($side),
            'h-full',
            self::sizeWidth($size),
            'bg-base-100 text-base-content',
            $borderSide,
            // Functional overlay with only ONE perimeter border (the edge it
            // slides from is flush with the viewport). Layer a quiet base
            // elevation under the tune shadow so the drawer reads as lifted in
            // flat tunes (minimal/corporate → --shadow-box: none) too.
            'border-base-300 shadow-[0_0_40px_-8px_rgb(0_0_0_/_0.20),var(--shadow-box)] p-element overflow-y-auto',
        ];
        if (!$backdrop) {
            // re-enable pointer events on the panel itself when overlay is
            // pointer-events-none — without this the drawer becomes non-clickable.
            $parts[] = 'pointer-events-auto';
        }
        return FieldVariants::join(...$parts);
    }

    /**
     * Floating close button anchored to the panel's top-right corner regardless
     * of drawer side. Drawer content typically places a heading at the top-left
     * (ltr default), so the close button always living at top-right avoids
     * overlap with the title.
     */
    private static function closeBtnFloat(): string
    {
        return FieldVariants::join(
            'absolute top-[var(--space-element)] right-[var(--space-element)]',
            'z-10',
            'text-base-content/50 hover:text-base-content transition-colors',
            'rounded-[var(--radius-field)] p-1 hover:bg-base-200',
        );
    }

    private static function closeIcon(): string
    {
        return 'w-5 h-5';
    }
}
