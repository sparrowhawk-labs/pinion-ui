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
    'stateLabel' => false,
])

@php
    use SparrowhawkLabs\PinionUi\Compose\ToggleComposer;

    $toggleId = $attributes->get('id', ($name ? $name . '_' : 'toggle_') . uniqid());

    $c = ToggleComposer::compose([
        'color' => $color,
        'appearance' => $appearance,
        'size' => $size,
        'error' => $error,
        'disabled' => $disabled,
        'stateLabel' => $stateLabel,
    ]);
@endphp

<label for="{{ $toggleId }}" class="{{ $c['wrapper'] }}">
    <span class="{{ $c['row'] }}">
        <input
            type="checkbox"
            role="switch"
            id="{{ $toggleId }}"
            class="{{ $c['input'] }}"
            @if($name) name="{{ $name }}" @endif
            @if($value !== null) value="{{ $value }}" @endif
            @if($checked) checked @endif
            @if($disabled) disabled @endif
            {{ $attributes->whereStartsWith('wire:') }}
            {{ $attributes->whereDoesntStartWith('wire:')->whereDoesntStartWith('class') }}
        />

        <span class="{{ $c['track'] }}" aria-hidden="true">
            @if($c['stateOn'])
                <span class="{{ $c['stateOn'] }}">ON</span>
                <span class="{{ $c['stateOff'] }}">OFF</span>
            @endif
            <span class="{{ $c['thumb'] }}"></span>
        </span>

        @if($label || (isset($slot) && trim($slot) !== ''))
            <span class="{{ $c['label'] }}">{{ $label ?? $slot }}</span>
        @endif
    </span>

    @if($description)
        <p class="{{ $c['description'] }}">{{ $description }}</p>
    @endif
</label>
