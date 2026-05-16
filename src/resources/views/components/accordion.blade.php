@props([
    'multiple' => false,
    'size' => 'md',
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\AccordionComposer::compose([
        'size' => $size,
    ]);
@endphp

<div
    x-data="{ open: {{ $multiple ? '[]' : 'null' }} }"
    {{ $attributes->merge(['class' => $c['root']]) }}
>
    {{ $slot }}
</div>
