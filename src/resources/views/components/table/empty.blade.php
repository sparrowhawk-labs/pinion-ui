@aware([
    'size' => 'md',
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\TableComposer::compose(['size' => $size]);
@endphp

{{-- colspan=999 is clamped by the browser to the table's real column count,
     so the placeholder spans every column without the caller passing one. --}}
<tr class="pn-table-empty">
    <td colspan="999" {{ $attributes->class([$c['empty']]) }}>{{ $slot }}</td>
</tr>
