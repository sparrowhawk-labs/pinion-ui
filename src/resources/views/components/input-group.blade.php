@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'size' => 'md',
])

@php
    use SparrowhawkLabs\PinionUi\Compose\InputGroupComposer;

    $c = InputGroupComposer::compose([
        'size'  => $size,
        'error' => $error,
    ]);
    $hintText = $error ?: $hint;
@endphp

<div class="w-full">
    @if($label)
        <label class="block mb-1.5 text-[length:var(--text-field-sm)] font-medium {{ $c['labelColor'] }}">
            {{ $label }}
        </label>
    @endif

    <div {{ $attributes->class([$c['wrapper']]) }}>
        {{ $slot }}
    </div>

    @if($hintText)
        <p class="mt-1.5 text-[length:var(--text-field-sm)] {{ $c['hintColor'] }}">{{ $hintText }}</p>
    @endif
</div>
