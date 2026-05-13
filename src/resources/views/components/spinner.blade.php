@props([
    'variant' => 'spinner',
    'size' => 'md',
    'color' => null,
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\SpinnerComposer::compose([
        'variant' => $variant,
        'size' => $size,
        'color' => $color,
    ]);
@endphp

<span {{ $attributes->class([$c['root']]) }} aria-label="Loading..."></span>
