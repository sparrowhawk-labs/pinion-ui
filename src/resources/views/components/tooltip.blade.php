@props([
    'text' => '',
    'position' => 'top',
    'color' => 'base-100',
    'open' => false,
])

@php
    use SparrowhawkLabs\PinionUi\Compose\TooltipComposer;

    $c = TooltipComposer::compose([
        'position' => $position,
        'color'    => $color,
        'open'     => $open,
    ]);
@endphp

<div
    x-data="{ open: {{ $c['forceOpen'] ? 'true' : 'false' }} }"
    @if(!$c['forceOpen'])
        x-on:mouseenter="open = true"
        x-on:mouseleave="open = false"
        x-on:focusin="open = true"
        x-on:focusout="open = false"
    @endif
    {{ $attributes->merge(['class' => $c['root']]) }}
>
    {{ $slot }}

    <div
        x-show="open"
        x-cloak
        role="tooltip"
        x-transition.opacity.duration.100ms
        class="{{ $c['bubble'] }}"
    >
        <div class="{{ $c['arrow'] }}"></div>
        {{ $text }}
    </div>
</div>
