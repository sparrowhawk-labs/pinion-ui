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

<div {{ $attributes->class([$c['root']]) }}>
    <div class="{{ $c['lineStart'] }}"></div>
    @if ($slot->isNotEmpty())
        <span class="{{ $c['label'] }}">{{ $slot }}</span>
    @endif
    <div class="{{ $c['lineEnd'] }}"></div>
</div>
