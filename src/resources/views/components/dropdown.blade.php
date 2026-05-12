@props([
    'label' => null,
    'position' => 'bottom-end',
    'size' => 'md',
    'width' => 'w-52',
])

@php
    $triggerSize = match($size) {
        'sm' => 'h-[var(--h-field-sm)] px-[var(--px-field-sm)] text-[length:var(--text-field-sm)]',
        'lg' => 'h-[var(--h-field-lg)] px-[var(--px-field-lg)] text-[length:var(--text-field-lg)]',
        default => 'h-[var(--h-field-md)] px-[var(--px-field-md)] text-[length:var(--text-field-md)]',
    };

    $itemSize = match($size) {
        'sm' => 'min-h-[var(--h-field-xs)] px-[var(--px-field-sm)] text-[length:var(--text-field-sm)]',
        'lg' => 'min-h-[var(--h-field-md)] px-[var(--px-field-lg)] text-[length:var(--text-field-lg)]',
        default => 'min-h-[var(--h-field-sm)] px-[var(--px-field-md)] text-[length:var(--text-field-md)]',
    };

    $positionClasses = match($position) {
        'bottom-start' => 'left-0 top-full mt-1',
        'top-end' => 'right-0 bottom-full mb-1',
        'top-start' => 'left-0 bottom-full mb-1',
        default => 'right-0 top-full mt-1',
    };
@endphp

<div
    x-data="{ open: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    {{ $attributes->merge(['class' => 'relative inline-block']) }}
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
            class="inline-flex items-center justify-center gap-1.5 font-medium transition-colors rounded-[var(--radius-field)] border-[length:var(--border)] border-base-300 bg-base-100 text-base-content hover:bg-base-200 cursor-pointer {{ $triggerSize }}"
        >
            {{ $label }}
            <svg class="w-4 h-4 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
        class="absolute z-40 {{ $positionClasses }} {{ $width }} bg-base-100 border-[length:var(--border)] border-base-300 rounded-[var(--radius-box)] shadow-lg py-1 overflow-hidden"
        x-cloak
    >
        {{ $slot }}
    </div>
</div>
