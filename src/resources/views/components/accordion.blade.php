@props([
    'items' => [],
    'multiple' => false,
    'size' => 'md',
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\AccordionComposer::compose([
        'size' => $size,
    ]);

    $uid = 'accordion_' . uniqid();
@endphp

<div
    x-data="{ open: {{ $multiple ? '[]' : 'null' }} }"
    {{ $attributes->merge(['class' => $c['root']]) }}
>
    @foreach($items as $key => $item)
        @php $itemKey = is_string($key) ? $key : $loop->index; @endphp
        <div>
            <button
                type="button"
                class="{{ $c['header'] }}"
                @click="{{ $multiple
                    ? "open.includes('$itemKey') ? open = open.filter(i => i !== '$itemKey') : open.push('$itemKey')"
                    : "open = open === '$itemKey' ? null : '$itemKey'"
                }}"
                :aria-expanded="{{ $multiple ? "open.includes('$itemKey')" : "open === '$itemKey'" }}"
            >
                <span>{{ $item['title'] ?? '' }}</span>
                <svg
                    class="{{ $c['icon'] }}"
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
                <div class="{{ $c['content'] }}">
                    {!! $item['content'] ?? '' !!}
                </div>
            </div>
        </div>
    @endforeach
</div>
