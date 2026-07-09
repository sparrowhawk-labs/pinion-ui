<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class ProgressComposer
{
    public static function compose(array $props): array
    {
        $color  = $props['color'] ?? null;
        $size   = $props['size'] ?? 'md';
        $hasVal = array_key_exists('value', $props) && $props['value'] !== null;

        return [
            'root'  => self::root(),
            'track' => self::track($size),
            'fill'  => self::fill($color, $hasVal),
            'label' => self::label(),
        ];
    }

    private static function root(): string
    {
        return 'w-full flex flex-col gap-1';
    }

    private static function track(string $size): string
    {
        // Plain Tailwind track — no daisyUI `.progress` structural class
        // (native <progress>'s ::-webkit-progress-bar pseudo-element is what
        // made daisyUI's CSS load-bearing here; a div track sidesteps that
        // entirely per CLAUDE.md invariant 6).
        return implode(' ', array_filter([
            'w-full bg-base-300 rounded-full overflow-hidden',
            self::sizeClass($size),
        ], fn ($p) => $p !== ''));
    }

    private static function fill(?string $color, bool $hasVal): string
    {
        $parts = ['h-full rounded-full', self::colorClass($color)];

        if (! $hasVal) {
            // No native <progress> stripe animation to lean on anymore —
            // drive the indeterminate sweep with a small custom keyframe
            // (`animate-progress-indeterminate`, defined in pinion-ui.css)
            // instead of daisyUI's `progress-indeterminate`.
            $parts[] = 'w-1/3 animate-progress-indeterminate';
        } else {
            $parts[] = 'transition-[width] duration-300 ease-out';
        }

        return implode(' ', array_filter($parts, fn ($p) => $p !== ''));
    }

    private static function colorClass(?string $color): string
    {
        return match ($color) {
            'primary'   => 'bg-primary',
            'secondary' => 'bg-secondary',
            'accent'    => 'bg-accent',
            'info'      => 'bg-info',
            'success'   => 'bg-success',
            'warning'   => 'bg-warning',
            'error'     => 'bg-error',
            'neutral'   => 'bg-neutral',
            // Div fill has no native fallback color the way <progress> did,
            // so the unset case needs an explicit default (was the browser's
            // stock progress-bar grey before).
            default     => 'bg-neutral',
        };
    }

    private static function sizeClass(string $size): string
    {
        // daisyUI 5 has no progress-{size} modifier, so we drive height via
        // tailwind utilities. Keep the steps small so the bar still looks
        // proportional next to <x-input> / <x-button> at matching sizes.
        return match ($size) {
            'xs' => 'h-1',
            'sm' => 'h-2',
            'lg' => 'h-4',
            default => 'h-3',
        };
    }

    private static function label(): string
    {
        return 'flex items-center justify-end text-xs text-base-content/60 tabular-nums';
    }
}
