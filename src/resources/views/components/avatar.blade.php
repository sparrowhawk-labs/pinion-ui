@props([
    'src' => null,
    'alt' => '',
    'initials' => null,
    'icon' => null,
    'color' => 'neutral',
    'appearance' => 'soft',
    'size' => 'md',
    'shape' => 'circle',
    'status' => null,
])

@php
    $sizeClasses = match($size) {
        'xs' => 'w-6 h-6 text-[0.625rem]',
        'sm' => 'w-8 h-8 text-xs',
        'lg' => 'w-14 h-14 text-lg',
        'xl' => 'w-20 h-20 text-2xl',
        default => 'w-10 h-10 text-sm',
    };

    $shapeClasses = match($shape) {
        'square' => 'rounded-[var(--radius-box)]',
        'rounded' => 'rounded-[var(--radius-field)]',
        default => 'rounded-full',
    };

    $variantClasses = match("{$appearance}-{$color}") {
        'solid-primary'   => 'bg-primary text-primary-content',
        'solid-secondary' => 'bg-secondary text-secondary-content',
        'solid-accent'    => 'bg-accent text-accent-content',
        'solid-neutral'   => 'bg-neutral text-neutral-content',
        'solid-info'      => 'bg-info text-info-content',
        'solid-success'   => 'bg-success text-success-content',
        'solid-warning'   => 'bg-warning text-warning-content',
        'solid-error'     => 'bg-error text-error-content',

        'soft-primary'   => 'bg-primary/15 text-primary',
        'soft-secondary' => 'bg-secondary/15 text-secondary',
        'soft-accent'    => 'bg-accent/15 text-accent',
        'soft-neutral'   => 'bg-base-content/15 text-base-content',
        'soft-info'      => 'bg-info/15 text-info',
        'soft-success'   => 'bg-success/15 text-success',
        'soft-warning'   => 'bg-warning/15 text-warning',
        'soft-error'     => 'bg-error/15 text-error',

        'outline-primary'   => 'bg-transparent text-primary tune-border border-primary',
        'outline-secondary' => 'bg-transparent text-secondary tune-border border-secondary',
        'outline-accent'    => 'bg-transparent text-accent tune-border border-accent',
        'outline-neutral'   => 'bg-transparent text-base-content tune-border border-base-300',
        'outline-info'      => 'bg-transparent text-info tune-border border-info',
        'outline-success'   => 'bg-transparent text-success tune-border border-success',
        'outline-warning'   => 'bg-transparent text-warning tune-border border-warning',
        'outline-error'     => 'bg-transparent text-error tune-border border-error',

        default => 'bg-base-200 text-base-content',
    };

    $statusColor = match($status) {
        'online'  => 'bg-success',
        'offline' => 'bg-neutral',
        'busy'    => 'bg-error',
        'away'    => 'bg-warning',
        default   => null,
    };

    $statusSize = match($size) {
        'xs' => 'w-1.5 h-1.5',
        'sm' => 'w-2 h-2',
        'lg' => 'w-3.5 h-3.5',
        'xl' => 'w-5 h-5',
        default => 'w-2.5 h-2.5',
    };
@endphp

<span {{ $attributes->merge(['class' => "relative inline-flex shrink-0 $sizeClasses $shapeClasses"]) }}>
    <span class="w-full h-full flex items-center justify-center font-semibold overflow-hidden {{ $shapeClasses }} {{ $variantClasses }}">
        @if($src)
            <img src="{{ $src }}" alt="{{ $alt }}" class="w-full h-full object-cover">
        @elseif($initials)
            <span class="select-none leading-none tune-optical-center">{{ $initials }}</span>
        @elseif($icon)
            <x-i :type="$icon" class="w-[60%] h-[60%]" />
        @else
            {{ $slot }}
        @endif
    </span>

    @if($statusColor)
        <span class="absolute bottom-0 right-0 {{ $statusSize }} {{ $statusColor }} rounded-full ring-2 ring-base-100"></span>
    @endif
</span>
