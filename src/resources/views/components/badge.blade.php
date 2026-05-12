@props([
    'color' => 'primary',
    'appearance' => 'soft',
    'size' => 'md',
    'icon' => null,
    'pill' => false,
])

@php
    $base = 'inline-flex items-center font-medium tune-border whitespace-nowrap';

    $shape = $pill ? 'rounded-full' : 'rounded-[var(--radius-selector)]';

    $sizeClasses = match($size) {
        'xs' => 'text-[0.625rem] leading-none px-1.5 py-0.5 gap-1',
        'sm' => 'text-xs leading-none px-2 py-0.5 gap-1',
        'lg' => 'text-sm leading-none px-3 py-1 gap-1.5',
        default => 'text-xs leading-none px-2 py-1 gap-1',
    };

    $variantClasses = match("{$appearance}-{$color}") {
        // solid
        'solid-primary'   => 'bg-primary text-primary-content border-primary',
        'solid-secondary' => 'bg-secondary text-secondary-content border-secondary',
        'solid-accent'    => 'bg-accent text-accent-content border-accent',
        'solid-neutral'   => 'bg-neutral text-neutral-content border-neutral',
        'solid-info'      => 'bg-info text-info-content border-info',
        'solid-success'   => 'bg-success text-success-content border-success',
        'solid-warning'   => 'bg-warning text-warning-content border-warning',
        'solid-error'     => 'bg-error text-error-content border-error',

        // outline
        'outline-primary'   => 'bg-transparent text-primary border-primary',
        'outline-secondary' => 'bg-transparent text-secondary border-secondary',
        'outline-accent'    => 'bg-transparent text-accent border-accent',
        'outline-neutral'   => 'bg-transparent text-base-content border-base-content/30',
        'outline-info'      => 'bg-transparent text-info border-info',
        'outline-success'   => 'bg-transparent text-success border-success',
        'outline-warning'   => 'bg-transparent text-warning border-warning',
        'outline-error'     => 'bg-transparent text-error border-error',

        // soft
        'soft-primary'   => 'bg-primary/15 text-primary border-transparent',
        'soft-secondary' => 'bg-secondary/15 text-secondary border-transparent',
        'soft-accent'    => 'bg-accent/15 text-accent border-transparent',
        'soft-neutral'   => 'bg-base-content/15 text-base-content border-transparent',
        'soft-info'      => 'bg-info/15 text-info border-transparent',
        'soft-success'   => 'bg-success/15 text-success border-transparent',
        'soft-warning'   => 'bg-warning/15 text-warning border-transparent',
        'soft-error'     => 'bg-error/15 text-error border-transparent',

        // base-100: primary surface bg + colored text + base-content/10 border
        'base-100-primary'   => 'bg-base-100 text-primary border-base-content/10',
        'base-100-secondary' => 'bg-base-100 text-secondary border-base-content/10',
        'base-100-accent'    => 'bg-base-100 text-accent border-base-content/10',
        'base-100-neutral'   => 'bg-base-100 text-base-content border-base-content/10',
        'base-100-info'      => 'bg-base-100 text-info border-base-content/10',
        'base-100-success'   => 'bg-base-100 text-success border-base-content/10',
        'base-100-warning'   => 'bg-base-100 text-warning border-base-content/10',
        'base-100-error'     => 'bg-base-100 text-error border-base-content/10',

        // base-200: secondary surface bg
        'base-200-primary'   => 'bg-base-200 text-primary border-base-content/10',
        'base-200-secondary' => 'bg-base-200 text-secondary border-base-content/10',
        'base-200-accent'    => 'bg-base-200 text-accent border-base-content/10',
        'base-200-neutral'   => 'bg-base-200 text-base-content border-base-content/10',
        'base-200-info'      => 'bg-base-200 text-info border-base-content/10',
        'base-200-success'   => 'bg-base-200 text-success border-base-content/10',
        'base-200-warning'   => 'bg-base-200 text-warning border-base-content/10',
        'base-200-error'     => 'bg-base-200 text-error border-base-content/10',

        // base-300: tertiary surface bg
        'base-300-primary'   => 'bg-base-300 text-primary border-base-content/10',
        'base-300-secondary' => 'bg-base-300 text-secondary border-base-content/10',
        'base-300-accent'    => 'bg-base-300 text-accent border-base-content/10',
        'base-300-neutral'   => 'bg-base-300 text-base-content border-base-content/10',
        'base-300-info'      => 'bg-base-300 text-info border-base-content/10',
        'base-300-success'   => 'bg-base-300 text-success border-base-content/10',
        'base-300-warning'   => 'bg-base-300 text-warning border-base-content/10',
        'base-300-error'     => 'bg-base-300 text-error border-base-content/10',

        // dot: neutral chip with colored indicator dot
        'dot-primary', 'dot-secondary', 'dot-accent', 'dot-neutral',
        'dot-info', 'dot-success', 'dot-warning', 'dot-error'
            => 'bg-base-100 text-base-content border-base-300',

        default => 'bg-primary/15 text-primary border-transparent',
    };

    $dotColor = match($color) {
        'primary'   => 'bg-primary',
        'secondary' => 'bg-secondary',
        'accent'    => 'bg-accent',
        'neutral'   => 'bg-base-content',
        'info'      => 'bg-info',
        'success'   => 'bg-success',
        'warning'   => 'bg-warning',
        'error'     => 'bg-error',
        default     => 'bg-primary',
    };
@endphp

<span {{ $attributes->merge(['class' => "$base $shape $sizeClasses $variantClasses"]) }}>
    @if($appearance === 'dot')
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
    @elseif($icon)
        <x-i :type="$icon" class="w-[1em] h-[1em]" />
    @endif
    {{ $slot }}
</span>
