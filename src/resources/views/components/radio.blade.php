@props([
    'name' => null,
    'value' => null,
    'label' => null,
    'description' => null,
    'color' => 'primary',
    'appearance' => 'solid',
    'size' => 'md',
    'error' => null,
    'disabled' => false,
    'checked' => false,
])

@php
    use SparrowhawkLabs\PinionUi\Compose\RadioComposer;

    $radioId = $attributes->get('id', ($name ? $name . '_' . ($value ?? '') . '_' : 'radio_') . uniqid());

    $c = RadioComposer::compose([
        'color' => $color,
        'appearance' => $appearance,
        'size' => $size,
        'error' => $error,
        'disabled' => $disabled,
    ]);
@endphp

<label for="{{ $radioId }}" class="{{ $c['wrapper'] }}">
    <span class="{{ $c['row'] }}">
        <input
            type="radio"
            id="{{ $radioId }}"
            class="{{ $c['input'] }}"
            @if($name) name="{{ $name }}" @endif
            @if($value !== null) value="{{ $value }}" @endif
            @if($checked) checked @endif
            @if($disabled) disabled @endif
            {{ $attributes->whereStartsWith('wire:') }}
            {{ $attributes->whereDoesntStartWith('wire:')->whereDoesntStartWith('class') }}
        />

        <span class="{{ $c['visualBox'] }}">
            <span class="{{ $c['dot'] }}"></span>
        </span>

        @if($label || (isset($slot) && trim($slot) !== ''))
            <span class="{{ $c['label'] }}">{{ $label ?? $slot }}</span>
        @endif
    </span>

    @if($description)
        <p class="{{ $c['description'] }}">{{ $description }}</p>
    @endif
</label>
