<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class FieldVariants
{
    public static function variant(string $appearance, string $effectiveColor): string
    {
        return match ("{$appearance}-{$effectiveColor}") {
            'outline-neutral' => 'bg-base-100 tune-border border-base-300 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary',
            'outline-primary' => 'bg-base-100 tune-border border-primary focus-within:ring-1 focus-within:ring-primary',
            'outline-info'    => 'bg-base-100 tune-border border-info focus-within:ring-1 focus-within:ring-info',
            'outline-success' => 'bg-base-100 tune-border border-success focus-within:ring-1 focus-within:ring-success',
            'outline-warning' => 'bg-base-100 tune-border border-warning focus-within:ring-1 focus-within:ring-warning',
            'outline-error'   => 'bg-base-100 tune-border border-error focus-within:ring-1 focus-within:ring-error',

            'soft-neutral' => 'bg-base-200 tune-border border-transparent focus-within:ring-1 focus-within:ring-primary',
            'soft-primary' => 'bg-primary/10 tune-border border-transparent focus-within:ring-1 focus-within:ring-primary',
            'soft-info'    => 'bg-info/10 tune-border border-transparent focus-within:ring-1 focus-within:ring-info',
            'soft-success' => 'bg-success/10 tune-border border-transparent focus-within:ring-1 focus-within:ring-success',
            'soft-warning' => 'bg-warning/10 tune-border border-transparent focus-within:ring-1 focus-within:ring-warning',
            'soft-error'   => 'bg-error/10 tune-border border-transparent focus-within:ring-1 focus-within:ring-error',

            'underline-neutral' => 'bg-transparent border-b border-base-300 focus-within:border-primary',
            'underline-primary' => 'bg-transparent border-b border-primary',
            'underline-info'    => 'bg-transparent border-b border-info',
            'underline-success' => 'bg-transparent border-b border-success',
            'underline-warning' => 'bg-transparent border-b border-warning',
            'underline-error'   => 'bg-transparent border-b border-error',

            'ghost-neutral' => 'bg-transparent tune-border border-transparent hover:bg-base-200/60 focus-within:bg-base-200',
            'ghost-primary' => 'bg-transparent tune-border border-transparent hover:bg-primary/5 focus-within:bg-primary/10',
            'ghost-info'    => 'bg-transparent tune-border border-transparent hover:bg-info/5 focus-within:bg-info/10',
            'ghost-success' => 'bg-transparent tune-border border-transparent hover:bg-success/5 focus-within:bg-success/10',
            'ghost-warning' => 'bg-transparent tune-border border-transparent hover:bg-warning/5 focus-within:bg-warning/10',
            'ghost-error'   => 'bg-transparent tune-border border-transparent hover:bg-error/5 focus-within:bg-error/10',

            default => 'bg-base-100 tune-border border-base-300 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary',
        };
    }

    public static function radius(string $appearance): string
    {
        return $appearance === 'underline' ? '' : 'rounded-[var(--radius-field)]';
    }

    public static function labelColor(?string $error): string
    {
        return $error ? 'text-error' : 'text-base-content';
    }

    public static function hintColor(?string $error): string
    {
        return $error ? 'text-error' : 'text-base-content/50';
    }

    public static function join(string ...$parts): string
    {
        return implode(' ', array_filter($parts, fn ($p) => $p !== ''));
    }
}
