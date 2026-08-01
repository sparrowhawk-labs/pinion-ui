<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * v0.5.x rewrite: no longer built on daisyUI's `.stats`/`.stat`/`.stat-*`
 * component classes. Design rule (2026-07-09): daisyUI classes are only
 * ever used for their SEMANTIC COLOR utilities (bg-primary, text-error,
 * border-base-content/10, …) — never structural/behavioral component
 * classes, which aren't tune-reactive and can carry hidden layout/border
 * bugs pinion-ui doesn't control (see the `.stat:not(:last-child)` divider
 * investigation this fix grew out of — daisyUI's own divider silently
 * computed a 0px border in some theme×tune combos). Layout is now plain
 * Tailwind flex; a stat's internal grouping ("Total Visits" alongside
 * multiple siblings) is <x-stat-group>'s job (StatGroupComposer), not
 * daisyUI's `.stats` container. BC: props unchanged.
 */
class StatComposer
{
    public static function compose(array $props): array
    {
        $valueColor = $props['valueColor'] ?? null;
        $trend      = $props['trend']      ?? null;
        // Boolean-parse rather than (bool)-cast: the plain-attribute form
        // `wrapped="false"` delivers the STRING "false", which a raw cast
        // treats as true — so every stat inside an <x-stat-group> silently
        // kept its own card border+radius and the group rendered doubled
        // seams (shipped that way in the playground for months before the
        // 2026-08-01 seam audit caught it). FILTER_VALIDATE_BOOLEAN maps
        // "false"/"0"/"off"/"no" → false; unrecognizable values fall back
        // to the old cast so no previously-working call site changes.
        $wrapped = array_key_exists('wrapped', $props)
            ? (filter_var($props['wrapped'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $props['wrapped'])
            : true;

        return [
            'root'  => $wrapped
                ? 'rounded-[var(--radius-box)] tune-border border-base-content/10 bg-base-100 shadow-[var(--shadow-box)] overflow-hidden'
                : '',
            'inner' => 'flex items-center justify-between gap-4 px-6 py-4',
            'text'  => 'flex flex-col gap-1 min-w-0',
            'figure' => self::figureClass($valueColor),
            'title'  => 'text-xs text-base-content/60 whitespace-nowrap',
            'value'  => self::valueClass($valueColor),
            'desc'   => self::descClass($trend),
        ];
    }

    /**
     * Trend → arrow character. NOT part of the compose() dict — kept as a
     * separate helper so the dict stays class-strings-only per the Compose
     * invariant (see CLAUDE.md). The Blade view calls this directly.
     */
    public static function arrowChar(?string $trend): string
    {
        return match ($trend) {
            'up'    => '↑',
            'down'  => '↓',
            'flat'  => '→',
            default => '',
        };
    }

    private static function valueClass(?string $color): string
    {
        $base = 'text-3xl font-extrabold whitespace-nowrap';

        return match ($color) {
            'primary'   => $base.' text-primary',
            'secondary' => $base.' text-secondary',
            'accent'    => $base.' text-accent',
            'info'      => $base.' text-info',
            'success'   => $base.' text-success',
            'warning'   => $base.' text-warning',
            'error'     => $base.' text-error',
            default     => $base.' text-base-content',
        };
    }

    private static function figureClass(?string $color): string
    {
        $base = 'shrink-0 flex items-center justify-center';

        return match ($color) {
            'primary'   => $base.' text-primary',
            'secondary' => $base.' text-secondary',
            'accent'    => $base.' text-accent',
            'info'      => $base.' text-info',
            'success'   => $base.' text-success',
            'warning'   => $base.' text-warning',
            'error'     => $base.' text-error',
            default     => $base.' text-base-content/60',
        };
    }

    private static function descClass(?string $trend): string
    {
        $base = 'text-xs whitespace-nowrap';

        return match ($trend) {
            'up'   => $base.' text-success',
            'down' => $base.' text-error',
            'flat' => $base.' text-base-content/50',
            default => $base.' text-base-content/60',
        };
    }
}
