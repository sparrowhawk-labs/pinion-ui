@props([
    'options' => [],     // [{ value, label?, icon?(raw svg string) }, …]
    'value' => null,     // initial selected value (defaults to the first option)
    'size' => 'md',
    'ariaLabel' => null,
])

@php
    // <x-segmented> — a minimal iOS-style segmented control (a sliding pill track). Pure
    // inline Alpine. On change it sets a hidden wire:model input (if bound) AND dispatches
    // a bubbling `segmented-change` CustomEvent { value }, so non-Livewire hosts can react.
    $opts = array_values($options);
    $sizeCls = match ($size) {
        'sm'    => 'h-[var(--h-field-sm)] text-[length:var(--text-field-sm)]',
        'lg'    => 'h-[var(--h-field-lg)] text-[length:var(--text-field-lg)]',
        default => 'h-[var(--h-field-md)] text-[length:var(--text-field-md)]',
    };
    $first = $opts[0]['value'] ?? null;
    $wireModel    = $attributes->whereStartsWith('wire:model');
    $hasWireModel = $wireModel->isNotEmpty();
@endphp

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class(['inline-flex items-center gap-0.5 p-0.5 rounded-[var(--radius-field)] tune-border border-base-300 bg-base-200/70 ' . $sizeCls]) }}
    role="tablist"
    @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    x-data="{
        current: @js($value ?? $first),
        set(v) {
            this.current = v;
            if (this.$refs.model) { this.$refs.model.value = v; this.$refs.model.dispatchEvent(new Event('input', { bubbles: true })); }
            this.$dispatch('segmented-change', { value: v });
        },
    }"
>
    @foreach($opts as $o)
        <button
            type="button"
            role="tab"
            class="inline-flex items-center justify-center gap-1.5 h-full px-3 rounded-[calc(var(--radius-field)*0.8)] font-medium cursor-pointer select-none transition-colors duration-150 [&_svg]:w-4 [&_svg]:h-4"
            x-bind:class="current === @js($o['value']) ? 'bg-base-100 text-base-content shadow-[var(--shadow-field)]' : 'text-base-content/55 hover:text-base-content'"
            x-bind:aria-selected="current === @js($o['value'])"
            x-on:click="set(@js($o['value']))"
        >
            @isset($o['icon']){!! $o['icon'] !!}@endisset
            @if(!empty($o['label']))<span>{{ $o['label'] }}</span>@endif
        </button>
    @endforeach

    @if($hasWireModel)<input type="hidden" x-ref="model" {{ $wireModel }} />@endif
</div>
