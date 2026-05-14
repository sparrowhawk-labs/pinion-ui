@props([
    'paginator' => null,

    'current' => 1,
    'last' => 1,
    'total' => 0,
    'perPage' => 10,

    'baseUrl' => null,
    'pageParam' => 'page',
    'preserveQuery' => true,

    'onEachSide' => 1,
    'showInfo' => true,

    'size' => 'md',
    'color' => 'primary',
    'appearance' => 'soft',

    'prevLabel' => null,
    'nextLabel' => null,
    'infoTemplate' => null,
])

@php
    $prevLabel    ??= pn_trans('pagination.prev', '前へ');
    $nextLabel    ??= pn_trans('pagination.next', '次へ');
    $infoTemplate ??= pn_trans('pagination.info', '全 :total 件中 :first - :last 件');

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
        'onEachSide' => $onEachSide,
        'size' => $size,
        'color' => $color,
        'appearance' => $appearance,
    ]);
    $window = \SparrowhawkLabs\PinionUi\Compose\PaginationComposer::windowPages((int) $current, (int) $last, (int) $onEachSide);

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
    {{ $attributes->class([$c['wrapper']]) }}
>
    @if($showInfo && $total > 0)
        <p class="{{ $c['infoText'] }} order-2 sm:order-1">{!! $infoHtml !!}</p>
    @else
        <div class="hidden sm:block"></div>
    @endif

    <nav aria-label="{{ pn_trans('pagination.aria', 'ページネーション') }}" class="{{ $c['nav'] }} order-1 sm:order-2">
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

        {{-- First page + dots --}}
        @if($window['showFirst'])
            @if($baseUrl)
                <a href="{{ $buildUrl(1) }}" class="{{ $c['itemBase'] }} {{ $c['itemIdle'] }}">1</a>
            @else
                <button type="button" @click="goToPage(1)" class="{{ $c['itemBase'] }} {{ $c['itemIdle'] }}">1</button>
            @endif
        @endif
        @if($window['showDotsLeft'])
            <span class="{{ $c['itemBase'] }} {{ $c['itemStatic'] }}">…</span>
        @endif

        {{-- Pages --}}
        @foreach($window['pages'] as $page)
            @if($page === (int) $current)
                <button type="button" class="{{ $c['itemBase'] }} {{ $c['itemActive'] }}" aria-current="page">{{ $page }}</button>
            @elseif($baseUrl)
                <a href="{{ $buildUrl($page) }}" class="{{ $c['itemBase'] }} {{ $c['itemIdle'] }}">{{ $page }}</a>
            @else
                <button type="button" @click="goToPage({{ $page }})" class="{{ $c['itemBase'] }} {{ $c['itemIdle'] }}">{{ $page }}</button>
            @endif
        @endforeach

        {{-- Dots + last page --}}
        @if($window['showDotsRight'])
            <span class="{{ $c['itemBase'] }} {{ $c['itemStatic'] }}">…</span>
        @endif
        @if($window['showLast'])
            @if($baseUrl)
                <a href="{{ $buildUrl($last) }}" class="{{ $c['itemBase'] }} {{ $c['itemIdle'] }}">{{ $last }}</a>
            @else
                <button type="button" @click="goToPage({{ (int) $last }})" class="{{ $c['itemBase'] }} {{ $c['itemIdle'] }}">{{ $last }}</button>
            @endif
        @endif

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
</div>
@endif
