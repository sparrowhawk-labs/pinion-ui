<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * DataGridComposer — class strings for the <x-data-grid> spreadsheet component.
 *
 * INVARIANT (load-bearing, see CLAUDE.md §Architecture invariants 1): this is a
 * PURE function. `compose($props)` returns a FLAT dict of CSS class strings only
 * — no markup, no JS, no side effects. The grid's *behavior* (Tabulator: inline
 * edit, range selection, clipboard, fill, sort, resize) lives entirely in the
 * opt-in JS module (src/resources/js/data-grid.js). The structural rules that
 * can't be utilities — i.e. mapping daisyUI/tune tokens onto Tabulator's own
 * `--tabulator-*` CSS variables — live in the bundled stylesheet (data-grid.css).
 *
 * Aesthetic: "a quiet spreadsheet". The grid is a flat, themed surface (no card),
 * its chrome composed from plain Tailwind + daisyUI *color* classes + tune tokens
 * (--radius-box, --space-*, --text-field-*) so it inherits the theme × tune
 * cascade like every other component. Tabulator's internals are coloured via
 * CSS variables in data-grid.css — NOT via daisyUI component classes.
 *
 * Returned keys:
 *   shell       — outer flex-col wrapper (toolbar + grid)
 *   toolbar     — optional top bar (add-row / count / actions slot)
 *   toolbarBtn  — a built-in toolbar action button (add row) in its resting state
 *   count       — muted row-count cluster
 *   grid        — the `.pn-data-grid` host Tabulator mounts into (themed shell)
 */
class DataGridComposer
{
    public static function compose(array $props): array
    {
        $size = $props['size'] ?? 'md';

        return [
            'shell'      => 'flex flex-col gap-2',
            'toolbar'    => 'flex items-center gap-2 flex-wrap',
            'toolbarBtn' => self::toolbarBtn($size),
            'count'      => 'text-[length:var(--text-field-xs)] text-base-content/50 tabular-nums',
            'grid'       => self::grid($size),
        ];
    }

    private static function grid(string $size): string
    {
        // `.pn-data-grid` is OUR hook class — data-grid.css targets
        // `.pn-data-grid .tabulator*` and maps daisyUI/tune tokens onto Tabulator's
        // CSS variables. Here we set the base text size off the tune scale and clip
        // Tabulator's square corners to the tune radius. `tune-border border-base-300`
        // mirrors FieldVariants so the frame tracks data-tune × data-theme.
        $text = match ($size) {
            'sm'    => 'text-[length:var(--text-field-sm)]',
            'lg'    => 'text-[length:var(--text-field-lg)]',
            default => 'text-[length:var(--text-field-md)]',
        };

        return FieldVariants::join(
            'pn-data-grid w-full overflow-hidden',
            'rounded-[var(--radius-box)] tune-border border-base-300',
            $text,
            'text-base-content',
        );
    }

    private static function toolbarBtn(string $size): string
    {
        // Quiet surface button (NOT a daisyUI `.btn`, excluded from the build).
        $dims = match ($size) {
            'sm'    => 'h-7 px-2 text-[length:var(--text-field-xs)]',
            'lg'    => 'h-9 px-3.5 text-[length:var(--text-field-md)]',
            default => 'h-8 px-3 text-[length:var(--text-field-sm)]',
        };

        return FieldVariants::join(
            $dims,
            'inline-flex items-center justify-center gap-1 shrink-0',
            'rounded-[calc(var(--radius-box)*0.6)]',
            'bg-base-200 text-base-content/80 hover:text-base-content hover:bg-base-content/[0.1]',
            'transition-colors duration-100 cursor-pointer select-none',
            'disabled:opacity-40 disabled:cursor-not-allowed',
        );
    }
}
