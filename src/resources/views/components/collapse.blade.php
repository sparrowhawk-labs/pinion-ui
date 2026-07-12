@props([
    'title' => null,
    'open' => false,
    'icon' => null,
    'bordered' => true,
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\CollapseComposer::compose([
        'icon' => $icon,
        'bordered' => $bordered,
    ]);
    $id = 'collapse-'.bin2hex(random_bytes(4));
@endphp

<div {{ $attributes->class([$c['root']]) }}>
    {{-- Hidden checkbox drives the toggle with pure CSS (no Alpine/JS) via the
         peer-checked variants on the label and the grid-rows panel below. --}}
    <input type="checkbox" id="{{ $id }}" class="peer sr-only" @if($open) checked @endif />
    <label for="{{ $id }}" class="{{ $c['title'] }}">
        <span>
            @isset($titleSlot)
                {{ $titleSlot }}
            @else
                {{ $title }}
            @endisset
        </span>
        @if($icon === 'arrow')
            <svg
                class="{{ $c['icon'] }}"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        @elseif($icon === 'plus')
            <span class="{{ $c['icon'] }}">
                <span class="cc-bar-h absolute inset-0 m-auto h-0.5 w-4 bg-current"></span>
                <span class="cc-bar-v absolute inset-0 m-auto h-4 w-0.5 bg-current transition-transform duration-300"></span>
            </span>
        @endif
    </label>
    <div class="{{ $c['panel'] }}">
        <div class="{{ $c['wrap'] }}">
            <div class="{{ $c['content'] }}">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
