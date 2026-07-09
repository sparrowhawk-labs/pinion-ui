@props([
    'direction' => 'horizontal',
    'shadow' => true,
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\StatGroupComposer::compose([
        'direction' => $direction,
        'shadow' => $shadow,
    ]);
@endphp

<div {{ $attributes->class([$c['root']]) }}>
    {{ $slot }}
</div>
