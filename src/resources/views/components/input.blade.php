@props([
    'type' => 'text',
    'name' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'cornerHint' => null,
    'color' => 'neutral',
    'appearance' => 'outline',
    'size' => 'md',
    'iconLeft' => null,
    'iconRight' => null,
    'prefix' => null,
    'suffix' => null,
    'floating' => false,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
])

@php
    use SparrowhawkLabs\PinionUi\Compose\InputComposer;

    $inputId  = $attributes->get('id', ($name ? $name . '_' : 'input_') . uniqid());
    $hintText = $error ?: $hint;

    $c = InputComposer::compose([
        'color'        => $color,
        'appearance'   => $appearance,
        'size'         => $size,
        'error'        => $error,
        'floating'     => $floating,
        'hasLabel'     => (bool) $label,
        'hasIconLeft'  => (bool) $iconLeft,
        'hasIconRight' => (bool) $iconRight,
        'disabled'     => $disabled,
    ]);
@endphp

<div class="w-full">
    @if($label && !$floating)
        <div class="flex items-baseline justify-between mb-1.5">
            <label for="{{ $inputId }}" class="block text-[length:var(--text-field-sm)] font-medium {{ $c['labelColor'] }}">
                {{ $label }}
                @if($required)<span class="text-error ml-0.5">*</span>@endif
            </label>
            @if($cornerHint)
                <span class="text-[length:var(--text-field-xs)] text-base-content/50">{{ $cornerHint }}</span>
            @endif
        </div>
    @endif

    <div class="{{ $c['wrapper'] }}">
        @if($prefix)
            <span class="{{ $c['addonBase'] }} border-r border-base-300">{{ $prefix }}</span>
        @endif

        <div class="relative flex-1 min-w-0 flex items-stretch">
            @if($iconLeft)
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-base-content/40 pointer-events-none">
                    <x-i :type="$iconLeft" variant="linear" class="w-4 h-4" />
                </span>
            @endif

            <input
                type="{{ $type }}"
                id="{{ $inputId }}"
                @if($floating && $label) placeholder=" " @endif
                @if($name) name="{{ $name }}" @endif
                {{ $attributes->whereStartsWith('wire:') }}
                {{ $attributes->whereDoesntStartWith('wire:')->merge(['class' => $c['inputClass']]) }}
                @if($required) required @endif
                @if($disabled) disabled @endif
                @if($readonly) readonly @endif
            />

            @if($floating && $label)
                <label for="{{ $inputId }}" class="{{ $c['floatingLabel'] }}">
                    {{ $label }}
                    @if($required)<span class="text-error ml-0.5">*</span>@endif
                </label>
            @endif

            @if($iconRight)
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-base-content/40 pointer-events-none">
                    <x-i :type="$iconRight" variant="linear" class="w-4 h-4" />
                </span>
            @endif
        </div>

        @if($suffix)
            <span class="{{ $c['addonBase'] }} border-l border-base-300">{{ $suffix }}</span>
        @endif

        @if(isset($append))
            <div class="shrink-0 flex items-stretch">
                {{ $append }}
            </div>
        @endif
    </div>

    @if($hintText)
        <p class="mt-1.5 text-[length:var(--text-field-sm)] {{ $c['hintColor'] }}">{{ $hintText }}</p>
    @endif
</div>
