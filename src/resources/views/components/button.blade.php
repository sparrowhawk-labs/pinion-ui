@props([
    'color' => 'primary',
    'appearance' => 'solid',
    'size' => 'md',
    'loading' => false,
    'disabled' => false,
    'as' => 'button',
    'href' => null,
    'icon' => null,
    'iconRight' => null,
])

@php
    $tag = $href ? 'a' : $as;

    $base = 'inline-flex items-center justify-center whitespace-nowrap font-medium tracking-wide transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-[var(--radius-field)] border-[length:var(--border)]';

    $sizeClasses = match($size) {
        'xs' => 'h-[var(--h-field-xs)] px-[var(--px-field-xs)] text-[length:var(--text-field-xs)] gap-1',
        'sm' => 'h-[var(--h-field-sm)] px-[var(--px-field-sm)] text-[length:var(--text-field-sm)] gap-1.5',
        'lg' => 'h-[var(--h-field-lg)] px-[var(--px-field-lg)] text-[length:var(--text-field-lg)] gap-2.5',
        default => 'h-[var(--h-field-md)] px-[var(--px-field-md)] text-[length:var(--text-field-md)] gap-2',
    };

    $variantClasses = match("{$appearance}-{$color}") {
        // solid: bg-color + content text
        'solid-primary'   => 'bg-primary text-primary-content border-primary hover:bg-primary/90 focus-visible:ring-primary',
        'solid-secondary' => 'bg-secondary text-secondary-content border-secondary hover:bg-secondary/90 focus-visible:ring-secondary',
        'solid-accent'    => 'bg-accent text-accent-content border-accent hover:bg-accent/90 focus-visible:ring-accent',
        'solid-neutral'   => 'bg-neutral text-neutral-content border-neutral hover:bg-neutral/90 focus-visible:ring-neutral',
        'solid-info'      => 'bg-info text-info-content border-info hover:bg-info/90 focus-visible:ring-info',
        'solid-success'   => 'bg-success text-success-content border-success hover:bg-success/90 focus-visible:ring-success',
        'solid-warning'   => 'bg-warning text-warning-content border-warning hover:bg-warning/90 focus-visible:ring-warning',
        'solid-error'     => 'bg-error text-error-content border-error hover:bg-error/90 focus-visible:ring-error',

        // outline: transparent bg, colored border/text, invert on hover
        'outline-primary'   => 'bg-transparent text-primary border-primary hover:bg-primary hover:text-primary-content focus-visible:ring-primary',
        'outline-secondary' => 'bg-transparent text-secondary border-secondary hover:bg-secondary hover:text-secondary-content focus-visible:ring-secondary',
        'outline-accent'    => 'bg-transparent text-accent border-accent hover:bg-accent hover:text-accent-content focus-visible:ring-accent',
        'outline-neutral'   => 'bg-transparent text-base-content border-base-content/10 hover:bg-base-content hover:text-base-100 focus-visible:ring-base-content',
        'outline-info'      => 'bg-transparent text-info border-info hover:bg-info hover:text-info-content focus-visible:ring-info',
        'outline-success'   => 'bg-transparent text-success border-success hover:bg-success hover:text-success-content focus-visible:ring-success',
        'outline-warning'   => 'bg-transparent text-warning border-warning hover:bg-warning hover:text-warning-content focus-visible:ring-warning',
        'outline-error'     => 'bg-transparent text-error border-error hover:bg-error hover:text-error-content focus-visible:ring-error',

        // base-100: primary surface bg + colored text + base-content/50 border, hover=base-200
        'base-100-primary'   => 'bg-base-100 text-primary border-base-content/10 hover:bg-base-200 focus-visible:ring-primary',
        'base-100-secondary' => 'bg-base-100 text-secondary border-base-content/10 hover:bg-base-200 focus-visible:ring-secondary',
        'base-100-accent'    => 'bg-base-100 text-accent border-base-content/10 hover:bg-base-200 focus-visible:ring-accent',
        'base-100-neutral'   => 'bg-base-100 text-base-content border-base-content/10 hover:bg-base-200 focus-visible:ring-base-content',
        'base-100-info'      => 'bg-base-100 text-info border-base-content/10 hover:bg-base-200 focus-visible:ring-info',
        'base-100-success'   => 'bg-base-100 text-success border-base-content/10 hover:bg-base-200 focus-visible:ring-success',
        'base-100-warning'   => 'bg-base-100 text-warning border-base-content/10 hover:bg-base-200 focus-visible:ring-warning',
        'base-100-error'     => 'bg-base-100 text-error border-base-content/10 hover:bg-base-200 focus-visible:ring-error',

        // base-200: secondary surface bg, hover=base-300
        'base-200-primary'   => 'bg-base-200 text-primary border-base-content/10 hover:bg-base-300 focus-visible:ring-primary',
        'base-200-secondary' => 'bg-base-200 text-secondary border-base-content/10 hover:bg-base-300 focus-visible:ring-secondary',
        'base-200-accent'    => 'bg-base-200 text-accent border-base-content/10 hover:bg-base-300 focus-visible:ring-accent',
        'base-200-neutral'   => 'bg-base-200 text-base-content border-base-content/10 hover:bg-base-300 focus-visible:ring-base-content',
        'base-200-info'      => 'bg-base-200 text-info border-base-content/10 hover:bg-base-300 focus-visible:ring-info',
        'base-200-success'   => 'bg-base-200 text-success border-base-content/10 hover:bg-base-300 focus-visible:ring-success',
        'base-200-warning'   => 'bg-base-200 text-warning border-base-content/10 hover:bg-base-300 focus-visible:ring-warning',
        'base-200-error'     => 'bg-base-200 text-error border-base-content/10 hover:bg-base-300 focus-visible:ring-error',

        // base-300: tertiary surface bg, hover=base-content/15 (subtle dim)
        'base-300-primary'   => 'bg-base-300 text-primary border-base-content/10 hover:bg-base-content/15 focus-visible:ring-primary',
        'base-300-secondary' => 'bg-base-300 text-secondary border-base-content/10 hover:bg-base-content/15 focus-visible:ring-secondary',
        'base-300-accent'    => 'bg-base-300 text-accent border-base-content/10 hover:bg-base-content/15 focus-visible:ring-accent',
        'base-300-neutral'   => 'bg-base-300 text-base-content border-base-content/10 hover:bg-base-content/15 focus-visible:ring-base-content',
        'base-300-info'      => 'bg-base-300 text-info border-base-content/10 hover:bg-base-content/15 focus-visible:ring-info',
        'base-300-success'   => 'bg-base-300 text-success border-base-content/10 hover:bg-base-content/15 focus-visible:ring-success',
        'base-300-warning'   => 'bg-base-300 text-warning border-base-content/10 hover:bg-base-content/15 focus-visible:ring-warning',
        'base-300-error'     => 'bg-base-300 text-error border-base-content/10 hover:bg-base-content/15 focus-visible:ring-error',

        // soft: tinted bg + colored text, no border
        'soft-primary'   => 'bg-primary/10 text-primary border-transparent hover:bg-primary/20 focus-visible:ring-primary',
        'soft-secondary' => 'bg-secondary/10 text-secondary border-transparent hover:bg-secondary/20 focus-visible:ring-secondary',
        'soft-accent'    => 'bg-accent/10 text-accent border-transparent hover:bg-accent/20 focus-visible:ring-accent',
        'soft-neutral'   => 'bg-base-content/10 text-base-content border-transparent hover:bg-base-content/20 focus-visible:ring-base-content',
        'soft-info'      => 'bg-info/10 text-info border-transparent hover:bg-info/20 focus-visible:ring-info',
        'soft-success'   => 'bg-success/10 text-success border-transparent hover:bg-success/20 focus-visible:ring-success',
        'soft-warning'   => 'bg-warning/10 text-warning border-transparent hover:bg-warning/20 focus-visible:ring-warning',
        'soft-error'     => 'bg-error/10 text-error border-transparent hover:bg-error/20 focus-visible:ring-error',

        // link: text only, underline on hover
        'link-primary'   => 'bg-transparent text-primary border-transparent underline-offset-4 hover:underline focus-visible:ring-primary',
        'link-secondary' => 'bg-transparent text-secondary border-transparent underline-offset-4 hover:underline focus-visible:ring-secondary',
        'link-accent'    => 'bg-transparent text-accent border-transparent underline-offset-4 hover:underline focus-visible:ring-accent',
        'link-neutral'   => 'bg-transparent text-base-content border-transparent underline-offset-4 hover:underline focus-visible:ring-base-content',
        'link-info'      => 'bg-transparent text-info border-transparent underline-offset-4 hover:underline focus-visible:ring-info',
        'link-success'   => 'bg-transparent text-success border-transparent underline-offset-4 hover:underline focus-visible:ring-success',
        'link-warning'   => 'bg-transparent text-warning border-transparent underline-offset-4 hover:underline focus-visible:ring-warning',
        'link-error'     => 'bg-transparent text-error border-transparent underline-offset-4 hover:underline focus-visible:ring-error',

        // ghost: color-independent, base-content text, base-200 hover
        'ghost-primary', 'ghost-secondary', 'ghost-accent', 'ghost-neutral',
        'ghost-info', 'ghost-success', 'ghost-warning', 'ghost-error'
            => 'bg-transparent text-base-content border-transparent hover:bg-base-200 focus-visible:ring-base-300',

        default => 'bg-primary text-primary-content border-primary hover:bg-primary/90 focus-visible:ring-primary',
    };

    $stateClasses = ($disabled || $loading) ? 'opacity-50 cursor-not-allowed pointer-events-none' : 'cursor-pointer';
@endphp

<{{ $tag }}
    {{ $attributes->merge([
        'class' => "$base $sizeClasses $variantClasses $stateClasses",
        'disabled' => ($tag === 'button' && ($disabled || $loading)) ? true : null,
        'href' => $href,
    ]) }}
>
    @if($loading)
        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @elseif($icon)
        <x-i :type="$icon" class="w-4 h-4" />
    @endif

    {{ $slot }}

    @if($iconRight && !$loading)
        <x-i :type="$iconRight" class="w-4 h-4" />
    @endif
</{{ $tag }}>
