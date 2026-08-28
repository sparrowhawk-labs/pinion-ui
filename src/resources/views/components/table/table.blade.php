@props([
    'size' => 'md',
    'card' => true,
    'hover' => true,
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\TableComposer::compose([
        'size' => $size,
        'card' => $card,
        'hover' => $hover,
    ]);
@endphp

<div {{ $attributes->class([$c['shell']]) }}>
    <table class="{{ $c['table'] }}">
        @isset($head)
            <thead>
                <tr class="{{ $c['headRow'] }}">
                    {{ $head }}
                </tr>
            </thead>
        @endisset

        <tbody class="{{ $c['tbody'] }}">
            {{ $slot }}
        </tbody>
    </table>
</div>
