@props([
    'position' => 'top-end',
    'dot' => false,
    'color' => 'error',
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\IndicatorComposer::compose([
        'position' => $position,
        'dot' => $dot,
        'color' => $color,
    ]);
@endphp

<div {{ $attributes->class([$c['root']]) }}>
    <span class="{{ $c['item'] }}">@if(!$dot){{ $badge ?? '' }}@endif</span>
    {{ $slot }}
</div>
