@props([
    'items' => [],
    'orientation' => 'horizontal',
    'variant' => 'numbered',
])

@php
    use SparrowhawkLabs\PinionUi\Compose\StepperComposer;

    $c = StepperComposer::compose([
        'orientation' => $orientation,
        'variant' => $variant,
    ]);
    $itemList = is_array($items) ? array_values($items) : [];
    $count    = count($itemList);
@endphp

<ol class="{{ $c['root'] }}">
    @foreach($itemList as $i => $item)
        @php
            $label   = $item['label']  ?? '';
            $desc    = $item['desc']   ?? null;
            $state   = $item['state']  ?? 'upcoming';
            $isLast  = $i === $count - 1;
            $circleColor    = StepperComposer::pick($c['stateColors'], $state);
            $connectorColor = StepperComposer::pick($c['stateConnectors'], $state);
        @endphp

        <li class="{{ $c['item'] }}">
            <div class="{{ $c['circle'] }} {{ $circleColor }}">
                @if($c['variant'] !== 'dotted')
                    @if($state === 'done')
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.5 7.6a1 1 0 0 1-1.42.005l-3.5-3.5a1 1 0 0 1 1.414-1.414l2.79 2.79 6.794-6.886a1 1 0 0 1 1.416-.009Z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        {{ $i + 1 }}
                    @endif
                @endif
            </div>

            @if($c['orientation'] === 'horizontal')
                <div class="mt-2 text-center">
                    <div class="{{ $c['label'] }}">{{ $label }}</div>
                    @if($desc !== null)<div class="{{ $c['desc'] }}">{{ $desc }}</div>@endif
                </div>
            @else
                <div class="flex-1 pt-1">
                    <div class="{{ $c['label'] }}">{{ $label }}</div>
                    @if($desc !== null)<div class="{{ $c['desc'] }}">{{ $desc }}</div>@endif
                </div>
            @endif
        </li>

        @if(!$isLast)
            <div class="{{ $c['connector'] }} {{ $connectorColor }}"></div>
        @endif
    @endforeach
</ol>
