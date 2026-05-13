@props([
    'id' => null,
    'side' => 'left',
    'size' => 'md',
    'backdrop' => true,
    'closeOnBackdrop' => true,
    'escape' => true,
])

@php
    $sidebarId = $id ?? 'sidebar_' . uniqid();
    $c = \SparrowhawkLabs\PinionUi\Compose\SidebarComposer::compose([
        'side'     => $side,
        'size'     => $size,
        'backdrop' => $backdrop,
    ]);
@endphp

<div
    x-data="{ open: false }"
    x-on:open-sidebar-{{ $sidebarId }}.window="open = true"
    x-on:close-sidebar-{{ $sidebarId }}.window="open = false"
    {{ $attributes }}
>
    {{-- Trigger --}}
    @if(isset($trigger))
        <div @click="open = true" class="cursor-pointer">
            {{ $trigger }}
        </div>
    @endif

    {{-- Drawer overlay (teleported to body) --}}
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
            {{-- Backdrop layer — only emitted when backdrop=true so a
                 backdrop=false drawer leaves the page content visible *and*
                 interactive (overlay above is pointer-events-none). --}}
            @if($backdrop)
                <div
                    class="{{ $c['backdrop'] }}"
                    @if($closeOnBackdrop) @click="open = false" @endif
                ></div>
            @endif

            {{-- Panel (slide-in) --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="{{ $c['enterFrom'] }}"
                x-transition:enter-end="{{ $c['enterTo'] }}"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="{{ $c['enterTo'] }}"
                x-transition:leave-end="{{ $c['enterFrom'] }}"
                x-trap.inert.noscroll="open"
                @if($escape) @keydown.escape.window="open = false" @endif
                class="{{ $c['panel'] }}"
                role="dialog"
                aria-modal="true"
            >
                {{-- Floating close button (panel inner edge, opposite the drawer side) --}}
                <button
                    type="button"
                    @click="open = false"
                    class="{{ $c['closeBtnFloat'] }}"
                    aria-label="Close"
                >
                    <svg class="{{ $c['closeIcon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Body --}}
                {{ $slot }}
            </div>
        </div>
    </template>
</div>
