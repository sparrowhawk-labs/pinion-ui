@aware([
    'size' => 'md',
    'multiple' => false,
])

@props([
    'name' => null,
    'title' => '',
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\AccordionComposer::compose([
        'size' => $size,
    ]);
    $itemKey = $name ?? 'item_'.bin2hex(random_bytes(4));
    $openExpr = $multiple
        ? "open.includes('{$itemKey}')"
        : "open === '{$itemKey}'";
    $toggleExpr = $multiple
        ? "open.includes('{$itemKey}') ? (open = open.filter(i => i !== '{$itemKey}')) : open.push('{$itemKey}')"
        : "open = (open === '{$itemKey}') ? null : '{$itemKey}'";
@endphp

<div>
    <button
        type="button"
        class="{{ $c['header'] }}"
        x-on:click="{{ $toggleExpr }}"
        x-bind:aria-expanded="{{ $openExpr }}"
    >
        <span>{{ $title }}</span>
        <svg
            class="{{ $c['icon'] }}"
            x-bind:class="{{ $openExpr }} && 'rotate-180'"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>
    <div
        x-show="{{ $openExpr }}"
        x-collapse
        x-cloak
    >
        <div class="{{ $c['content'] }}">
            {{ $slot }}
        </div>
    </div>
</div>
