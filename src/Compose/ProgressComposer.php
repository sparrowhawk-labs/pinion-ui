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
            'bar'   => self::bar($color, $size, $hasVal),
            'label' => self::label(),
        ];
    }

    private static function root(): string
    {
        return 'w-full flex flex-col gap-1';
    }

    private static function bar(?string $color, string $size, bool $hasVal): string
    {
        $parts = ['progress', 'w-full'];

        $parts[] = self::colorClass($color);
        $parts[] = self::sizeClass($size);

        if (! $hasVal) {
            // daisyUI animates the stripe when no `value` attribute is present.
            $parts[] = 'progress-indeterminate';
        }

        return implode(' ', array_filter($parts, fn ($p) => $p !== ''));
    }

    private static function colorClass(?string $color): string
    {
        return match ($color) {
            'primary'   => 'progress-primary',
            'secondary' => 'progress-secondary',
            'accent'    => 'progress-accent',
            'info'      => 'progress-info',
            'success'   => 'progress-success',
            'warning'   => 'progress-warning',
            'error'     => 'progress-error',
            'neutral'   => 'progress-neutral',
            default     => '',
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
