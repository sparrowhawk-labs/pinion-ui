@props([
    'locales' => [],
    'current' => null,
    'size' => 'sm',
    'position' => 'bottom-end',
    'width' => 'w-40',
])

@php
    // Locale-routing-agnostic by design: this component doesn't know how
    // `href` is built (locale prefix, subdomain, query param, …) — the
    // consumer resolves each locale's URL and hands over a flat array:
    // [['code' => 'ja', 'label' => '🇯🇵 JA', 'href' => '/ja/foo'], ...].
    // `active` per-item is optional; `current` (a locale code) is the usual
    // way to mark the active one from the outside.
    $active = collect($locales)->first(fn ($l) => ($l['code'] ?? null) === $current)
        ?? collect($locales)->first(fn ($l) => $l['active'] ?? false)
        ?? ($locales[0] ?? null);
    $activeLabel = $active['label'] ?? ($active['code'] ?? '');
@endphp

<x-dropdown :label="$activeLabel" :size="$size" :position="$position" :width="$width" {{ $attributes }}>
    @foreach($locales as $loc)
        <x-menu-item
            :href="$loc['href'] ?? '#'"
            :active="($loc['code'] ?? null) === ($active['code'] ?? null)"
            :size="$size"
        >
            {{ $loc['label'] ?? ($loc['code'] ?? '') }}
        </x-menu-item>
    @endforeach
</x-dropdown>
