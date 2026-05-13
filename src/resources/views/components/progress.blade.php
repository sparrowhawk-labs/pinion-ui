@props([
    'value' => null,
    'max' => 100,
    'color' => null,
    'size' => 'md',
    'showLabel' => false,
    'labelFormat' => 'percent',
])

@php
    $isIndeterminate = $value === null;
    $intMax = max(1, (int) $max);
    $intValue = $isIndeterminate ? null : max(0, min($intMax, (int) $value));

    $c = \SparrowhawkLabs\PinionUi\Compose\ProgressComposer::compose([
        'value' => $intValue,
        'max' => $intMax,
        'color' => $color,
        'size' => $size,
    ]);

    if (! $isIndeterminate) {
        $percentage = (int) round(($intValue / $intMax) * 100);
        $labelText = $labelFormat === 'fraction'
            ? "{$intValue} / {$intMax}"
            : "{$percentage}%";
    } else {
        $labelText = '';
    }
@endphp

<div
    {{ $attributes->class([$c['root']]) }}
    role="progressbar"
    @if(! $isIndeterminate)
        aria-valuenow="{{ $intValue }}"
    @endif
    aria-valuemin="0"
    aria-valuemax="{{ $intMax }}"
>
    @if($showLabel && ! $isIndeterminate)
        <div class="{{ $c['label'] }}">{{ $labelText }}</div>
    @endif

    @if($isIndeterminate)
        <progress class="{{ $c['bar'] }}" max="{{ $intMax }}"></progress>
    @else
        <progress class="{{ $c['bar'] }}" value="{{ $intValue }}" max="{{ $intMax }}"></progress>
    @endif
</div>
