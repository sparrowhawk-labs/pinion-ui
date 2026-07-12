<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * Migrated off daisyUI's `timeline`/`timeline-*` structural classes (project
 * rule, CLAUDE.md invariant 6 — daisyUI classes are semantic-color-only now).
 *
 * Layout is reproduced with plain CSS Grid: each `<li>` is a 3x3 grid
 * (`[side-start] [middle] [side-end]` x `[before] [icon-row] [after]`),
 * matching daisyUI's own `timeline.css` grid-area algebra (verified against
 * `pinion-ui-playground/node_modules/daisyui/components/timeline.css` — no
 * public daisyUI doc spells out the grid internals, only the class-name
 * surface in `docs/daisyui/pages/daisyui-5-components__6.md`).
 *
 * `.timeline-box` in that same source has NO speech-bubble arrow pseudo
 * element — it is a plain bordered card (border/radius/bg/padding/shadow
 * only). So the plain-bordered-box choice here is exact parity with
 * daisyUI, not a simplification.
 *
 * `compact` is reproduced as: the icon/connector column (vertical) or row
 * (horizontal) collapses toward zero width/height, and BOTH the `start` and
 * `end` slot positions resolve to the same "after" grid cell — so whichever
 * Blade branch renders (time vs. box), content always lands on one side,
 * matching the documented "drops every item to [one] side" behavior without
 * reproducing every one of daisyUI's internal `:has()` selector branches.
 */
class TimelineComposer
{
    public static function compose(array $props): array
    {
        $orientation = $props['orientation'] ?? 'vertical';
        $compact     = (bool) ($props['compact'] ?? false);
        $snap        = (bool) ($props['snap'] ?? false);
        $appearance  = $props['appearance'] ?? 'solid';
        $orientationKey = self::orientationKey($orientation);

        return [
            'root'            => self::rootClass($orientationKey),
            'orientation'     => $orientationKey,
            'li'              => self::liClass($orientationKey, $compact, $snap),
            'middle'          => self::middleClass(),
            'sideStart'       => self::sideClass($orientationKey, $compact, 'start'),
            'sideEnd'         => self::sideClass($orientationKey, $compact, 'end'),
            'connectorBefore' => self::connectorClass($orientationKey, 'before'),
            'connectorAfter'  => self::connectorClass($orientationKey, 'after'),
            'box'             => self::boxClass(),
            'stateColors'     => self::stateColors($appearance),
            'hrColors'        => self::hrColors($appearance),
        ];
    }

    /**
     * Normalised orientation key — unknown values fall back to 'vertical'.
     */
    private static function orientationKey(string $orientation): string
    {
        return $orientation === 'horizontal' ? 'horizontal' : 'vertical';
    }

    /**
     * `gap-sm` (tune-aware `--spacing-sm`, 8px floor) keeps a real
     * gap between adjacent `<li>` items. Every `sideStart`/`sideEnd`/`box`
     * slot spans the item's full grid height (vertical) or width
     * (horizontal) with no margin of its own, so with zero inter-item gap
     * two consecutive boxes render pixel-touching: their borders coincide
     * (doubling `tune-border`'s width) and, on tunes with a hard-offset
     * `--shadow-box` (e.g. brutal), one box's shadow is drawn directly over
     * the next box. The floor guarantees breathing room even on the
     * tightest tune/strength combination.
     */
    private static function rootClass(string $orientationKey): string
    {
        return $orientationKey === 'horizontal'
            ? 'flex flex-row relative gap-sm'
            : 'flex flex-col relative gap-sm';
    }

    /**
     * Per-item 3x3 grid. `compact` shrinks the icon/connector track
     * (columns for vertical, rows for horizontal) toward zero; `snap`
     * shrinks the track on the *other* axis so the icon sits near the
     * start of the box instead of vertically/horizontally centered on it
     * — mirroring daisyUI's `--timeline-col-start` / `--timeline-row-start`
     * custom-property overrides for `timeline-compact` / `timeline-snap-icon`.
     */
    private static function liClass(string $orientationKey, bool $compact, bool $snap): string
    {
        if ($orientationKey === 'horizontal') {
            $cols = $snap ? '0.5rem_auto_1fr' : '1fr_auto_1fr';
            $rows = $compact ? '0_auto_1fr' : '1fr_auto_1fr';

            return "grid grid-cols-[{$cols}] grid-rows-[{$rows}] items-center relative";
        }

        $cols = $compact ? '0_auto_1fr' : '1fr_auto_1fr';
        $rows = $snap ? '0.5rem_auto_1fr' : '1fr_auto_1fr';

        return "grid grid-cols-[{$cols}] grid-rows-[{$rows}] items-center relative justify-items-center";
    }

