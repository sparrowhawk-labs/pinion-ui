@props([
    'name' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'min' => 0,
    'max' => 100,
    'value' => null,
    'step' => 1,
    'color' => 'primary',
    'size' => 'md',
    'showValue' => false,
    'disabled' => false,
    'required' => false,
])

@php
    use SparrowhawkLabs\PinionUi\Compose\RangeSliderComposer;

    $inputId  = $attributes->get('id', ($name ? $name . '_' : 'range_') . uniqid());
    $hintText = $error ?: $hint;
    $current  = $value !== null ? $value : $min;

    $c = RangeSliderComposer::compose([
        'color' => $color,
        'size'  => $size,
        'error' => $error,
    ]);
@endphp

<div class="w-full" @if($showValue) x-data="{ v: '{{ $current }}' }" @endif>
    @if($label)
        <div class="flex items-baseline justify-between mb-1.5">
            <label for="{{ $inputId }}" class="block text-[length:var(--text-field-sm)] font-medium {{ $c['labelColor'] }}">
                {{ $label }}@if($required)<span class="text-error ml-0.5">*</span>@endif
            </label>
            @if($showValue)
                <span class="text-[length:var(--text-field-xs)] text-base-content/70 tabular-nums" x-text="v">{{ $current }}</span>
            @endif
        </div>
    @endif

    <input
        type="range"
        id="{{ $inputId }}"
        min="{{ $min }}"
        max="{{ $max }}"
        step="{{ $step }}"
        value="{{ $current }}"
        @if($name) name="{{ $name }}" @endif
        @if($showValue) x-on:input="v = $event.target.value" @endif
        {{ $attributes->whereStartsWith('wire:') }}
        {{ $attributes->whereDoesntStartWith('wire:')->merge(['class' => $c['input']]) }}
        @if($required) required @endif
        @if($disabled) disabled @endif
    />

    @if($hintText || ($showValue && !$label))
        <div class="flex items-baseline justify-between mt-1">
            <p class="text-[length:var(--text-field-sm)] {{ $c['hintColor'] }}">{{ $hintText ?? '' }}</p>
            @if($showValue && !$label)
                <span class="text-[length:var(--text-field-xs)] text-base-content/70 tabular-nums shrink-0 ml-2" x-text="v">{{ $current }}</span>
            @endif
        </div>
    @endif
</div>
