@props([
    'size' => 'md',
    'appearance' => 'default',
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\KbdComposer::compose([
        'size' => $size,
        'appearance' => $appearance,
    ]);
@endphp

<kbd {{ $attributes->class([$c['root']]) }}>{{ $slot }}</kbd>
