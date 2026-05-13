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
        'shape' => $shape,
        'half' => $half,
    ]);

    if ($half) {
        // Each star is split into 2 radios (half-1 then half-2).
        // Step 1 (mask-half-1) = 0.5, step 2 (mask-half-2) = 1.0, step 3 = 1.5, ...
        // checked step = round(value * 2)
        $checkedStep = (int) round($value * 2);
    } else {
        $checkedStep = (int) round($value);
    }
@endphp

<div {{ $attributes->class([$c['root']]) }}>
    {{-- Hidden "no rating" radio (value=0). Always present so users can clear. --}}
    <input
        type="radio"
        name="{{ $name }}"
        class="{{ $c['hidden'] }}"
        @if($checkedStep === 0) checked @endif
        @if($readonly) disabled @endif
        aria-label="評価なし"
    />

    @if($half)
        @for($i = 1; $i <= $max; $i++)
            @php
                $step1 = ($i - 1) * 2 + 1; // 1, 3, 5, ... (mask-half-1 = 0.5, 1.5, ...)
                $step2 = ($i - 1) * 2 + 2; // 2, 4, 6, ... (mask-half-2 = 1.0, 2.0, ...)
            @endphp
            <input
                type="radio"
                name="{{ $name }}"
                class="{{ $c['itemHalf1'] }}"
                @if($checkedStep === $step1) checked @endif
                @if($readonly) disabled @endif
                aria-label="{{ ($i - 0.5) }} star"
            />
            <input
                type="radio"
                name="{{ $name }}"
                class="{{ $c['itemHalf2'] }}"
                @if($checkedStep === $step2) checked @endif
                @if($readonly) disabled @endif
                aria-label="{{ $i }} star"
            />
        @endfor
    @else
        @for($i = 1; $i <= $max; $i++)
            <input
                type="radio"
                name="{{ $name }}"
                class="{{ $c['item'] }}"
                @if($checkedStep === $i) checked @endif
                @if($readonly) disabled @endif
                aria-label="{{ $i }} star"
            />
        @endfor
    @endif
</div>
