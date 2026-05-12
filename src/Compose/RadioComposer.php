<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class RadioComposer
{
    public static function compose(array $props): array
    {
        $color = $props['color'] ?? 'primary';
        $appearance = $props['appearance'] ?? 'solid';
        $size = $props['size'] ?? 'md';
        $error = $props['error'] ?? null;
        $disabled = $props['disabled'] ?? false;

        $effectiveColor = $error ? 'error' : $color;

        return [
            'wrapper' => self::wrapper($disabled),
            'row' => 'flex items-center gap-2',
            'input' => 'peer sr-only',
            'visualBox' => self::visualBox($size, $appearance, $effectiveColor),
            'dot' => self::dot($size, $appearance),
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

    private static function visualBox(string $size, string $appearance, string $color): string
    {
        $sizeClass = match ($size) {
            'sm' => 'size-3.5',
            'lg' => 'size-5',
            default => 'size-4',
        };

        $base = 'relative shrink-0 inline-flex items-center justify-center border-[length:var(--border)] rounded-full transition-colors peer-focus-visible:ring-2 peer-focus-visible:ring-offset-1 peer-focus-visible:ring-current';

        // Reveal the inner dot when the peer input is checked.
        $dotState = 'peer-checked:[&_.xy-dot]:scale-100';

        return self::join($sizeClass, $base, $dotState, self::appearanceColor($appearance, $color));
    }

    private static function appearanceColor(string $appearance, string $color): string
    {
        // Soft & base-N use base-content for the 'neutral' color (better contrast on surfaces).
        // The Tailwind JIT scan happens via pinion-ui.css safelist (@source inline) — full
        // permutations are pre-generated, so $color/$appearance interpolation is safe here.
        $tone = ($color === 'neutral' && $appearance !== 'solid') ? 'base-content' : $color;

        return match (true) {
            $appearance === 'solid' =>
                "bg-base-100 border-base-content/10 text-{$color}-content peer-checked:bg-{$color} peer-checked:border-{$color}",

            $appearance === 'soft' =>
                "bg-{$tone}/10 border-transparent text-{$tone}/80 peer-checked:bg-{$tone}/15 peer-checked:border-{$tone}/30",

            in_array($appearance, ['base-100', 'base-200', 'base-300'], true) =>
                "bg-{$appearance} border-base-content/15 text-{$tone}/80 peer-checked:border-{$tone}/70",

            default =>
                'bg-base-100 border-base-content/10 text-primary-content peer-checked:bg-primary peer-checked:border-primary',
        };
    }

    private static function dot(string $size, string $appearance): string
    {
        $sizeClass = match ($size) {
            'sm' => 'size-1.5',
            'lg' => 'size-2.5',
            default => 'size-2',
        };
        return self::join('xy-dot', $sizeClass, 'rounded-full bg-current scale-0 transition-transform');
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
        $indentClass = match ($size) {
            'sm' => 'ml-[22px]',
            'lg' => 'ml-7',
            default => 'ml-6',
        };
        $colorClass = $error ? 'text-error' : 'text-base-content/60';
        return self::join($indentClass, 'mt-1 text-[length:var(--text-field-xs)]', $colorClass, 'leading-snug');
    }

    private static function join(string ...$parts): string
    {
        $filtered = array_filter($parts, fn ($p) => $p !== '');
        return implode(' ', $filtered);
    }
}
