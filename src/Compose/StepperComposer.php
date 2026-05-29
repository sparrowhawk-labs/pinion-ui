<?php

namespace SparrowhawkLabs\PinionUi\Compose;

/**
 * Stepper visualises a multi-step process — sign-up flow, checkout, wizard.
 * Each item is one of `done` / `current` / `upcoming`; done items show a
 * check inside a filled circle, current shows the step number with a
 * primary-coloured ring, upcoming shows the number with a muted ring.
 *
 * State → colour maps are pipe-joined strings consumed via the helper
 * `StepperComposer::pick($map, $key)` (same pattern as TimelineComposer).
 */
class StepperComposer
{
    public static function compose(array $props): array
    {
        $orientation = $props['orientation'] ?? 'horizontal';
        $variant     = $props['variant']     ?? 'numbered';

        $orientation = $orientation === 'vertical' ? 'vertical' : 'horizontal';
        $variant     = $variant === 'dotted' ? 'dotted' : 'numbered';

        return [
            'root'            => self::rootClass($orientation),
            'item'            => self::itemClass($orientation),
            'circle'          => self::circleClass($variant),
            'connector'       => self::connectorClass($orientation, $variant),
            'label'           => 'text-sm font-medium',
            'desc'            => 'text-xs text-base-content/60 mt-0.5',
            'stateColors'     => self::stateColors(),
            'stateConnectors' => self::stateConnectors(),
            'orientation'     => $orientation,
            'variant'         => $variant,
        ];
    }

    private static function rootClass(string $orientation): string
    {
        return $orientation === 'vertical'
            ? 'flex flex-col gap-1'
            : 'flex items-start gap-2 w-full';
    }

    private static function itemClass(string $orientation): string
    {
        return $orientation === 'vertical'
            ? 'flex gap-3 items-start'
            : 'flex flex-col items-center shrink-0';
    }

    private static function circleClass(string $variant): string
    {
        return $variant === 'dotted'
            ? 'w-3 h-3 rounded-full border-2 shrink-0 flex items-center justify-center transition-colors'
            : 'w-9 h-9 rounded-full border-2 shrink-0 flex items-center justify-center text-sm font-semibold transition-colors';
    }

    /**
     * Connector geometry is variant-aware. The offset that places the bar
     * at the circle's centre depends on the circle's diameter:
     *   - numbered : w-9/h-9 (36px) → center 18px → use mt-4 / ml-4 (16px,
     *     close enough that 1px-2px drift is invisible at the bar's stroke)
     *   - dotted   : w-3/h-3 (12px) → center 6px → mt-4 / ml-4 leaves the
     *     bar 10px BELOW (or to the right of) the dot, so the dotted row
     *     reads as "dots floating above a disconnected line" instead of a
     *     joined progress. Use mt-[5px] / ml-[5px] so the bar's 2px stroke
     *     centres on the dot's 6px centre.
     * Also tighten min-w-* / h-* so dotted reads as the compact pager
     * variant the user expects (carousel position etc.).
     */
    private static function connectorClass(string $orientation, string $variant = 'numbered'): string
    {
        if ($orientation === 'vertical') {
            return $variant === 'dotted'
                ? 'w-0.5 h-3 ml-[5px] shrink-0'
                : 'w-0.5 h-6 ml-4 shrink-0';
        }
        return $variant === 'dotted'
            ? 'flex-1 h-0.5 mt-[5px] min-w-3'
            : 'flex-1 h-0.5 mt-4 min-w-6';
    }

    /**
     * State → circle colour class.
     * done = primary fill, current = primary ring, upcoming = muted ring.
     */
    private static function stateColors(): string
    {
        return implode('|', [
            'done=bg-primary border-primary text-primary-content',
            'current=bg-base-100 border-primary text-primary',
            'upcoming=bg-base-100 border-base-content/20 text-base-content/40',
            'default=bg-base-100 border-base-content/20 text-base-content/40',
        ]);
    }

    /**
     * State of the item → colour of the connector AFTER it. done connector
     * is filled primary; everything else stays subdued.
     */
    private static function stateConnectors(): string
    {
        return implode('|', [
            'done=bg-primary',
            'current=bg-base-content/20',
            'upcoming=bg-base-content/20',
            'default=bg-base-content/20',
        ]);
    }

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
