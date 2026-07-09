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
        $percentage = 0;
        $labelText = '';
    }
@endphp

{{--
    Div-based track/fill bar, not the native <progress> element — the
    ::-webkit-progress-bar / ::-webkit-progress-value / ::-moz-progress-bar
    pseudo-elements only take daisyUI's CSS, which is exactly what this
    migration removes (see CLAUDE.md invariant 6). Accessibility semantics
    are preserved via the standard WAI-ARIA `progressbar` pattern
    (role + aria-valuenow/min/max on the wrapper), so no hidden native
    <progress> announcer is needed.
--}}
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

    <div class="{{ $c['track'] }}">
        <div
            class="{{ $c['fill'] }}"
            @if(! $isIndeterminate)
                style="width: {{ $percentage }}%"
            @endif
        ></div>
    </div>
</div>
