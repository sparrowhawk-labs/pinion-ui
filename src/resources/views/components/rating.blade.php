@props([
    'name' => null,
    'value' => 0,
    'max' => 5,
    'size' => 'md',
    'color' => 'warning',
    'half' => false,
    'readonly' => false,
    'shape' => 'star',
])

@php
    use SparrowhawkLabs\PinionUi\Compose\RatingComposer;

    if (! $name) {
        throw new \InvalidArgumentException('<x-rating> requires a `name` prop (radio group identifier).');
    }

    $max = max(1, (int) $max);
    $value = (float) $value;
    $half = (bool) $half;
    $readonly = (bool) $readonly;

    $c = RatingComposer::compose([
        'size' => $size,
        'color' => $color,
        'half' => $half,
    ]);

    // Shape geometry (path/circle data + viewBox) is markup data, not classes —
    // kept out of compose()'s dict per the Compose purity invariant. This is what
    // replaces daisyUI's `mask`/`mask-star`/`mask-heart`/`mask-circle` classes:
    // an actual inline <svg> per star instead of a CSS mask-image.
    $geo = RatingComposer::shapeGeometry($shape);
    $viewBox = RatingComposer::viewBox($geo);
    [$half1ViewBox, $half2ViewBox] = RatingComposer::halfViewBoxes($geo);

    if ($half) {
        // Each star is split into 2 radios (half-1 then half-2).
        // Step 1 (half-1) = 0.5, step 2 (half-2) = 1.0, step 3 = 1.5, ...
        // checked step = round(value * 2)
        $checkedStep = (int) round($value * 2);
    } else {
        $checkedStep = (int) round($value);
    }

    // Livewire: detect wire:model (pure Blade — works without Livewire installed).
    // When present we bind natively: each radio carries a `value` + the forwarded
    // wire:model bag, and Livewire (not our server-rendered `checked`) drives the
    // selection. Gated so non-Livewire output stays byte-identical.
    $wireModel = $attributes->whereStartsWith('wire:model');
    $hasWire   = $wireModel->isNotEmpty();
@endphp

{{--
    daisyUI-free rating: flat `<input><svg>` siblings (no wrapper elements — the
    fill-toggle CSS in pinion-ui.css relies on `+`/`~` sibling combinators that
    only work across siblings sharing the same parent). The <input> is an
    invisible-but-real click target sized exactly to its <svg>'s footprint; the
    <svg> is pulled back on top of it with a matching negative left-margin
    (`-ml-*`) and `pointer-events:none`, so clicks pass through to the <input>
    beneath. See CSS comment "rating stars — daisyUI-free" in pinion-ui.css for
    the checked/highlight-preceding-stars mechanism.
--}}
<div {{ $attributes->whereDoesntStartWith('wire:model')->class([$c['root']]) }}>
    <input
        type="radio"
        name="{{ $name }}"
        class="{{ $c['hidden'] }}"
        @if($hasWire) value="0" {{ $wireModel }} @elseif($checkedStep === 0) checked @endif
        @if($readonly) disabled @endif
        aria-label="{{ pn_trans('rating.none', '評価なし') }}"
    />

    @if($half)
        @for($i = 1; $i <= $max; $i++)
            @php
                $step1 = ($i - 1) * 2 + 1; // half-1 = 0.5, 1.5, ...
                $step2 = ($i - 1) * 2 + 2; // half-2 = 1.0, 2.0, ...
            @endphp
            <input
                type="radio"
                name="{{ $name }}"
                class="{{ $c['inputHalf'] }}"
                @if($hasWire) value="{{ $i - 0.5 }}" {{ $wireModel }} @elseif($checkedStep === $step1) checked @endif
                @if($readonly) disabled @endif
                aria-label="{{ $i - 0.5 }} star"
            />
            <svg data-pn-star viewBox="{{ $half1ViewBox }}" class="{{ $c['starHalf'] }}" aria-hidden="true">
                @if($geo['tag'] === 'circle')
                    <circle cx="{{ $geo['cx'] }}" cy="{{ $geo['cy'] }}" r="{{ $geo['r'] }}" />
                @else
                    <path d="{{ $geo['path'] }}" fill-rule="{{ $geo['fillRule'] }}" />
                @endif
            </svg>
            <input
                type="radio"
                name="{{ $name }}"
                class="{{ $c['inputHalf'] }}"
                @if($hasWire) value="{{ $i }}" {{ $wireModel }} @elseif($checkedStep === $step2) checked @endif
                @if($readonly) disabled @endif
                aria-label="{{ $i }} star"
            />
            <svg data-pn-star viewBox="{{ $half2ViewBox }}" class="{{ $c['starHalf'] }}" aria-hidden="true">
                @if($geo['tag'] === 'circle')
                    <circle cx="{{ $geo['cx'] }}" cy="{{ $geo['cy'] }}" r="{{ $geo['r'] }}" />
                @else
                    <path d="{{ $geo['path'] }}" fill-rule="{{ $geo['fillRule'] }}" />
                @endif
            </svg>
        @endfor
    @else
        @for($i = 1; $i <= $max; $i++)
            <input
                type="radio"
                name="{{ $name }}"
                class="{{ $c['input'] }}"
                @if($hasWire) value="{{ $i }}" {{ $wireModel }} @elseif($checkedStep === $i) checked @endif
                @if($readonly) disabled @endif
                aria-label="{{ $i }} star"
            />
            <svg data-pn-star viewBox="{{ $viewBox }}" class="{{ $c['star'] }}" aria-hidden="true">
                @if($geo['tag'] === 'circle')
                    <circle cx="{{ $geo['cx'] }}" cy="{{ $geo['cy'] }}" r="{{ $geo['r'] }}" />
                @else
                    <path d="{{ $geo['path'] }}" fill-rule="{{ $geo['fillRule'] }}" />
                @endif
            </svg>
        @endfor
    @endif
</div>
