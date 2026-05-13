@props([
    'label' => null,
    'position' => 'bottom-end',
    'size' => 'md',
    'width' => 'w-52',
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\DropdownComposer::compose([
        'position' => $position,
        'size' => $size,
        'width' => $width,
    ]);
@endphp

<div
    x-data="{ open: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    {{ $attributes->merge(['class' => $c['root']]) }}
>
    {{-- Trigger --}}
    @if(isset($trigger))
        <div @click="open = !open" class="cursor-pointer">
            {{ $trigger }}
        </div>
    @elseif($label)
        <button
            type="button"
            @click="open = !open"
            class="{{ $c['trigger'] }}"
        >
            {{ $label }}
            <svg class="{{ $c['icon'] }}" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </button>
    @endif

    {{-- Menu --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="{{ $c['menu'] }}"
        x-cloak
    >
        {{ $slot }}
    </div>
</div>
