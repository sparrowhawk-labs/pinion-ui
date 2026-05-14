@props([
    'items' => [],
    'orientation' => 'vertical',
    'compact' => false,
    'snap' => false,
    'appearance' => 'soft',
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\TimelineComposer::compose([
        'orientation' => $orientation,
        'compact' => $compact,
        'snap' => $snap,
        'appearance' => $appearance,
    ]);
    $itemList = is_array($items) ? array_values($items) : [];
    $count = count($itemList);
@endphp

<ul {{ $attributes->class([$c['root']]) }}>
    @foreach($itemList as $i => $item)
        @php
            $title = $item['title'] ?? '';
            $time = $item['time'] ?? null;
            $desc = $item['desc'] ?? null;
            $side = $item['side'] ?? 'start';
            $state = $item['state'] ?? null;
            $iconColor = \SparrowhawkLabs\PinionUi\Compose\TimelineComposer::pick($c['stateColors'], $state);
            $hrColor = \SparrowhawkLabs\PinionUi\Compose\TimelineComposer::pick($c['hrColors'], $state);
            // For alternating layout: side='end' swaps time/box positions.
            $timeOnEnd = $side === 'end';
        @endphp
        <li>
            @if($i > 0)
                <hr class="{{ $hrColor }}" />
            @endif

            @if($time !== null && !$timeOnEnd)
                <div class="timeline-start text-xs text-base-content/60">{{ $time }}</div>
            @endif

            <div class="{{ $c['middle'] }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 {{ $iconColor }}">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                </svg>
            </div>

            @if($timeOnEnd)
                <div class="timeline-end text-xs text-base-content/60">{{ $time }}</div>
                <div class="timeline-start {{ $c['box'] }}">
                    <div class="font-medium">{{ $title }}</div>
                    @if($desc !== null)
                        <div class="text-sm text-base-content/70">{{ $desc }}</div>
                    @endif
                </div>
            @else
                <div class="timeline-end {{ $c['box'] }}">
                    <div class="font-medium">{{ $title }}</div>
                    @if($desc !== null)
                        <div class="text-sm text-base-content/70">{{ $desc }}</div>
                    @endif
                </div>
            @endif

            @if($i < $count - 1)
                <hr class="{{ $hrColor }}" />
            @endif
        </li>
    @endforeach
</ul>
