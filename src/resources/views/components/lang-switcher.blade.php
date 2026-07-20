@props([
    'locales' => [],
    'current' => null,
    'label' => 'Lang',       // visible label text; pass '' to hide
    'size' => 'sm',          // legacy (v0.7 dropdown chrome) — accepted for BC, single size now
    'position' => 'bottom-end', // legacy — only the top/bottom axis is honored (drop direction)
    'width' => 'w-40',       // dropdown panel width
])

@php
    // <x-lang-switcher> — locale picker in the same control family as
    // <x-theme-tune-switcher> (label + chip trigger + dropdown; v0.8.0 redesign —
    // the previous <x-dropdown>-based chrome read as a different control family
    // when placed next to the theme/tune switcher, see SEMVER.md).
    //
    // Locale-routing-agnostic by design: this component doesn't know how
    // `href` is built (locale prefix, subdomain, query param, …) — the
    // consumer resolves each locale's URL and hands over a flat array:
    // [['code' => 'ja', 'label' => '🇯🇵 JA', 'href' => '/ja/foo'], ...].
    // `active` per-item is optional; `current` (a locale code) is the usual
    // way to mark the active one from the outside. Options are server-rendered
    // <a href> links so static exporters / crawlers see every locale.
    $active = collect($locales)->first(fn ($l) => ($l['code'] ?? null) === $current)
        ?? collect($locales)->first(fn ($l) => $l['active'] ?? false)
        ?? ($locales[0] ?? null);
    $activeLabel = $active['label'] ?? ($active['code'] ?? '');
    $activeCode  = $active['code'] ?? null;
    // legacy `position` ('bottom-end' | 'top-end' | …): only the vertical axis matters now.
    $dropPos = str_starts_with((string) $position, 'top') ? 'bottom-full right-0 mb-1' : 'top-full right-0 mt-1';
    $chev  = '<svg class="size-3 text-base-content/50" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>';
    $check = '<svg class="ml-auto size-3 text-primary shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 01.006 1.414l-7.5 7.6a1 1 0 01-1.42.005l-3.5-3.5a1 1 0 011.414-1.414l2.79 2.79 6.794-6.886a1 1 0 011.416-.009z" clip-rule="evenodd"/></svg>';
@endphp

<div {{ $attributes->class(['relative inline-flex items-center gap-2']) }}
    x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false">
    @if ($label !== '' && $label !== false)
        <label class="text-xs font-medium text-base-content/60">{{ $label }}</label>
    @endif
    <button type="button" x-on:click="open = !open" x-bind:aria-expanded="open" aria-label="Language"
        class="text-xs px-2 py-1 rounded-[var(--radius-field)] tune-border border-base-300 bg-base-100 hover:bg-base-200 transition-colors flex items-center gap-1.5 cursor-pointer">
        <span class="leading-none">{{ $activeLabel }}</span>
        <span x-bind:class="open ? 'rotate-180' : ''" class="inline-flex transition-transform">{!! $chev !!}</span>
    </button>
    <ul x-show="open" x-cloak x-transition.opacity.duration.100ms role="listbox"
        class="absolute {{ $dropPos }} z-50 {{ $width }} rounded-[var(--radius-box)] tune-border border-base-300 bg-base-100 shadow-[var(--shadow-box)] py-1">
        @foreach ($locales as $loc)
            <li>
                <a href="{{ $loc['href'] ?? '#' }}" role="option"
                    aria-selected="{{ ($loc['code'] ?? null) === $activeCode ? 'true' : 'false' }}"
                    class="flex items-center gap-2.5 w-full px-3 py-1.5 text-xs hover:bg-base-200 transition-colors text-left {{ ($loc['code'] ?? null) === $activeCode ? 'bg-base-200 font-semibold' : '' }}">
                    <span>{{ $loc['label'] ?? ($loc['code'] ?? '') }}</span>
                    @if (($loc['code'] ?? null) === $activeCode)
                        {!! $check !!}
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</div>
