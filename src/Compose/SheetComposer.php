<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * SheetComposer — class strings for the <x-sheet> Locality-of-Behavior spreadsheet.
 *
 * INVARIANT (load-bearing, same rule as DataGridComposer): this is a PURE function.
 * `compose($props)` returns a FLAT dict of CSS class strings only — no markup, no JS,
 * no side effects.
 *
 * <x-sheet> COEXISTS with <x-data-grid>. The difference is the SOURCE of behavior:
 * <x-data-grid> delegates all editing/selection/clipboard to Tabulator (an opaque
 * engine in a JS closure), so its Composer only styles a thin chrome and the engine's
 * DOM is recoloured by fighting Tabulator's unlayered hex CSS (data-grid.css). <x-sheet>
 * OWNS its own <table> DOM (hand-written Alpine+Tailwind), so:
 *   - this Composer enumerates EVERY cell/header/gutter/handle slot the hand-rolled grid
 *     renders (a reviewer can name them all up front — subset-match fixtures do NOT flag
 *     a MISSING key, so the full set must be listed here AND in tests/fixtures), and
 *   - the descendant rules it can't express as utilities (grid lines, sticky header,
 *     the composed checkbox glyph) live in a CLEAN `@layer components` stylesheet
 *     (sheet.css) — NOT the unlayered out-specify hack, because we own our markup.
 *
 * Aesthetic mirrors <x-data-grid> ("a quiet spreadsheet"): a flat themed surface, chrome
 * from plain Tailwind + daisyUI *color* classes + tune tokens, so it tracks data-theme ×
 * data-tune like every other component. CELLS are text-entry surfaces, so cell padding
 * uses the --px-input-* / --py-input-* scale (tighter than --px-field-*) and the font the
 * --text-field-* scale — matching DataGridComposer's intent.
 *
 * Returned keys (S0 uses the static-render subset; the state/handle slots are returned
 * now so the contract + fixture are complete from day one, and are WIRED in later stages):
 *   shell              — outer flex-col wrapper (toolbar + grid)               [S0]
 *   toolbar            — optional top bar (toolbox / count / help)             [S0]
 *   toolbox            — grouped cluster holding icon-only operation buttons   [S0]
 *   iconBtn            — an icon-only square operation button (resting)        [S0 visual / S1 wired]
 *   toolbarBtn         — legacy text action button (kept for host `actions`)   [S0 visual / S1 wired]
 *   count              — muted row-count cluster                               [S0]
 *   grid               — the `.pn-sheet` host: themed frame + scroll container [S0]
 *   table              — the <table> element inside the host                   [S0]
 *   headerCell         — a column header cell (resting)                        [S0]
 *   headerCellSortable — a sortable column header cell                         [S0 visual / S3 wired]
 *   cell               — a data cell (resting)                                 [S0]
 *   cellSelected       — the single focused/selected cell outline              [S1]
 *   cellEditing        — a cell with its inline editor open                    [S1]
 *   cellInRange        — a cell inside the selected range                      [S2]
 *   rowNumGutter       — frozen row-number gutter cell                         [S0]
 *   gutterCorner       — top-left corner above the row-number gutter           [S0]
 *   resizeHandle       — column-resize drag handle                             [S3]
 *   fillHandle         — range fill handle (bottom-right of the selection)     [S2]
 *   colDragHandle      — column header drag affordance (reorder)               [S3]
 *   numStepper         — −/+ stepper button inside a number cell               [S2]
 *   checkCell          — composed checkbox box inside a checkbox cell          [S0 resting / S2 toggle]
 */
class SheetComposer
{
    public static function compose(array $props): array
    {
        $size = $props['size'] ?? 'md';

        return [
            'shell'              => 'flex flex-col gap-2',
            'toolbar'            => 'flex items-center gap-2 flex-wrap',
            'toolbox'            => 'inline-flex items-center gap-0.5 p-0.5 rounded-[calc(var(--radius-box)*0.6)] bg-base-200/60',
            'iconBtn'            => self::iconBtn($size),
            'toolbarBtn'         => self::toolbarBtn($size),
            'count'              => 'text-[length:var(--text-field-xs)] text-base-content/50 tabular-nums',
            'grid'               => self::grid($size),
            'table'              => 'pn-sheet-table w-full border-collapse',
            'headerCell'         => self::headerCell($size, false),
            'headerCellSortable' => self::headerCell($size, true),
            'cell'               => self::cell($size),
            'cellSelected'       => 'outline outline-2 -outline-offset-2 outline-primary',
            'cellEditing'        => 'outline outline-2 -outline-offset-2 outline-primary bg-base-100',
            'cellInRange'        => 'bg-primary/10',
            'rowNumGutter'       => self::rowNumGutter($size),
            'gutterCorner'       => 'pn-sheet-gutter bg-base-100 select-none',
            'resizeHandle'       => 'absolute top-0 right-0 h-full w-1 cursor-col-resize select-none hover:bg-primary/40',
            'fillHandle'         => 'absolute -bottom-1 -right-1 w-2 h-2 bg-primary rounded-[1px] cursor-crosshair',
            'colDragHandle'      => 'cursor-grab select-none',
            'numStepper'         => self::numStepper(),
            'checkCell'          => self::checkCell(),
        ];
    }

