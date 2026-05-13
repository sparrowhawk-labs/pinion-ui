@props([
    'shape' => 'rect',
    'width' => null,
    'height' => null,
    'lines' => 1,
    'radius' => 'default',
    'animated' => true,
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\SkeletonComposer::compose([
        'shape' => $shape,
        'width' => $width,
        'height' => $height,
        'lines' => (int) $lines,
        'radius' => $radius,
        'animated' => $animated,
    ]);
    $isMultiline = $shape === 'text' && (int) $lines > 1;
@endphp

@if($isMultiline)
    <div {{ $attributes->class([$c['root']]) }} aria-hidden="true">
        @for($i = 0; $i < (int) $lines; $i++)
            <div class="{{ $i === (int) $lines - 1 ? $c['itemLast'] : $c['item'] }}"></div>
        @endfor
    </div>
@else
    <div {{ $attributes->class([$c['root']]) }} aria-hidden="true"></div>
@endif
