@props([
    'items' => null,
    'separator' => 'chevron',
    'size' => 'md',
])

@php
    $c = \SparrowhawkLabs\PinionUi\Compose\BreadcrumbComposer::compose([
        'separator' => $separator,
        'size' => $size,
    ]);
@endphp

<div {{ $attributes->class([$c['root']]) }}>
    <ul>
        @if($items !== null)
            @foreach($items as $item)
                <li>
                    @if($item['url'] ?? null)
                        <a href="{{ $item['url'] }}">{{ $item['label'] ?? '' }}</a>
                    @else
                        <span>{{ $item['label'] ?? '' }}</span>
                    @endif
                </li>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </ul>
</div>
