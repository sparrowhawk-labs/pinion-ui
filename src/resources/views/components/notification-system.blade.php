@props([
    'position' => 'bottom-right',
    'appearance' => 'bordered-left',
    'size' => 'md',
    'duration' => 3000,
    'eventName' => 'notify',
    'closeLabel' => null,
])

@php $closeLabel ??= pn_trans('notification.close', '閉じる'); @endphp

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\NotificationSystemComposer::compose([
        'position' => $position,
        'appearance' => $appearance,
        'size' => $size,
    ]);

    $variantMap = [
        'info'    => $c['variantInfo'],
        'success' => $c['variantSuccess'],
        'warning' => $c['variantWarning'],
        'error'   => $c['variantError'],
    ];
    $iconColorMap = [
        'info'    => $c['iconColorInfo'],
        'success' => $c['iconColorSuccess'],
        'warning' => $c['iconColorWarning'],
        'error'   => $c['iconColorError'],
    ];

    $flash = session('notify');
@endphp

<div
    x-data="{
        notifications: [],
        variants: @js($variantMap),
        iconColors: @js($iconColorMap),
        defaultVariant: @js($variantMap['info']),
        defaultIconColor: @js($iconColorMap['info']),
        init() {
            @if($flash)
                this.add({
                    detail: {
                        type: @js($flash['type'] ?? 'info'),
                        content: @js($flash['content'] ?? ''),
                    },
                    timeStamp: Date.now(),
                });
            @endif
        },
        add(e) {
            const id = e.timeStamp ?? Date.now() + Math.random();
            this.notifications.push({
                id,
                type: e.detail.type,
                content: e.detail.content,
                show: false,
            });
            this.$nextTick(() => {
                const n = this.notifications.find(x => x.id === id);
                if (n) n.show = true;
                setTimeout(() => this.dismiss(id), {{ (int) $duration }});
            });
        },
        dismiss(id) {
            const n = this.notifications.find(x => x.id === id);
            if (!n) return;
            n.show = false;
            setTimeout(() => {
                this.notifications = this.notifications.filter(x => x.id !== id);
            }, 300);
        },
        variantFor(type) { return this.variants[type] || this.defaultVariant; },
        iconColorFor(type) { return this.iconColors[type] || this.defaultIconColor; },
    }"
    {{ '@'.$eventName }}.window="add($event)"
    {{ $attributes->class([$c['wrapper']]) }}
    role="status"
    aria-live="polite"
>
    <template x-for="n in notifications" :key="n.id">
        <div
            x-show="n.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            :class="variantFor(n.type)"
            class="{{ $c['item'] }}"
        >
            {{-- Icon: render all four, x-show one based on n.type --}}
            <div x-show="n.type === 'info'" class="{{ $c['iconWrap'] }}">
                <x-i type="info-circle" variant="linear" :class="$c['iconSize']" ::class="iconColorFor('info')" />
            </div>
            <div x-show="n.type === 'success'" class="{{ $c['iconWrap'] }}">
                <x-i type="check-circle" variant="linear" :class="$c['iconSize']" ::class="iconColorFor('success')" />
            </div>
            <div x-show="n.type === 'warning'" class="{{ $c['iconWrap'] }}">
                <x-i type="danger-triangle" variant="linear" :class="$c['iconSize']" ::class="iconColorFor('warning')" />
            </div>
            <div x-show="n.type === 'error'" class="{{ $c['iconWrap'] }}">
                <x-i type="close-circle" variant="linear" :class="$c['iconSize']" ::class="iconColorFor('error')" />
            </div>

            <div class="{{ $c['content'] }}">
                <span x-text="n.content"></span>
            </div>

            <button type="button" @click="dismiss(n.id)" class="{{ $c['closeBtn'] }}" aria-label="{{ $closeLabel }}">
                ×
            </button>
        </div>
    </template>
</div>
