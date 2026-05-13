@props([
    'orientation' => 'horizontal',
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\ButtonGroupComposer::compose([
        'orientation' => $orientation,
    ]);
@endphp

<div {{ $attributes->class([$c['root']]) }}>{{ $slot }}</div>
