@props([
    'id' => null,
    'title' => null,
    'size' => 'md',
    'showClose' => true,
    'closeOnBackdrop' => true,
])

@php
    $modalId = $id ?? 'modal_' . uniqid();

    $sizeClasses = match($size) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-full mx-4',
        default => 'max-w-lg',
    };
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
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-cloak
        >
            {{-- Backdrop --}}
            <div
                class="absolute inset-0 bg-black/50"
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
                class="relative w-full {{ $sizeClasses }} bg-base-100 text-base-content text-[length:var(--text-field-md)] rounded-[var(--radius-box)] border-[length:var(--border)] border-base-300 shadow-lg p-element"
                role="dialog"
                aria-modal="true"
                @if($title) aria-labelledby="{{ $modalId }}_title" @endif
            >
                {{-- Header --}}
                @if($title || $showClose)
                    <div class="flex items-center justify-between mb-[var(--space-compact)]">
                        @if($title)
                            <h3 id="{{ $modalId }}_title" class="text-[length:var(--text-field-lg)] font-semibold text-base-content">
                                {{ $title }}
                            </h3>
                        @else
                            <div></div>
                        @endif

                        @if($showClose)
                            <button
                                type="button"
                                @click="open = false"
                                class="text-base-content/50 hover:text-base-content transition-colors rounded-[var(--radius-field)] p-1 hover:bg-base-200"
                                aria-label="Close"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif

                {{-- Body --}}
                {{ $slot }}

                {{-- Footer / Actions --}}
                @if(isset($actions))
                    <div class="flex items-center justify-end gap-inline mt-[var(--space-element)]">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    </template>
</div>
