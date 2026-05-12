@props([
    'items' => [],
    'multiple' => false,
    'size' => 'md',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'min-h-[var(--h-field-sm)] px-[var(--px-field-sm)] text-[length:var(--text-field-sm)]',
        'lg' => 'min-h-[var(--h-field-lg)] px-[var(--px-field-lg)] text-[length:var(--text-field-lg)]',
        default => 'min-h-[var(--h-field-md)] px-[var(--px-field-md)] text-[length:var(--text-field-md)]',
    };

    $uid = 'accordion_' . uniqid();
@endphp

<div
    x-data="{ open: {{ $multiple ? '[]' : 'null' }} }"
    {{ $attributes->merge(['class' => 'w-full divide-y divide-base-300 radius-box tune-border border-base-300 overflow-hidden']) }}
>
    @foreach($items as $key => $item)
        @php $itemKey = is_string($key) ? $key : $loop->index; @endphp
        <div>
            <button
                type="button"
                class="w-full flex items-center justify-between {{ $sizeClasses }} py-0 font-medium text-base-content hover:bg-base-200/50 transition-colors cursor-pointer"
                @click="{{ $multiple
                    ? "open.includes('$itemKey') ? open = open.filter(i => i !== '$itemKey') : open.push('$itemKey')"
                    : "open = open === '$itemKey' ? null : '$itemKey'"
                }}"
                :aria-expanded="{{ $multiple ? "open.includes('$itemKey')" : "open === '$itemKey'" }}"
            >
                <span>{{ $item['title'] ?? '' }}</span>
                <svg
                    class="w-4 h-4 shrink-0 transition-transform duration-200"
                    :class="{{ $multiple ? "open.includes('$itemKey') && 'rotate-180'" : "open === '$itemKey' && 'rotate-180'" }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
            <div
                x-show="{{ $multiple ? "open.includes('$itemKey')" : "open === '$itemKey'" }}"
                x-collapse
                x-cloak
            >
                <div class="{{ $sizeClasses }} py-[var(--space-compact)] text-base-content/70">
                    {!! $item['content'] ?? '' !!}
                </div>
            </div>
        </div>
    @endforeach
</div>
