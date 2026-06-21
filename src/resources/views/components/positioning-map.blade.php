@props([
    'points' => [],      // each: ['x'=>0..100,'y'=>0..100,'label'=>'A','sub'=>'opt',
                         //        'icon'=>'star','iconLibrary'=>'solar','image'=>'/logo.svg','imageShape'=>'circle'|'rounded']
                         // (x: 0=left,100=right; y: 0=top,100=bottom). label/icon/image all optional.
    'xLabels' => [],     // [leftCaption, rightCaption]
    'yLabels' => [],     // [topCaption, bottomCaption]
    'quadrants' => [],   // optional faint bg labels [topLeft, topRight, bottomLeft, bottomRight]
    'active' => null,    // static highlight: matches a point's label
    'xActive' => null,   // live highlight: an Alpine expression (e.g. "tune") compared to each label
    'size' => 'md',      // sm | md | lg → plot height
    'grid' => true,
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\PositioningMapComposer::compose([
        'size' => $size,
        'grid' => $grid,
    ]);
    $gridStyle = \SparrowhawkLabs\PinionUi\Compose\PositioningMapComposer::gridStyle();
@endphp

<div {{ $attributes->class([$c['root']]) }}>
    <div class="{{ $c['grid'] }}" style="{{ $gridStyle }}"></div>

    {{-- Faint quadrant context, set INSIDE the field and inset from the edges so
         it never collides with the gutter/bottom axis captions (axis = primary
         frame label, quadrant = secondary in-field context). --}}
    @if(!empty(array_filter($quadrants)))
        @if(($quadrants[0] ?? '') !== '')<span class="{{ $c['quadrant'] }} left-8 top-3">{{ $quadrants[0] }}</span>@endif
        @if(($quadrants[1] ?? '') !== '')<span class="{{ $c['quadrant'] }} right-3 top-3 text-right">{{ $quadrants[1] }}</span>@endif
        @if(($quadrants[2] ?? '') !== '')<span class="{{ $c['quadrant'] }} left-8 bottom-9">{{ $quadrants[2] }}</span>@endif
        @if(($quadrants[3] ?? '') !== '')<span class="{{ $c['quadrant'] }} right-3 bottom-9 text-right">{{ $quadrants[3] }}</span>@endif
    @endif

    {{-- y-axis captions: vertical, on the left gutter, clear of the point field --}}
    @if(($yLabels[0] ?? '') !== '')<span class="{{ $c['axis'] }} left-2.5 top-3 [writing-mode:vertical-rl] rotate-180">{{ $yLabels[0] }}</span>@endif
    @if(($yLabels[1] ?? '') !== '')<span class="{{ $c['axis'] }} left-2.5 bottom-12 [writing-mode:vertical-rl] rotate-180">{{ $yLabels[1] }}</span>@endif
    {{-- x-axis captions: a short tick rule + tracked label, no glyph arrows --}}
    @if(($xLabels[0] ?? '') !== '')<span class="{{ $c['axis'] }} left-3 bottom-3 flex items-center gap-1.5"><span class="inline-block h-px w-3 bg-base-content/30"></span>{{ $xLabels[0] }}</span>@endif
    @if(($xLabels[1] ?? '') !== '')<span class="{{ $c['axis'] }} right-3 bottom-3 flex items-center gap-1.5">{{ $xLabels[1] }}<span class="inline-block h-px w-3 bg-base-content/30"></span></span>@endif

    <div class="{{ $c['plot'] }}">
        @foreach($points as $p)
            @php
                $x = $p['x'] ?? 0;
                $y = $p['y'] ?? 0;
                $label = $p['label'] ?? '';
                $sub = $p['sub'] ?? ($p['sublabel'] ?? null);
                $icon = $p['icon'] ?? null;
                $iconLib = $p['iconLibrary'] ?? ($p['library'] ?? null);
                $image = $p['image'] ?? null;
                $imgRound = (($p['imageShape'] ?? 'rounded') === 'circle') ? 'rounded-full' : 'rounded-[var(--radius-field)]';
                $isActive = $active !== null && $active === $label;
            @endphp
            <div @class([$c['point'] => !($xActive || $isActive), $c['pointActive'] => !$xActive && $isActive])
                 @if($xActive) x-bind:class="({{ $xActive }}) === @js($label) ? '{{ $c['pointActive'] }}' : '{{ $c['point'] }}'" @endif
                 style="left:{{ $x }}%;top:{{ $y }}%" data-pm-point="{{ $label }}">
                {{-- chip + stem grow upward from the node, anchored above it.
                     marker = image thumbnail / icon+label / label — whichever the datum provides. --}}
                <span class="{{ $c['flag'] }}">
                    @if($image)
                        {{-- image / logo thumbnail (single class attr; live state via x-bind) --}}
                        <span class="{{ $imgRound }} @unless($xActive){{ $isActive ? $c['markerImageActive'] : $c['markerImage'] }}@endunless"
                              @if($xActive) x-bind:class="({{ $xActive }}) === @js($label) ? '{{ $c['markerImageActive'] }}' : '{{ $c['markerImage'] }}'" @endif>
                            <img src="{{ $image }}" alt="{{ $label }}" class="{{ $c['imageEl'] }}">
                        </span>
                        @if($label !== '')<span class="{{ $isActive ? $c['sublabelActive'] : $c['sublabel'] }}">{{ $label }}</span>@endif
                    @else
                        {{-- text chip, optionally with a leading icon --}}
                        <span @if($xActive) x-bind:class="({{ $xActive }}) === @js($label) ? '{{ $c['markerActive'] }}' : '{{ $c['marker'] }}'" @else class="{{ $isActive ? $c['markerActive'] : $c['marker'] }}" @endif>
                            @if($icon)
                                <x-i :type="$icon" :library="$iconLib" :class="$c['icon']" />
                            @endif
                            @if($label !== '')<span>{{ $label }}</span>@endif
                        </span>
                        @if($sub)
                            <span @if($xActive) x-bind:class="({{ $xActive }}) === @js($label) ? '{{ $c['sublabelActive'] }}' : '{{ $c['sublabel'] }}'" @else class="{{ $isActive ? $c['sublabelActive'] : $c['sublabel'] }}" @endif>{{ $sub }}</span>
                        @endif
                    @endif
                    <span class="{{ $c['stem'] }}"></span>
                </span>
                {{-- node = the precise datum, sitting exactly on (x,y) --}}
                @if($xActive)
                    <span class="{{ $c['ping'] }}" x-cloak x-show="({{ $xActive }}) === @js($label)"></span>
                    <span x-bind:class="({{ $xActive }}) === @js($label) ? '{{ $c['nodeActive'] }}' : '{{ $c['node'] }}'"></span>
                @else
                    @if($isActive)<span class="{{ $c['ping'] }}"></span>@endif
                    <span class="{{ $isActive ? $c['nodeActive'] : $c['node'] }}"></span>
                @endif
            </div>
        @endforeach
    </div>
</div>
