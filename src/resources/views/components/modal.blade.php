@props([
    'id' => null,
    'title' => null,
    'size' => 'md',
    'showClose' => true,
    'closeOnBackdrop' => true,
])

@php
    $modalId = $id ?? 'modal_' . uniqid();
    $c = \SparrowhawkLabs\PinionUi\Compose\ModalComposer::compose([
        'size' => $size,
    ]);
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal-{{ $modalId }}.window="open = true"
    x-on:close-modal-{{ $modalId }}.window="open = false"
    {{ $attributes }}
>
    {{-- Trigger --}}
    @if(isset($trigger))
        <div @click="open = true" class="cursor-pointer">
            {{ $trigger }}
        </div>
    @endif

    {{-- Modal overlay --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="{{ $c['overlay'] }}"
            x-cloak
        >
            {{-- Backdrop --}}
            <div
                class="{{ $c['backdrop'] }}"
                @if($closeOnBackdrop) @click="open = false" @endif
            ></div>

            {{-- Modal content --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                x-trap.inert.noscroll="open"
                @keydown.escape.window="open = false"
                class="{{ $c['panel'] }}"
                role="dialog"
                aria-modal="true"
                @if($title) aria-labelledby="{{ $modalId }}_title" @endif
            >
                {{-- Header / close button. With a title, render a normal header row.
                     Without a title, float the × at the panel's top-right corner so
                     body content starts at the top padding (no empty header row). --}}
                @if($title)
                    <div class="{{ $c['header'] }}">
                        <h3 id="{{ $modalId }}_title" class="{{ $c['title'] }}">
                            {{ $title }}
                        </h3>
                        @if($showClose)
                            <button
                                type="button"
                                @click="open = false"
                                class="{{ $c['closeBtn'] }}"
                                aria-label="Close"
                            >
                                <svg class="{{ $c['closeIcon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                @elseif($showClose)
                    <button
                        type="button"
                        @click="open = false"
                        class="{{ $c['closeBtnFloat'] }}"
                        aria-label="Close"
                    >
                        <svg class="{{ $c['closeIconFloat'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif

                {{-- Body --}}
                {{ $slot }}

                {{-- Footer / Actions --}}
                @if(isset($actions))
                    <div class="{{ $c['actions'] }}">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    </template>
</div>
