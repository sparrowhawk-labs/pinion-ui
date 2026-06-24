<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * CalendarComposer — class strings for the <x-calendar> minimal date picker.
 *
 * PURE function (load-bearing, like every Composer): returns a FLAT dict of class
 * strings only. The calendar's behavior (month math, selection) lives in the opt-in
 * Alpine factory (calendar.js). The calendar is FULLY utility-composed — it owns no
 * descendant-selector stylesheet (no calendar.css); everything tracks data-theme ×
 * data-tune through the daisyUI color classes + tune tokens in these strings.
 *
 * Aesthetic: "a quiet month grid" (PrelineUI-flavoured) — a flat themed popover, rounded
 * day cells, primary fill on the selected day, a soft ring on today. Chrome from plain
 * Tailwind + daisyUI *color* classes + tune tokens, so it tracks data-theme × data-tune.
 *
 * Returned keys:
 *   panel       — the popover surface (also the standalone inline container)
 *   header      — top row: prev | month label | next
 *   navBtn      — a prev/next icon button
 *   monthLabel  — the "YYYY年 M月" label
 *   weekRow     — the 7-column grid (weekday header + each week)
 *   weekday     — a weekday header cell (日…土)
 *   day         — a selectable day cell (resting)
 *   daySelected — the selected day (primary fill)
 *   dayToday    — today's ring (when not selected)
 *   dayMuted    — a blank cell (no day)
 *   footer      — bottom row (今日 / クリア)
 *   footerBtn   — a footer text button
 *   trigger     — the standalone date-input trigger button
 */
class CalendarComposer
{
    public static function compose(array $props): array
    {
        $size = $props['size'] ?? 'md';

        return [
            'panel'       => 'w-[15.5rem] bg-base-100 rounded-[var(--radius-box)] tune-border border-base-300 shadow-[var(--shadow-box)] p-3 flex flex-col gap-2',
            'header'      => 'flex items-center justify-between gap-2',
            'navBtn'      => 'inline-grid place-content-center w-7 h-7 shrink-0 rounded-[calc(var(--radius-box)*0.45)] text-base-content/60 hover:text-base-content hover:bg-base-content/[0.08] transition-colors cursor-pointer select-none [&_svg]:w-[1.05rem] [&_svg]:h-[1.05rem] [&_svg]:stroke-2',
            'monthLabel'  => 'text-[length:var(--text-field-md)] font-medium text-base-content tabular-nums select-none',
            'weekRow'     => 'grid grid-cols-7 gap-0.5',
            'weekday'     => 'h-7 grid place-content-center text-[length:var(--text-field-xs)] text-base-content/40 select-none',
            'day'         => 'h-8 grid place-content-center text-[length:var(--text-field-sm)] tabular-nums rounded-[calc(var(--radius-box)*0.5)] text-base-content hover:bg-base-content/[0.08] cursor-pointer select-none transition-colors',
            'daySelected' => 'bg-primary text-primary-content hover:bg-primary',
            'dayToday'    => 'ring-1 ring-inset ring-primary/45 text-primary',
            'dayMuted'    => 'h-8',
            'yearRow'     => 'grid grid-cols-4 gap-1',
            'year'        => 'h-9 grid place-content-center text-[length:var(--text-field-sm)] tabular-nums rounded-[calc(var(--radius-box)*0.5)] text-base-content hover:bg-base-content/[0.08] cursor-pointer select-none transition-colors',
            'footer'      => 'flex items-center justify-between pt-1 border-t border-base-300',
            'footerBtn'   => 'text-[length:var(--text-field-xs)] text-base-content/60 hover:text-primary px-1.5 py-1 rounded-[calc(var(--radius-box)*0.4)] hover:bg-base-content/[0.06] cursor-pointer select-none transition-colors',
            'trigger'     => self::trigger($size),
        ];
    }

    private static function trigger(string $size): string
    {
        // The standalone date-input trigger (button that opens the popover). Mirrors the
        // field scale used by <x-input> (text-entry padding/height tokens).
        $dims = match ($size) {
            'sm'    => 'h-[var(--h-field-sm)] px-[var(--px-input-sm)] text-[length:var(--text-field-sm)]',
            'lg'    => 'h-[var(--h-field-lg)] px-[var(--px-input-lg)] text-[length:var(--text-field-lg)]',
            default => 'h-[var(--h-field-md)] px-[var(--px-input-md)] text-[length:var(--text-field-md)]',
        };

        return FieldVariants::join(
            $dims,
            'inline-flex items-center gap-2 justify-between',
            'rounded-[var(--radius-field)] tune-border border-base-300 bg-base-100',
            'text-base-content hover:border-primary focus:outline-none focus-visible:ring-1 focus-visible:ring-primary',
            'cursor-pointer select-none transition-colors',
            '[&_svg]:w-[1.05rem] [&_svg]:h-[1.05rem] [&_svg]:stroke-[1.75] [&_svg]:text-base-content/50',
        );
    }
}
