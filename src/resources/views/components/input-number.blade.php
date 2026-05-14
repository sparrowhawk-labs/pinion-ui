@props([
    'name' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'value' => 0,
    'min' => null,
    'max' => null,
    'step' => 1,
    'size' => 'md',
    'width' => null,
    'digits' => null,
    'disabled' => false,
    'readonly' => false,
    'required' => false,
])

@php
    use SparrowhawkLabs\PinionUi\Compose\InputNumberComposer;

    $inputId  = $attributes->get('id', ($name ? $name . '_' : 'inputnum_') . uniqid());
    $hintText = $error ?: $hint;

    $minJs  = $min !== null ? (string) $min : 'null';
    $maxJs  = $max !== null ? (string) $max : 'null';
    $stepJs = (string) $step;
    $disabledJs = $disabled ? 'true' : 'false';

    // Digit width: longest of max / min / current value (min 2). +1 for cursor padding.
    $widths = array_filter([
        $max   !== null ? strlen((string) $max)   : null,
        $min   !== null ? strlen((string) $min)   : null,
        $value !== null ? strlen((string) $value) : null,
    ], fn ($v) => $v !== null);
    $digitCount = $digits ?? (empty($widths) ? 3 : max($widths));
    $digitCount = max(2, (int) $digitCount);
    $inputSize  = $digitCount + 1;

    // When the user passes an explicit `width` (e.g. "w-full", "w-64"), the
    // wrapper stretches and the input must `flex-1` to fill the new space.
    // Default (null) uses `inline-flex flex-col` so the wrapper reliably
    // shrinks to the inner row's natural width — `w-fit` alone gets pushed
    // wider by the block-level <label> child on some layouts.
    $widthClass    = $width ?? 'inline-flex flex-col';
    $stretchInput  = $width !== null;

    $c = InputNumberComposer::compose([
        'size'  => $size,
        'error' => $error,
    ]);
@endphp

<div class="{{ $widthClass }}"
    x-data="{
        v: '{{ $value }}',
        min: {{ $minJs }},
        max: {{ $maxJs }},
        step: {{ $stepJs }},
        disabled: {{ $disabledJs }},
        clamp(n) {
            if (this.min !== null && n < this.min) n = this.min;
            if (this.max !== null && n > this.max) n = this.max;
            return n;
        },
        inc() {
            if (this.disabled) return;
            const n = parseFloat(this.v);
            const next = isNaN(n) ? (this.min ?? 0) : n + this.step;
            this.v = String(this.clamp(next));
        },
        dec() {
            if (this.disabled) return;
            const n = parseFloat(this.v);
            const next = isNaN(n) ? (this.min ?? 0) : n - this.step;
            this.v = String(this.clamp(next));
        },
        atMin() { return this.min !== null && parseFloat(this.v) <= this.min; },
        atMax() { return this.max !== null && parseFloat(this.v) >= this.max; },
    }">
    @if($label)
        <label for="{{ $inputId }}" class="block mb-1.5 text-[length:var(--text-field-sm)] font-medium {{ $c['labelColor'] }}">
            {{ $label }}@if($required)<span class="text-error ml-0.5">*</span>@endif
        </label>
    @endif

    <div class="{{ $c['wrapper'] }}">
        <button type="button" class="{{ $c['button'] }}"
                aria-label="Decrease"
                tabindex="-1"
                x-on:click="dec()"
                x-bind:disabled="disabled || atMin()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                <path d="M5 12h14" />
            </svg>
        </button>

        <input
            type="number"
            id="{{ $inputId }}"
            x-model="v"
            x-bind:disabled="disabled"
            @if(!$stretchInput) size="{{ $inputSize }}" @endif
            @if($name) name="{{ $name }}" @endif
            @if($min !== null) min="{{ $min }}" @endif
            @if($max !== null) max="{{ $max }}" @endif
            step="{{ $step }}"
            inputmode="numeric"
            {{ $attributes->whereStartsWith('wire:') }}
            {{ $attributes->whereDoesntStartWith('wire:')->merge(['class' => $c['input'] . ($stretchInput ? ' flex-1' : '')]) }}
            @if($required) required @endif
            @if($readonly) readonly @endif
        />

        <button type="button" class="{{ $c['button'] }}"
                aria-label="Increase"
                tabindex="-1"
                x-on:click="inc()"
                x-bind:disabled="disabled || atMax()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                <path d="M5 12h14" />
                <path d="M12 5v14" />
            </svg>
        </button>
    </div>

    @if($hintText)
        <p class="mt-1.5 text-[length:var(--text-field-sm)] {{ $c['hintColor'] }}">{{ $hintText }}</p>
    @endif
</div>
