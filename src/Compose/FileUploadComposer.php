<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class FileUploadComposer
{
    public static function compose(array $props): array
    {
        $color         = $props['color'] ?? 'neutral';
        $appearance    = $props['appearance'] ?? 'outline';
        $size          = $props['size'] ?? 'md';
        $error         = $props['error'] ?? null;
        $disabled      = $props['disabled'] ?? false;
        $previewLayout = $props['previewLayout'] ?? 'horizontal';

        $effectiveColor = $error ? 'error' : $color;
        $isDropzone     = $appearance === 'dropzone';
        $isGrid         = $previewLayout === 'grid';

        return [
            'wrapper'       => self::wrapper($disabled),
            'labelText'     => FieldVariants::labelColor($error) . ' text-[length:var(--text-field-sm)] font-medium',
            'field'         => $isDropzone
                ? self::dropzoneField($size, $effectiveColor)
                : self::inlineField($size, $appearance, $effectiveColor),
            'inputClass'    => $isDropzone ? 'sr-only' : self::inlineInputClass($size),
            'dropzoneIcon'  => $isDropzone ? self::dropzoneIcon($size) : '',
            'browseLink'    => self::browseLink($effectiveColor),
            'dropzoneHint'  => 'text-[length:var(--text-field-xs)] text-base-content/60',
            'hint'          => 'text-[length:var(--text-field-xs)] ' . FieldVariants::hintColor($error),
            'previewList'   => $isGrid
                ? 'mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 empty:hidden'
                : 'mt-2 flex flex-col gap-1.5 empty:hidden',
            'previewItem'   => $isGrid ? self::previewItemGrid() : self::previewItem($size),
            'previewThumb'  => $isGrid
                ? 'absolute inset-0 w-full h-full object-cover'
                : 'size-10 shrink-0 rounded object-cover bg-base-200',
            'previewIconBox' => $isGrid
                ? 'absolute inset-0 flex items-center justify-center bg-base-200 text-base-content/40'
                : 'size-10 shrink-0 flex items-center justify-center rounded bg-base-200 text-base-content/40',
            'previewIconSize' => $isGrid ? 'size-10' : 'size-5',
            'previewName'   => $isGrid
                ? 'absolute inset-x-0 bottom-0 px-2 pt-6 pb-2 bg-gradient-to-t from-black/70 via-black/40 to-transparent text-white text-[length:var(--text-field-xs)] truncate'
                : 'truncate',
            'previewRemove' => $isGrid
                ? 'absolute top-1.5 right-1.5 size-6 inline-flex items-center justify-center rounded-full bg-black/50 text-white hover:bg-error transition-colors cursor-pointer'
                : 'shrink-0 text-base-content/50 hover:text-error transition-colors cursor-pointer',
            'progressTrack' => $isGrid
                ? 'absolute bottom-0 inset-x-0 h-1 bg-black/30 overflow-hidden'
                : 'h-1 w-full bg-base-300/60 rounded-full overflow-hidden',
            'progressFill'  => self::progressFill($effectiveColor),
        ];
    }

    private static function wrapper(bool $disabled): string
    {
        $base = 'flex flex-col gap-1.5';
        return $disabled ? "$base opacity-60 [&_*]:cursor-not-allowed [&_*]:pointer-events-none" : $base;
    }

    /**
     * Inline mode — borrows the standard field shell (outline/soft) so colour
     * × appearance behaves exactly like input/textarea/select. The native
     * `<input type="file">` lives inside this wrapper and uses Tailwind's
     * `file:*` modifier to style the browser-rendered chooser button.
     */
    private static function inlineField(string $size, string $appearance, string $color): string
    {
        $variant = FieldVariants::variant($appearance, $color);
        $radius  = FieldVariants::radius($appearance);
        $height  = match ($size) {
            'sm'    => 'h-[var(--h-field-sm)]',
            'lg'    => 'h-[var(--h-field-lg)]',
            default => 'h-[var(--h-field-md)]',
        };
        return FieldVariants::join('flex items-stretch overflow-clip transition-colors', $radius, $variant, $height);
    }

    private static function inlineInputClass(string $size): string
    {
        $text = match ($size) {
            'sm'    => 'text-[length:var(--text-field-sm)]',
            'lg'    => 'text-[length:var(--text-field-lg)]',
            default => 'text-[length:var(--text-field-md)]',
        };
        // Picker button (`file:` pseudo) stays neutral regardless of $color so
        // it doesn't compete with the rail signal. Same choice Penguin/Preline make.
        $filePad = match ($size) {
            'sm'    => 'file:px-3 file:py-1',
            'lg'    => 'file:px-5 file:py-3',
            default => 'file:px-4 file:py-2',
        };
        return FieldVariants::join(
            'flex-1 min-w-0 w-full bg-transparent border-0 outline-none cursor-pointer',
            'pr-3 placeholder:text-base-content/40 text-base-content',
            $text,
            'file:mr-3 file:border-0 file:bg-base-200 file:text-base-content file:font-medium file:cursor-pointer',
            'hover:file:bg-base-300 transition-colors',
            $filePad
        );
    }

    /**
     * Dropzone mode — large dashed-border drop zone with a hidden input. The
     * native `<input type="file">` already accepts dropped files, so we don't
     * need any drag-handler JS; the visual is the entire affordance.
     */
    private static function dropzoneField(string $size, string $color): string
    {
        $padding = match ($size) {
            'sm'    => 'px-4 py-5',
            'lg'    => 'px-10 py-12',
            default => 'px-6 py-8',
        };
        $hover = self::dropzoneHover($color);

        return FieldVariants::join(
            'flex flex-col items-center justify-center gap-2 text-center',
            'rounded-[var(--radius-box)] tune-border border-dashed border-base-300 bg-base-100',
            'transition-colors cursor-pointer',
            $hover,
            $padding
        );
    }

    /**
     * Static enumeration of hover/focus-within colour pairs — `hover:border-X`
     * and `focus-within:ring-X` aren't in the preset safelist, and writing them
     * out keeps the safelist's surface unchanged.
     */
    private static function dropzoneHover(string $color): string
    {
        return match ($color) {
            'primary'   => 'hover:border-primary focus-within:border-primary focus-within:ring-1 focus-within:ring-primary',
            'secondary' => 'hover:border-secondary focus-within:border-secondary focus-within:ring-1 focus-within:ring-secondary',
            'accent'    => 'hover:border-accent focus-within:border-accent focus-within:ring-1 focus-within:ring-accent',
            'info'      => 'hover:border-info focus-within:border-info focus-within:ring-1 focus-within:ring-info',
            'success'   => 'hover:border-success focus-within:border-success focus-within:ring-1 focus-within:ring-success',
            'warning'   => 'hover:border-warning focus-within:border-warning focus-within:ring-1 focus-within:ring-warning',
            'error'     => 'hover:border-error focus-within:border-error focus-within:ring-1 focus-within:ring-error',
            default     => 'hover:border-primary focus-within:border-primary focus-within:ring-1 focus-within:ring-primary',
        };
    }

    private static function dropzoneIcon(string $size): string
    {
        return match ($size) {
            'sm'    => 'size-8 text-base-content/40',
            'lg'    => 'size-14 text-base-content/40',
            default => 'size-10 text-base-content/40',
        };
    }

    private static function browseLink(string $color): string
    {
        // Only the link text picks up $color so the brand accent shows through
        // on what is otherwise a neutral surface.
        return match ($color) {
            'primary'   => 'font-semibold text-primary hover:underline',
            'secondary' => 'font-semibold text-secondary hover:underline',
            'accent'    => 'font-semibold text-accent hover:underline',
            'info'      => 'font-semibold text-info hover:underline',
            'success'   => 'font-semibold text-success hover:underline',
            'warning'   => 'font-semibold text-warning hover:underline',
            'error'     => 'font-semibold text-error hover:underline',
            default     => 'font-semibold text-primary hover:underline',
        };
    }

    /**
     * Progress bar fill colour switches to `success` once a row completes
     * (driven by the Alpine `complete` flag in the Blade view); MUI / Ant /
     * Dropzone all flip to green-on-done as feedback.
     */
    private static function progressFill(string $color): string
    {
        return match ($color) {
            'primary'   => 'h-full bg-primary transition-[width] duration-300 ease-out',
            'secondary' => 'h-full bg-secondary transition-[width] duration-300 ease-out',
            'accent'    => 'h-full bg-accent transition-[width] duration-300 ease-out',
            'info'      => 'h-full bg-info transition-[width] duration-300 ease-out',
            'success'   => 'h-full bg-success transition-[width] duration-300 ease-out',
            'warning'   => 'h-full bg-warning transition-[width] duration-300 ease-out',
            'error'     => 'h-full bg-error transition-[width] duration-300 ease-out',
            default     => 'h-full bg-primary transition-[width] duration-300 ease-out',
        };
    }

    /**
     * Horizontal preview row — thumbnail / icon on the left, file metadata
     * inline, progress bar below. Surface stays neutral so the colour signal
     * lives in the progress fill (Mantine / MUI / Ant share this partition).
     */
    private static function previewItem(string $size): string
    {
        $text = match ($size) {
            'sm'    => 'text-[length:var(--text-field-xs)]',
            'lg'    => 'text-[length:var(--text-field-md)]',
            default => 'text-[length:var(--text-field-sm)]',
        };
        return FieldVariants::join(
            'flex flex-col gap-1.5 px-3 py-2 rounded-[var(--radius-field)] tune-border border-base-300 bg-base-100',
            'text-base-content', $text
        );
    }

    /**
     * Square card for grid layout — thumbnail / icon fills the card, filename
     * floats in a gradient overlay, progress bar pinned to the bottom edge.
     * Same affordance as Ant Design's `picture-card` and Mantine's grid
     * preview pattern.
     */
    private static function previewItemGrid(): string
    {
        return FieldVariants::join(
            'group relative aspect-square rounded-[var(--radius-field)] overflow-hidden',
            'tune-border border-base-300 bg-base-100'
        );
    }
}
