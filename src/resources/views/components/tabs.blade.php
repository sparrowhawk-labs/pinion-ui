@props([
    'tabs' => [],
    'variant' => 'underline',
    'size' => 'md',
    'default' => null,
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\TabsComposer::compose([
        'variant' => $variant,
        'size' => $size,
    ]);

    $tabKeys = array_keys($tabs);
    $defaultTab = $default ?? ($tabKeys[0] ?? null);
@endphp

<div
    x-data="{ activeTab: '{{ $defaultTab }}' }"
    {{ $attributes->merge(['class' => $c['root']]) }}
>
    {{-- Tab buttons --}}
    <div class="{{ $c['tabList'] }}" role="tablist">
        @foreach($tabs as $key => $tab)
            <button
                type="button"
                role="tab"
                :aria-selected="activeTab === '{{ $key }}'"
                :class="activeTab === '{{ $key }}' ? '{{ $c['tabActive'] }}' : '{{ $c['tabIdle'] }}'"
                class="{{ $c['tabBase'] }}"
                @click="activeTab = '{{ $key }}'"
            >
                @if(isset($tab['icon']))
                    <span class="{{ $c['iconWrap'] }}">
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
    <div class="{{ $c['panels'] }}">
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
