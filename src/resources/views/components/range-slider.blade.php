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

    // --range-fill (%) drives the WebKit linear-gradient "filled portion"
    // trick in pinion-ui.css's .pn-range — Firefox needs none of this
    // (::-moz-range-progress fills natively from the input's own value).
    // Computed here for the initial paint, then kept live via the
    // x-on:input handler below (Alpine already wired for showValue; we
    // extend it to always track pct regardless of showValue).
    $minNum   = is_numeric($min) ? (float) $min : 0.0;
    $maxNum   = is_numeric($max) ? (float) $max : 100.0;
    $curNum   = is_numeric($current) ? (float) $current : $minNum;
    $rangeNum = $maxNum - $minNum;
    $pct      = $rangeNum > 0 ? (($curNum - $minNum) / $rangeNum) * 100 : 0;
    $pct      = max(0, min(100, $pct));
@endphp

<div class="w-full" x-data="{ pct: {{ $pct }}@if($showValue), v: '{{ $current }}'@endif }">
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
        style="--range-fill: {{ $pct }}%"
        x-bind:style="'--range-fill: ' + pct + '%'"
        @if($name) name="{{ $name }}" @endif
        x-on:input="pct = Math.max(0, Math.min(100, (($event.target.value - {{ $minNum }}) / {{ $rangeNum ?: 1 }}) * 100))@if($showValue); v = $event.target.value @endif"
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
