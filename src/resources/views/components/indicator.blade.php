@props([
    'position' => 'top-end',
    'dot' => false,
    'color' => 'error',
    'appearance' => 'solid',
])

@php
    // Perfect-circle detection lives here (render time), not in the Composer:
    // the Composer only ever sees props, never slot markup (purity contract
    // in CLAUDE.md). We measure the rendered `badge` slot — strip tags so an
    // icon-only slot (no visible text) also counts as "short" — and hand the
    // Composer a plain boolean. mb_strlen so multibyte glyphs count as 1.
    $badgeText = isset($badge) ? trim(strip_tags((string) $badge)) : '';
    $circle = ! $dot && mb_strlen($badgeText) <= 1;

    $c = \SparrowhawkLabs\PinionUi\Compose\IndicatorComposer::compose([
        'position' => $position,
        'dot' => $dot,
        'color' => $color,
        'appearance' => $appearance,
        'circle' => $circle,
    ]);
@endphp

<div {{ $attributes->class([$c['root']]) }}>
    <span class="{{ $c['item'] }}">@if(!$dot){{ $badge ?? '' }}@endif</span>
    {{ $slot }}
</div>
