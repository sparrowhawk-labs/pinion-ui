@props([
    'paginator' => null,

    'current' => 1,
    'last' => 1,
    'total' => 0,
    'perPage' => 10,

    'baseUrl' => null,
    'pageParam' => 'page',
    'preserveQuery' => true,

    'showInfo' => true,

    'size' => 'md',
    'color' => 'primary',
    'appearance' => 'soft',

    'prevLabel' => '前へ',
    'nextLabel' => '次へ',
    'infoTemplate' => '全 :total 件中 :first - :last 件',
])

@php
    if ($paginator) {
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $total = $paginator->total();
        $perPage = $paginator->perPage();
        $firstItem = $paginator->firstItem() ?? 0;
        $lastItem = $paginator->lastItem() ?? 0;
        $hasPages = $paginator->hasPages();
        $onFirstPage = $paginator->onFirstPage();
        $hasMorePages = $paginator->hasMorePages();
    } else {
        $firstItem = $total > 0 ? ($current - 1) * $perPage + 1 : 0;
        $lastItem = min($current * $perPage, $total);
        $hasPages = $last > 1;
        $onFirstPage = $current <= 1;
        $hasMorePages = $current < $last;
    }

    $c = \SparrowhawkLabs\PinionUi\Compose\PaginationComposer::compose([
        'current' => $current,
        'last' => $last,
        'size' => $size,
        'color' => $color,
        'appearance' => $appearance,
    ]);

    $existingQuery = request()->query();
    $buildUrl = fn ($page) => \SparrowhawkLabs\PinionUi\Compose\PaginationComposer::buildUrl(
        $baseUrl, (int) $page, $pageParam, $preserveQuery, $existingQuery
    );

    $infoHtml = str_replace(
        [':total', ':first', ':last'],
        [
            '<span class="font-medium text-base-content">' . number_format((int) $total) . '</span>',
            '<span class="font-medium text-base-content">' . e($firstItem) . '</span>',
            '<span class="font-medium text-base-content">' . e($lastItem) . '</span>',
        ],
        e($infoTemplate)
    );

    $iconSize = match ($size) {
        'sm' => 'w-3.5 h-3.5',
        'lg' => 'w-5 h-5',
        default => 'w-4 h-4',
    };
@endphp

@if($hasPages)
<div
    x-data="{
        current: {{ (int) $current }},
        last: {{ (int) $last }},
        baseUrl: @js($baseUrl ?? ''),
        pageParam: @js($pageParam),
        goToPage(page) {
            if (page < 1 || page > this.last || page === this.current) return;
            this.$dispatch('page-change', { page });
            if (this.baseUrl) {
                const url = new URL(this.baseUrl, window.location.origin);
                url.searchParams.set(this.pageParam, page);
                window.location.href = url.toString();
            }
        }
    }"
    {{ $attributes->class([$c['wrapperSimple']]) }}
>
    <nav aria-label="ページネーション" class="{{ $c['nav'] }}">
        {{-- Prev --}}
        @if($onFirstPage)
            <button type="button" class="{{ $c['itemBase'] }} {{ $c['itemDisabled'] }}" disabled aria-label="{{ $prevLabel }}">
                <x-i type="alt-arrow-left" variant="linear" class="{{ $iconSize }}" />
            </button>
        @elseif($baseUrl)
            <a href="{{ $buildUrl($current - 1) }}" class="{{ $c['itemBase'] }} {{ $c['itemIdle'] }}" aria-label="{{ $prevLabel }}">
                <x-i type="alt-arrow-left" variant="linear" class="{{ $iconSize }}" />
            </a>
        @else
            <button type="button" @click="goToPage(current - 1)" class="{{ $c['itemBase'] }} {{ $c['itemIdle'] }}" aria-label="{{ $prevLabel }}">
                <x-i type="alt-arrow-left" variant="linear" class="{{ $iconSize }}" />
            </button>
        @endif

        {{-- Current / total info --}}
        <span class="{{ $c['itemBase'] }} {{ $c['itemStatic'] }} font-normal">
            <span class="font-medium">{{ $current }}</span>
            <span class="text-base-content/50 mx-1">/</span>
            <span>{{ $last }}</span>
        </span>

        {{-- Next --}}
        @if(!$hasMorePages)
            <button type="button" class="{{ $c['itemBase'] }} {{ $c['itemDisabled'] }}" disabled aria-label="{{ $nextLabel }}">
                <x-i type="alt-arrow-right" variant="linear" class="{{ $iconSize }}" />
            </button>
        @elseif($baseUrl)
            <a href="{{ $buildUrl($current + 1) }}" class="{{ $c['itemBase'] }} {{ $c['itemIdle'] }}" aria-label="{{ $nextLabel }}">
                <x-i type="alt-arrow-right" variant="linear" class="{{ $iconSize }}" />
            </a>
        @else
            <button type="button" @click="goToPage(current + 1)" class="{{ $c['itemBase'] }} {{ $c['itemIdle'] }}" aria-label="{{ $nextLabel }}">
                <x-i type="alt-arrow-right" variant="linear" class="{{ $iconSize }}" />
            </button>
        @endif
    </nav>

    @if($showInfo && $total > 0)
        <p class="{{ $c['infoText'] }}">{!! $infoHtml !!}</p>
    @endif
</div>
@endif
