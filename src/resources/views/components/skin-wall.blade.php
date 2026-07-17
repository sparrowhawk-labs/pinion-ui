@props([
    'combos'     => null,   // array of [tune, theme] pairs cycled across the cards; null → a curated default set
    'angle'      => -12,    // diagonal tilt in degrees
    'speed'      => 36,     // base seconds for one loop (each column varies slightly around it)
    'columns'    => 7,      // number of vertical columns
    'per'        => 2,      // distinct combos per column
    'height'     => 560,    // stage height in px
    'cardWidth'  => 320,    // card width in px
    'cardHeight' => 480,    // card height in px
    'scale'      => 0.3333, // content scale (the slot is authored at cardWidth / scale, then scaled down)
    'gap'        => 26,     // px between stacked cards
    'cover'      => 1.14,   // wall scale so the rotated wall still covers the stage
])

@php
    // A "skin wall": the SAME slot markup, rendered across many tune × theme skins,
    // as a full-width diagonal marquee. Decorative (aria-hidden) — repeated markup
    // means duplicate ids, so it is not part of the accessibility/tab order.
    // Curated tune × theme pairs from the v0.6.0 original lineup (daisyUI's
    // built-in themes no longer exist in the build — see theme.css).
    $combos = $combos ?: [
        ['default','pinion'], ['brutal','mood-monokai-dark'], ['editorial','atelier'], ['tech','devtool-dark'],
        ['soft','kids'], ['pixel','mood-pop'], ['corporate','mood-bigblue'], ['luxury','atelier-dark'],
        ['minimal','mono'], ['sharp','mono-dark'], ['draft','pinion'], ['tech','mood-synthwave-dark'],
        ['soft','mood-vapor'], ['editorial','pinion-dark'], ['brutal','mood-neotokyo-dark'], ['corporate','finance'],
        ['luxury','legal-dark'], ['minimal','wellness'], ['sharp','payments-dark'], ['pixel','mood-pop-dark'],
        ['default','travel'], ['editorial','media-dark'], ['soft','people'], ['tech','pinion-dark'],
    ];
    $REPEAT  = 3;                              // repeat the set 3× so the centred window never runs off the track ends
    $setH    = $per * ($cardHeight + $gap);    // one set's height in px = the exact seamless scroll distance
    $designW = round($cardWidth / max($scale, 0.0001));  // width the slot is authored/rendered at
@endphp

<div {{ $attributes->class('sw') }} aria-hidden="true"
     style="--sw-h:{{ $height }}px; --sw-card-w:{{ $cardWidth }}px; --sw-card-h:{{ $cardHeight }}px; --sw-gap:{{ $gap }}px; --sw-angle:{{ $angle }}deg; --sw-cover:{{ $cover }}; --sw-design-w:{{ $designW }}px; --sw-scale:{{ $scale }};">
    <div class="sw__wall">
        @for ($c = 0; $c < $columns; $c++)
            @php
                $dir  = $c % 2 === 0 ? 'up' : 'down';
                $dur  = max(12, $speed + ((($c * 5) % 18) - 8));   // gentle per-column variation
                $cards = [];
                for ($k = 0; $k < $per; $k++) { $cards[] = $combos[($c * $per + $k) % count($combos)]; }
                $loop = [];
                for ($r = 0; $r < $REPEAT; $r++) { $loop = array_merge($loop, $cards); }
            @endphp
            <div class="sw__col">
                <div class="sw__track sw__track--{{ $dir }}" style="--sw-set:{{ $setH }}px; animation-duration:{{ $dur }}s;">
                    @foreach ($loop as [$tune, $theme])
                        <div class="sw__card" data-tune="{{ $tune }}" data-theme="{{ $theme }}" style="background:var(--color-base-100);">
                            <div class="sw__screen">{{ $slot }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endfor
    </div>
</div>

@once
<style>
    /* pinion-ui skin-wall — full-width diagonal marquee of skin specimens.
       Seam-free: cards use margin-bottom (no flex gap) and the set is repeated
       3×, with the track scrolled by EXACTLY one set (--sw-set) so the centred
       window never sweeps past the track ends. */
    .sw { position: relative; width: 100%; height: var(--sw-h, 560px); overflow: hidden;
        background: var(--color-base-200);
        border-block: 1px solid color-mix(in oklab, var(--color-base-content) 8%, transparent);
        -webkit-mask-image: linear-gradient(to right, transparent, black 7%, black 93%, transparent), linear-gradient(to bottom, transparent, black 16%, black 84%, transparent);
        -webkit-mask-composite: source-in;
        mask-image: linear-gradient(to right, transparent, black 7%, black 93%, transparent), linear-gradient(to bottom, transparent, black 16%, black 84%, transparent);
        mask-composite: intersect; }
    .sw__wall { position: absolute; top: 50%; left: 50%; display: flex; gap: calc(var(--sw-gap, 26px)); transform: translate(-50%, -50%) rotate(var(--sw-angle, -12deg)) scale(var(--sw-cover, 1.14)); }
    .sw__col { width: var(--sw-card-w, 320px); flex: none; }
    .sw__track { display: flex; flex-direction: column; will-change: transform; }
    .sw__track--up { animation: sw-up linear infinite; }
    .sw__track--down { animation: sw-down linear infinite; }
    @keyframes sw-up   { from { transform: translateY(0); }                              to   { transform: translateY(calc(-1 * var(--sw-set))); } }
    @keyframes sw-down { from { transform: translateY(calc(-1 * var(--sw-set))); }       to   { transform: translateY(0); } }
    @media (prefers-reduced-motion: reduce) { .sw__track--up, .sw__track--down { animation: none; } }
    .sw__card { width: var(--sw-card-w, 320px); height: var(--sw-card-h, 480px); margin-bottom: var(--sw-gap, 26px); overflow: hidden; position: relative; border-radius: 16px;
        border: 1px solid color-mix(in oklab, var(--color-base-content) 10%, transparent);
        box-shadow: 0 26px 50px -28px color-mix(in oklab, var(--color-base-content) 55%, transparent); }
    .sw__screen { position: absolute; top: 0; left: 0; width: var(--sw-design-w, 960px); transform: scale(var(--sw-scale, 0.3333)); transform-origin: top left; }
</style>
@endonce