    private static function middleClass(): string
    {
        return 'row-start-2 col-start-2';
    }

    /**
     * `$edge` is 'start' or 'end' — matches daisyUI's `timeline-start` /
     * `timeline-end` slot names. Whichever div in the Blade view carries
     * this class (the muted time text or the title box) is positioned by
     * it; the Blade view decides per item which slot holds which content.
     */
    private static function sideClass(string $orientationKey, bool $compact, string $edge): string
    {
        if ($compact) {
            return $orientationKey === 'horizontal'
                ? 'col-start-1 col-end-4 row-start-3 row-end-4 justify-self-center self-start'
                : 'row-start-1 row-end-4 col-start-3 col-end-4 self-center justify-self-start';
        }

        if ($orientationKey === 'horizontal') {
            return $edge === 'start'
                ? 'col-start-1 col-end-4 row-start-1 row-end-2 justify-self-center self-end'
                : 'col-start-1 col-end-4 row-start-3 row-end-4 justify-self-center self-start';
        }

        return $edge === 'start'
            ? 'row-start-1 row-end-4 col-start-1 col-end-2 self-center justify-self-end'
            : 'row-start-1 row-end-4 col-start-3 col-end-4 self-center justify-self-start';
    }

    /**
     * `$position` is 'before' or 'after' — the connector segment above/left
     * of the icon, and below/right of it, respectively.
     */
    private static function connectorClass(string $orientationKey, string $position): string
    {
        if ($orientationKey === 'horizontal') {
            return $position === 'before'
                ? 'row-start-2 col-start-1 h-1 w-full my-auto'
                : 'row-start-2 col-start-3 h-1 w-full my-auto';
        }

        return $position === 'before'
            ? 'row-start-1 col-start-2 w-1 h-full mx-auto'
            : 'row-start-3 col-start-2 w-1 h-full mx-auto';
    }

    /**
     * Was `.timeline-box`. daisyUI's own timeline.css gives this class only
     * border + radius + bg + padding + font-size + shadow — no arrow — so
     * this is a direct port, not a fallback simplification.
     */
    private static function boxClass(): string
    {
        return 'rounded-[var(--radius-box)] tune-border border-base-300 bg-base-100 shadow-[var(--shadow-box)] py-2 px-4 text-xs';
    }

    /**
     * State → middle-icon color class.
     *
     * `appearance='solid'` (default; reverted from the v0.3.0 soft default in
     * v0.3.4): done/default segments use full `text-primary` so the done-chain
     * keeps a clear visual hierarchy.
     * `appearance='soft'` (opt-in): done/default use `text-primary/70` so a list
     * of many done items reads as a calm gradient rather than a saturated wall.
     * `current` and `upcoming` are independent of appearance.
     */
    private static function stateColors(string $appearance): string
    {
        $done = $appearance === 'solid' ? 'text-primary' : 'text-primary/70';

        return implode('|', [
            "done={$done}",
            'current=text-base-content',
            'upcoming=text-base-content/40',
            "default={$done}",
        ]);
    }

    /**
     * State → connector (<hr>) color class.
     *
     * `appearance='solid'` (default since the v0.3.4 revert): done segments keep
     * the full `bg-primary` line. `appearance='soft'` (opt-in) uses
     * `bg-primary/30` for a muted connector.
     */
    private static function hrColors(string $appearance): string
    {
        $done = $appearance === 'solid' ? 'bg-primary' : 'bg-primary/30';

        return implode('|', [
            "done={$done}",
            'current=bg-base-content/30',
            'upcoming=bg-base-content/15',
            "default={$done}",
        ]);
    }

    /**
     * Helper for blade: pick a class out of the pipe-joined map.
     */
    public static function pick(string $map, ?string $key): string
    {
        $pairs = [];
        foreach (explode('|', $map) as $kv) {
            [$k, $v] = array_pad(explode('=', $kv, 2), 2, '');
            $pairs[$k] = $v;
        }
        return $pairs[$key] ?? ($pairs['default'] ?? '');
    }
}
