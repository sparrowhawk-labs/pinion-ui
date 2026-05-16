@aware([
    'variant' => 'underline',
    'size' => 'md',
])

@props([
    'name',
    'label' => null,
    'icon' => null,
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\TabsComposer::compose([
        'variant' => $variant,
        'size' => $size,
    ]);
@endphp

<button
    type="button"
    role="tab"
    data-pn-tab="{{ $name }}"
    x-bind:aria-selected="activeTab === @js($name)"
    x-bind:class="activeTab === @js($name) ? '{{ $c['tabActive'] }}' : '{{ $c['tabIdle'] }}'"
    class="{{ $c['tabBase'] }}"
    x-on:click="activeTab = @js($name)"
>
    @if($icon)
        <span class="{{ $c['iconWrap'] }}">{!! $icon !!}{{ $label ?? $name }}</span>
    @else
        {{ $label ?? $name }}
    @endif
</button>

<div
    role="tabpanel"
    class="{{ $c['panel'] }}"
    x-show="activeTab === @js($name)"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-cloak
>
    {{ $slot }}
</div>
