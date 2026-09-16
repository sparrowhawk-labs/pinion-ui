<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class ToggleComposer
{
    public static function compose(array $props): array
    {
        $color = $props['color'] ?? 'primary';
        $appearance = $props['appearance'] ?? 'solid';
        $size = $props['size'] ?? 'md';
        $error = $props['error'] ?? null;
        $disabled = $props['disabled'] ?? false;
        $stateLabel = $props['stateLabel'] ?? false;

        $effectiveColor = $error ? 'error' : $color;

        // State label requires solid track for color contrast and md/lg track for room.
        $showStateLabel = $stateLabel && $appearance === 'solid' && in_array($size, ['md', 'lg'], true);

        return [
            'wrapper' => self::wrapper($disabled),
            'row' => 'inline-flex items-center gap-3',
            'input' => 'peer sr-only',
            'track' => self::track($size, $appearance, $effectiveColor, $showStateLabel),
            'thumb' => self::thumb($size, $appearance),
            'stateOn' => $showStateLabel ? self::stateLabel($size, $effectiveColor, 'on') : '',
            'stateOff' => $showStateLabel ? self::stateLabel($size, $effectiveColor, 'off') : '',
            'label' => self::label($size, $error, $disabled),
            'description' => self::description($size, $error),
        ];
    }

    private static function wrapper(bool $disabled): string
    {
        $base = 'block';
        $cursorClass = $disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer';
        return self::join($base, $cursorClass);
    }

    private static function track(string $size, string $appearance, string $color, bool $hasStateLabel): string
    {
        $sizeClass = match ($size) {
            'sm' => 'h-5 w-9',
            'lg' => 'h-7 w-13',
            default => 'h-6 w-11',
        };

        $base = 'relative inline-block shrink-0 border-[length:var(--border)] rounded-[var(--radius-field)] transition-colors peer-focus-visible:ring-2 peer-focus-visible:ring-offset-1 peer-focus-visible:ring-current';

        // Thumb / state-label sit *inside* the track, so they aren't direct
        // siblings of `<input class="peer">` and `peer-checked:` won't reach
        // them on its own. Drive their state changes via descendant selectors
        // declared on the track itself (which IS a sibling). Travel distance
        // (track − thumb − 2 × offset, in rem): sm 1 / md 1.25 / lg 1.5 — minus
        // 2 × the tune's border width, because `left-0.5` is measured from the
        // padding box: with a thick tune border (tech = 3px) a fixed travel
        // pushed the thumb past the inner edge on the checked side (v0.17.2).
        $thumbTravel = match ($size) {
            'sm' => 'peer-checked:[&_.xy-thumb]:translate-x-[calc(1rem-2*var(--border))]',
            'lg' => 'peer-checked:[&_.xy-thumb]:translate-x-[calc(1.5rem-2*var(--border))]',
            default => 'peer-checked:[&_.xy-thumb]:translate-x-[calc(1.25rem-2*var(--border))]',
        };

        // Soft inverts the colour flow vs solid: solid keeps the thumb white
        // (`bg-base-100`, set on the thumb itself) so the rail carries the
        // signal, while soft flips it — track stays muted, thumb takes the
        // full `{color}` on check (Preline pattern). Driven from the track via
        // the same descendant selector trick as travel.
        $thumbColor = match ($appearance) {
            'soft' => "peer-checked:[&_.xy-thumb]:bg-{$color}",
            // wire: thumb is base-content at rest and inverts to base-100 once the rail fills.
            'wire' => 'peer-checked:[&_.xy-thumb]:bg-base-100',
            default => '',
        };

        $stateFade = $hasStateLabel
            ? 'peer-checked:[&_.xy-state-on]:opacity-100 peer-checked:[&_.xy-state-off]:opacity-0'
            : '';

        return self::join($sizeClass, $base, $thumbTravel, $thumbColor, $stateFade, self::appearanceColor($appearance, $color));
    }

    private static function appearanceColor(string $appearance, string $color): string
    {
        // Two appearances only — solid (colour flows into the rail) and soft
        // (colour flows into the thumb, via track descendant selector above).
        // base-100/200/300 don't carry a clean semantic for switches: a state
        // indicator can't double as a surface, so they were removed.
        return match ($appearance) {
            'solid' =>
                "bg-base-300 border-base-content/10 peer-checked:bg-{$color} peer-checked:border-{$color}",

            'soft' =>
                "bg-base-200 border-base-content/10 peer-checked:bg-{$color}/15 peer-checked:border-{$color}/30",

            // wire (v0.17.2): transparent rail + hairline; fills with the colour on check.
            'wire' =>
                "bg-transparent border-base-content/45 peer-checked:bg-{$color} peer-checked:border-{$color}",

            default =>
                'bg-base-300 border-base-content/10 peer-checked:bg-primary peer-checked:border-primary',
        };
    }

    private static function thumb(string $size, string $appearance = 'solid'): string
    {
        $sizeClass = match ($size) {
            'sm' => 'size-4',
            'lg' => 'size-6',
            default => 'size-5',
        };

        // No peer-checked here — track drives translate-x via [&_.xy-thumb].
        // wire: a flat base-content block, no shadow (colour inverts from the track on check).
        $look = $appearance === 'wire'
            ? 'absolute top-1/2 left-0.5 -translate-y-1/2 rounded-[var(--radius-field)] bg-base-content border-[length:var(--border)] border-transparent shadow-none transition-transform pointer-events-none'
            : 'absolute top-1/2 left-0.5 -translate-y-1/2 rounded-[var(--radius-field)] bg-base-100 border-[length:var(--border)] border-base-content/10 shadow-[var(--shadow-field)] transition-transform pointer-events-none';
        return self::join('xy-thumb', $sizeClass, $look);
    }

    private static function stateLabel(string $size, string $color, string $kind): string
    {
        // ON sits at the left of the track (the empty side when the thumb is on the right).
        // OFF sits at the right (empty side when the thumb is on the left).
        // Initial visibility — track flips it via descendant selector on peer-checked.
        $marker = $kind === 'on' ? 'xy-state-on' : 'xy-state-off';
        $visibility = $kind === 'on' ? 'opacity-0' : 'opacity-100';

        // The label lives in the half the thumb has vacated. On md the free zone is only
        // ~20px wide, so a 9px "OFF" at right-1 ran under the thumb's edge (v0.17.2 fix):
        // md drops to 8px / 2px inset, lg keeps 10px but sits 3px in.
        // ON sits ~1.5px further in than OFF: the "O" glyph's side bearing makes an equal inset
        // read as too far left (user review, 2026-09-16).
        $position = match ($size) {
            'lg' => $kind === 'on' ? 'left-[4.5px]' : 'right-[3px]',
            default => $kind === 'on' ? 'left-[3.5px]' : 'right-0.5',
        };
        $sizeClass = $size === 'lg' ? 'text-[10px]' : 'text-[8px]';

        // Track turns $color when checked → ON uses contrast color.
        // Track is base-300 when unchecked → OFF uses muted base-content.
        $colorClass = $kind === 'on' ? "text-{$color}-content" : 'text-base-content/60';

        return self::join(
            $marker,
            'absolute top-1/2 -translate-y-1/2 font-bold tracking-tight leading-none uppercase transition-opacity pointer-events-none',
            $position,
            $sizeClass,
            $visibility,
            $colorClass
        );
    }

    private static function label(string $size, ?string $error, bool $disabled): string
    {
        $sizeClass = match ($size) {
            'sm' => 'text-[length:var(--text-field-xs)]',
            'lg' => 'text-[length:var(--text-field-md)]',
            default => 'text-[length:var(--text-field-sm)]',
        };

        $colorClass = $error
            ? 'text-error'
            : ($disabled ? 'text-base-content/60' : 'text-base-content');

        return self::join($sizeClass, $colorClass, 'select-none leading-snug');
    }

    private static function description(string $size, ?string $error): string
    {
        $colorClass = $error ? 'text-error' : 'text-base-content/60';
        return self::join('mt-1 text-[length:var(--text-field-xs)]', $colorClass, 'leading-snug');
    }

    private static function join(string ...$parts): string
    {
        $filtered = array_filter($parts, fn ($p) => $p !== '');
        return implode(' ', $filtered);
    }
}
