@props([
    'name' => null,
    'value' => null,
    'label' => null,
    'description' => null,
    'color' => 'primary',
    'appearance' => 'soft',
    'size' => 'md',
    'error' => null,
    'indeterminate' => false,
    'disabled' => false,
    'checked' => false,
])

@php
    use SparrowhawkLabs\PinionUi\Compose\CheckboxComposer;

    $checkboxId = $attributes->get('id', ($name ? $name . '_' : 'checkbox_') . uniqid());

    $c = CheckboxComposer::compose([
        'color' => $color,
        'appearance' => $appearance,
        'size' => $size,
        'error' => $error,
        'disabled' => $disabled,
    ]);
@endphp

<label for="{{ $checkboxId }}" class="{{ $c['wrapper'] }}">
    <span class="{{ $c['row'] }}">
        <input
            type="checkbox"
            id="{{ $checkboxId }}"
            class="{{ $c['input'] }}"
            @if($name) name="{{ $name }}" @endif
            @if($value !== null) value="{{ $value }}" @endif
            @if($checked) checked @endif
            @if($disabled) disabled @endif
            @if($indeterminate) x-data x-init="$el.indeterminate = true" @endif
            {{ $attributes->whereStartsWith('wire:') }}
            {{ $attributes->whereDoesntStartWith('wire:')->whereDoesntStartWith('class') }}
        />

        <span class="{{ $c['visualBox'] }}">
            <svg class="{{ $c['checkmark'] }}" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <svg class="{{ $c['indeterminate'] }}" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" d="M6 12h12"/>
            </svg>
        </span>

        @if($label || (isset($slot) && trim($slot) !== ''))
            <span class="{{ $c['label'] }}">{{ $label ?? $slot }}</span>
        @endif
    </span>

    @if($description)
        <p class="{{ $c['description'] }}">{{ $description }}</p>
    @endif
</label>
