@props([
    'text' => '',
    'position' => 'top',
    'color' => null,
    'open' => false,
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\TooltipComposer::compose([
        'position' => $position,
        'color' => $color,
        'open' => $open,
    ]);
@endphp

<div {{ $attributes->class([$c['root']]) }} data-tip="{{ $text }}">
    {{ $slot }}
</div>
