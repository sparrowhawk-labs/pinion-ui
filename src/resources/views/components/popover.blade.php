@props([
    'placement' => 'bottom',
    'width' => 'w-72',
    'arrow' => true,
    'trigger' => 'click', // 'click' | 'hover'
])

@php
    use SparrowhawkLabs\PinionUi\Compose\PopoverComposer;

    $c = PopoverComposer::compose([
        'placement' => $placement,
        'width'     => $width,
        'arrow'     => $arrow,
    ]);

    $alpineOpen  = $trigger === 'hover'
        ? "x-on:mouseenter=\"open = true\" x-on:mouseleave=\"open = false\""
        : "x-on:click=\"open = !open\" x-on:click.outside=\"open = false\"";
@endphp

<div
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
    {{ $attributes->merge(['class' => $c['root']]) }}
>
    {{-- Trigger slot --}}
    @if(isset($triggerSlot))
        <div {!! $alpineOpen !!} class="inline-block cursor-pointer">
            {{ $triggerSlot }}
        </div>
    @endif

    {{-- Panel --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        role="dialog"
        class="{{ $c['panel'] }}"
    >
        @if($c['showArrow'])
            <div class="{{ $c['arrow'] }}"></div>
        @endif
        {{ $slot }}
    </div>
</div>
