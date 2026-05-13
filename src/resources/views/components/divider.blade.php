@props([
    'direction' => 'horizontal',
    'color' => null,
    'position' => 'center',
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\DividerComposer::compose([
        'direction' => $direction,
        'color' => $color,
        'position' => $position,
    ]);
@endphp

<div {{ $attributes->class([$c['root']]) }}>{{ $slot }}</div>
