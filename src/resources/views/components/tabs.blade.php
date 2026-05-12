@props([
    'tabs' => [],
    'variant' => 'underline',
    'size' => 'md',
    'default' => null,
])

@php
    $tabKeys = array_keys($tabs);
    $defaultTab = $default ?? ($tabKeys[0] ?? null);

    $sizeClasses = match($size) {
        'sm' => 'h-[var(--h-field-sm)] px-[var(--px-field-sm)] text-[length:var(--text-field-sm)]',
        'lg' => 'h-[var(--h-field-lg)] px-[var(--px-field-lg)] text-[length:var(--text-field-lg)]',
        default => 'h-[var(--h-field-md)] px-[var(--px-field-md)] text-[length:var(--text-field-md)]',
    };

    $tabBase = 'relative inline-flex items-center font-medium transition-colors cursor-pointer whitespace-nowrap';

    $activeClasses = match($variant) {
        'boxed' => 'bg-primary text-primary-content rounded-[var(--radius-field)]',
        'pill' => 'bg-base-200 text-base-content rounded-[var(--radius-selector)]',
        default => 'text-primary border-b-2 border-primary',
    };

    $inactiveClasses = match($variant) {
        'boxed' => 'text-base-content/60 hover:text-base-content hover:bg-base-200/50 rounded-[var(--radius-field)]',
        'pill' => 'text-base-content/60 hover:text-base-content hover:bg-base-200/50 rounded-[var(--radius-selector)]',
        default => 'text-base-content/60 hover:text-base-content border-b-2 border-transparent',
    };

    $wrapperClasses = match($variant) {
        'boxed' => 'bg-base-200/50 p-1 rounded-[var(--radius-box)] gap-1',
        'pill' => 'gap-1',
        default => 'border-b border-base-300 gap-0',
    };
@endphp

<div
    x-data="{ activeTab: '{{ $defaultTab }}' }"
    {{ $attributes->merge(['class' => 'w-full']) }}
>
    {{-- Tab buttons --}}
    <div class="flex {{ $wrapperClasses }}" role="tablist">
        @foreach($tabs as $key => $tab)
            <button
                type="button"
                role="tab"
                :aria-selected="activeTab === '{{ $key }}'"
                :class="activeTab === '{{ $key }}' ? '{{ $activeClasses }}' : '{{ $inactiveClasses }}'"
                class="{{ $tabBase }} {{ $sizeClasses }}"
                @click="activeTab = '{{ $key }}'"
            >
                @if(isset($tab['icon']))
                    <span class="inline-flex items-center gap-inline">
                        {!! $tab['icon'] !!}
                        {{ $tab['label'] }}
                    </span>
                @else
                    {{ $tab['label'] }}
                @endif
            </button>
        @endforeach
    </div>

    {{-- Tab panels --}}
    <div class="mt-[var(--space-compact)]">
        @foreach($tabs as $key => $tab)
            <div
                x-show="activeTab === '{{ $key }}'"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                role="tabpanel"
                x-cloak
            >
                {!! $tab['content'] ?? '' !!}
            </div>
        @endforeach
    </div>
</div>
