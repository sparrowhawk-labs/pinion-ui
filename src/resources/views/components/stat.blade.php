@props([
    'label' => '',
    'value' => null,
    'desc' => null,
    'valueColor' => null,
    'trend' => null,
    'trendValue' => null,
    'wrapped' => true,
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\StatComposer::compose([
        'valueColor' => $valueColor,
        'trend' => $trend,
        'wrapped' => $wrapped,
    ]);
    $arrow = \SparrowhawkLabs\PinionUi\Compose\StatComposer::arrowChar($trend);
    $hasFigure = isset($slot) && trim((string) $slot) !== '';
    $hasDesc = $desc !== null || $trend !== null || $trendValue !== null;
@endphp

@if($wrapped)
<div {{ $attributes->class([$c['root']]) }}>
    <div class="{{ $c['inner'] }}">
        @if($hasFigure)
            <div class="{{ $c['figure'] }}">{{ $slot }}</div>
        @endif
        <div class="{{ $c['title'] }}">{{ $label }}</div>
        @if($value !== null)
            <div class="{{ $c['value'] }}">{{ $value }}</div>
        @endif
        @if($hasDesc)
            <div class="{{ $c['desc'] }}">
                @if($arrow !== '')<span class="mr-1">{{ $arrow }}</span>@endif
                @if($desc !== null){{ $desc }}@endif
                @if($trendValue !== null)@if($desc !== null) @endif<span class="font-medium">{{ $trendValue }}</span>@endif
            </div>
        @endif
    </div>
</div>
@else
<div {{ $attributes->class([$c['inner']]) }}>
    @if($hasFigure)
        <div class="{{ $c['figure'] }}">{{ $slot }}</div>
    @endif
    <div class="{{ $c['title'] }}">{{ $label }}</div>
    @if($value !== null)
        <div class="{{ $c['value'] }}">{{ $value }}</div>
    @endif
    @if($hasDesc)
        <div class="{{ $c['desc'] }}">
            @if($arrow !== '')<span class="mr-1">{{ $arrow }}</span>@endif
            @if($desc !== null){{ $desc }}@endif
            @if($trendValue !== null)@if($desc !== null) @endif<span class="font-medium">{{ $trendValue }}</span>@endif
        </div>
    @endif
</div>
@endif