    private static function grid(string $size): string
    {
        // `.pn-sheet` is OUR hook class — sheet.css targets `.pn-sheet table`,
        // `.pn-sheet th`, `.pn-sheet td` for grid lines / sticky header / the composed
        // checkbox glyph (all under @layer components). Base text size off the tune
        // scale; the tune radius clips the frame, `tune-border border-base-300` tracks
        // data-tune × data-theme (mirrors DataGridComposer + FieldVariants).
        $text = match ($size) {
            'sm'    => 'text-[length:var(--text-field-sm)]',
            'lg'    => 'text-[length:var(--text-field-lg)]',
            default => 'text-[length:var(--text-field-md)]',
        };

        return FieldVariants::join(
            'pn-sheet w-full overflow-auto',
            'rounded-[var(--radius-box)] tune-border border-base-300',
            $text,
            'text-base-content',
        );
    }

    private static function pad(string $size): string
    {
        // Cells are text-entry surfaces → --px-input-* / --py-input-* (tighter than the
        // --px-field-* click/container scale). Tracks data-tune.
        return match ($size) {
            'sm'    => 'px-[var(--px-input-sm)] py-[var(--py-input-sm)]',
            'lg'    => 'px-[var(--px-input-lg)] py-[var(--py-input-lg)]',
            default => 'px-[var(--px-input-md)] py-[var(--py-input-md)]',
        };
    }

    private static function cell(string $size): string
    {
        // Grid LINES (borders) live in sheet.css; the Composer owns padding + colour +
        // alignment + interaction affordances only.
        return FieldVariants::join(
            'relative',
            self::pad($size),
            'text-base-content align-middle select-none cursor-default',
        );
    }

    private static function headerCell(string $size, bool $sortable): string
    {
        return FieldVariants::join(
            self::pad($size),
            'text-left font-medium text-base-content bg-base-100 select-none align-middle',
            $sortable ? 'cursor-pointer hover:bg-base-200/60 transition-colors duration-100' : '',
        );
    }

    private static function rowNumGutter(string $size): string
    {
        // A muted, centred gutter. `pn-sheet-gutter` is the hook sheet.css uses to freeze
        // the gutter COLUMN (header corner + body cells) to the left edge on horizontal
        // scroll — keyed off the class, NOT `td:first-child`, so a no-rowNumbers sheet's
        // first DATA cell and the empty-state cell are never wrongly frozen. A tight
        // horizontal padding (the gutter holds only a 1–3 digit number).
        return FieldVariants::join(
            'pn-sheet-gutter px-2 py-[var(--py-input-' . $size . ')]',
            'text-center text-base-content/45 bg-base-100 tabular-nums select-none cursor-default align-middle',
        );
    }

    private static function iconBtn(string $size): string
    {
        // Icon-only square operation button (add-row / add-col / help). Sized off the
        // tune scale; the [&_svg] rules size the inline SVG (mirrors the editor toolbox).
        $dims = match ($size) {
            'sm'    => 'h-6 w-6',
            'lg'    => 'h-8 w-8',
            default => 'h-7 w-7',
        };

        return FieldVariants::join(
            $dims,
            'inline-grid place-content-center shrink-0',
            'rounded-[calc(var(--radius-box)*0.45)]',
            'text-base-content/70 hover:text-base-content hover:bg-base-content/[0.08]',
            'transition-colors duration-100 cursor-pointer select-none',
            '[&_svg]:w-[1.05rem] [&_svg]:h-[1.05rem] [&_svg]:stroke-[1.75]',
            'disabled:opacity-40 disabled:cursor-not-allowed',
        );
    }

    private static function toolbarBtn(string $size): string
    {
        // Quiet surface button (NOT a daisyUI `.btn`, excluded from the build) — same
        // recipe as DataGridComposer so the two grids share toolbar chrome.
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

    private static function numStepper(): string
    {
        // A −/＋ stepper button inside a number cell. Larger, square, themed. The
        // hover-reveal (opacity) is on the WRAPPER span in the Blade (so space is
        // reserved → no layout shift); the button itself is always opaque within it.
        return FieldVariants::join(
            'inline-grid place-content-center w-6 h-6 shrink-0',
            'text-[length:var(--text-field-md)] leading-none text-base-content/55',
            'hover:text-primary hover:bg-base-content/[0.08]',
            'rounded-[calc(var(--radius-box)*0.4)] cursor-pointer select-none',
            'transition-colors duration-100',
        );
    }

    private static function checkCell(): string
    {
        // Composed checkbox box (NOT daisyUI `.checkbox`, excluded) matching
        // <x-checkbox> / the editor task-list box. The check glyph (::after) + the
        // .is-checked fill live in sheet.css keyed on the `pn-sheet-check` hook.
        return FieldVariants::join(
            'pn-sheet-check inline-grid place-content-center align-middle',
            'w-[1.15em] h-[1.15em] box-border cursor-pointer',
            'tune-border border-base-content/35 rounded-[calc(var(--radius-box)*0.4)]',
            'hover:border-primary transition-colors duration-100',
        );
    }
}
