@aware([
    'size' => 'md',
])

@props([
    'numeric' => false,
    'action' => false,
    'muted' => false,
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

<td {{ $attributes->class([
    $c['td'],
    $alignClass,
    'tabular-nums' => $numeric,
    'text-base-content/60' => $muted,
]) }}>{{ $slot }}</td>
