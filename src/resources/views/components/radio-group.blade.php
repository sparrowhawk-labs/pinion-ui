@props([
    'name' => null,
    'label' => null,
    'description' => null,
    'value' => null,
    'options' => null,
    'color' => 'primary',
    'appearance' => 'solid',
    'size' => 'md',
    'error' => null,
    'required' => false,
    'disabled' => false,
    'orientation' => 'vertical',
    'hint' => null,
])

@php
    $groupId = $attributes->get('id', ($name ? $name . '_group' : 'radio_group_') . uniqid());
    $orientationClass = $orientation === 'horizontal'
        ? 'flex flex-wrap gap-x-6 gap-y-2'
        : 'flex flex-col gap-2';
    $hintText = $error ?: $hint;
    $hintColor = $error ? 'text-error' : 'text-base-content/60';
    $labelColor = $error ? 'text-error' : 'text-base-content';
@endphp

<fieldset {{ $attributes->merge(['class' => 'block']) }}>
    @if($label)
        <legend id="{{ $groupId }}" class="block text-[length:var(--text-field-sm)] font-medium {{ $labelColor }} mb-1.5">
            {{ $label }}
            @if($required)<span class="text-error ml-0.5">*</span>@endif
        </legend>
    @endif

    @if($description)
        <p class="text-[length:var(--text-field-xs)] text-base-content/60 mb-2 leading-snug">{{ $description }}</p>
    @endif

    <div class="{{ $orientationClass }}" role="radiogroup" @if($label) aria-labelledby="{{ $groupId }}" @endif>
        @if(is_array($options))
            @foreach($options as $optValue => $optLabel)
                <x-radio
                    :name="$name"
                    :value="$optValue"
                    :label="$optLabel"
                    :color="$color"
                    :appearance="$appearance"
                    :size="$size"
                    :error="$error"
                    :disabled="$disabled"
                    :checked="$value !== null && (string) $value === (string) $optValue"
                />
            @endforeach
        @else
            {{ $slot }}
        @endif
    </div>

    @if($hintText)
        <p class="mt-1.5 text-[length:var(--text-field-sm)] {{ $hintColor }}">{{ $hintText }}</p>
    @endif
</fieldset>
