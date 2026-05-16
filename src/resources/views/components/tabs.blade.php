@props([
    'variant' => 'underline',
    'size' => 'md',
    'default' => null,
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\TabsComposer::compose([
        'variant' => $variant,
        'size' => $size,
    ]);
@endphp

<div
    x-data="{ activeTab: @js($default) }"
    x-init="if (!activeTab) { activeTab = $el.querySelector('[data-pn-tab]')?.dataset.pnTab ?? null }"
    {{ $attributes->merge(['class' => $c['root']]) }}
    role="tablist"
>
    {{ $slot }}
</div>
