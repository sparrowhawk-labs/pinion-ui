@props([
    'title' => null,
    'open' => false,
    'icon' => 'arrow',
    'bordered' => true,
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\CollapseComposer::compose([
        'icon' => $icon,
        'bordered' => $bordered,
    ]);
@endphp

<div {{ $attributes->class([$c['root']]) }}>
    <input type="checkbox" @if($open) checked @endif />
    <div class="{{ $c['title'] }}">
        @isset($titleSlot)
            {{ $titleSlot }}
        @else
            {{ $title }}
        @endisset
    </div>
    <div class="{{ $c['content'] }}">
        {{ $slot }}
    </div>
</div>
