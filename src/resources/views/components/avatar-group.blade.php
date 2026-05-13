@props([
    'spacing' => 'normal',
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\AvatarGroupComposer::compose([
        'spacing' => $spacing,
    ]);
@endphp

<div {{ $attributes->class([$c['root']]) }}>
    {{ $slot }}
</div>
