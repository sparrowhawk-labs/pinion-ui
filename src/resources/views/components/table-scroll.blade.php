@props([
    'fadeColor' => 'base-100',
    'buttonStyle' => 'circle',
    'scrollAmount' => 0.6,
    'showButtons' => true,
    'prevLabel' => '前へスクロール',
    'nextLabel' => '次へスクロール',
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\TableScrollComposer::compose([
        'fadeColor' => $fadeColor,
        'buttonStyle' => $buttonStyle,
    ]);
@endphp

<div
    x-data="{
        canScrollLeft: false,
        canScrollRight: false,
        container: null,
        observer: null,
        init() {
            this.container = this.$refs.scrollContainer;
            this.checkScroll();
            this.container.addEventListener('scroll', () => this.checkScroll(), { passive: true });
            this.observer = new ResizeObserver(() => this.checkScroll());
            this.observer.observe(this.container);
            for (const child of this.container.children) this.observer.observe(child);
        },
        destroy() { this.observer?.disconnect(); },
        checkScroll() {
            if (!this.container) return;
            const { scrollLeft, scrollWidth, clientWidth } = this.container;
            this.canScrollLeft = scrollLeft > 1;
            this.canScrollRight = scrollLeft < scrollWidth - clientWidth - 1;
        },
        scrollTo(direction) {
            const amount = this.container.clientWidth * {{ (float) $scrollAmount }};
            this.container.scrollBy({
                left: direction === 'left' ? -amount : amount,
                behavior: 'smooth',
            });
        },
    }"
    {{ $attributes->class([$c['wrapper']]) }}
>
    @if($showButtons)
        {{-- Left fade + button --}}
        <div x-show="canScrollLeft" x-cloak class="{{ $c['leftFade'] }} left-0"
             x-transition.opacity.duration.150ms></div>
        <button
            type="button"
            x-show="canScrollLeft"
            x-cloak
            x-transition.opacity.duration.150ms
            @click="scrollTo('left')"
            class="{{ $c['buttonOuterLeft'] }}"
            aria-label="{{ $prevLabel }}"
        >
            <span class="{{ $c['buttonInner'] }}">
                <x-i type="alt-arrow-left" variant="linear" :class="$c['iconSize']" />
            </span>
        </button>

        {{-- Right fade + button --}}
        <div x-show="canScrollRight" x-cloak class="{{ $c['rightFade'] }} right-0"
             x-transition.opacity.duration.150ms></div>
        <button
            type="button"
            x-show="canScrollRight"
            x-cloak
            x-transition.opacity.duration.150ms
            @click="scrollTo('right')"
            class="{{ $c['buttonOuterRight'] }}"
            aria-label="{{ $nextLabel }}"
        >
            <span class="{{ $c['buttonInner'] }}">
                <x-i type="alt-arrow-right" variant="linear" :class="$c['iconSize']" />
            </span>
        </button>
    @endif

    <div x-ref="scrollContainer" class="{{ $c['scrollContainer'] }}">
        {{ $slot }}
    </div>
</div>
