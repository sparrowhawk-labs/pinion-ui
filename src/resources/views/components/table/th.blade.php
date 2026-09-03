@aware([
    'size' => 'md',
])

@props([
    'numeric' => false,
    'action' => false,
    'align' => null,
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\TableComposer::compose(['size' => $size]);

    $alignClass = match (true) {
        $align === 'center'          => 'text-center',
        $align === 'right'           => 'text-right',
        $align === 'left'            => 'text-left',
        (bool) $numeric, (bool) $action => 'text-right whitespace-nowrap',
        default                      => '',
    };
@endphp

<th {{ $attributes->class([$c['th'], $alignClass]) }}>{{ $slot }}</th>
