@props([
    'position' => 'fixed',   // 'fixed' (floating top-right) | 'inline' (sits in flow)
    'storage' => true,       // persist the choice to localStorage
    'storageKey' => 'pn',    // localStorage key prefix
    'themes' => null,        // override the theme list (array) — default below
    'tunes' => null,         // override the tune list (array)  — default below
])

@php
    // <x-theme-tune-switcher> — a self-contained data-theme × data-tune switcher (pure
    // inline Alpine; needs Alpine on the page but NO ui:install). Each option previews its
    // own theme via `:data-theme` color dots / its own tune via `:data-tune` on the label.
    // The look matches the visualize playground switcher. (Distinct from <x-theme-switcher>,
    // which is a simple light/dark toggle button.)
    $themeList = $themes ?? ['reactive', 'pinion', 'light', 'dark', 'night', 'business', 'corporate', 'dim', 'nord', 'cupcake', 'emerald', 'forest', 'dracula', 'sunset', 'winter'];
    $tuneList  = $tunes ?? ['default', 'minimal', 'sharp', 'corporate', 'tech', 'brutal', 'editorial', 'luxury', 'soft', 'pixel', 'draft'];
    $wrap = $position === 'inline'
        ? 'inline-flex items-center gap-3'
        : 'fixed top-3 right-4 z-[900] flex items-center gap-3 px-3 py-2 rounded-[var(--radius-box)] tune-border border-base-content/15 bg-base-100/90 backdrop-blur shadow-[var(--shadow-box)]';
    $chev  = '<svg class="size-3 text-base-content/50" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>';
    $check = '<svg class="ml-auto size-3 text-primary" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 01.006 1.414l-7.5 7.6a1 1 0 01-1.42.005l-3.5-3.5a1 1 0 011.414-1.414l2.79 2.79 6.794-6.886a1 1 0 011.416-.009z" clip-rule="evenodd"/></svg>';
    $dots  = '<span class="size-2 rounded-full bg-primary"></span><span class="size-2 rounded-full bg-secondary"></span><span class="size-2 rounded-full bg-accent"></span>';
@endphp

<div
    {{ $attributes->class([$wrap]) }}
    x-data="{
        theme: @js((bool) $storage) ? (localStorage.getItem('{{ $storageKey }}-theme') || document.documentElement.dataset.theme || 'reactive') : (document.documentElement.dataset.theme || 'reactive'),
        tune: @js((bool) $storage) ? (localStorage.getItem('{{ $storageKey }}-tune') || document.documentElement.dataset.tune || 'default') : (document.documentElement.dataset.tune || 'default'),
        themes: @js(array_values($themeList)),
        tunes: @js(array_values($tuneList)),
        themeOpen: false, tuneOpen: false,
        setTheme(t) { this.theme = t; document.documentElement.dataset.theme = t; if (@js((bool) $storage)) localStorage.setItem('{{ $storageKey }}-theme', t); this.themeOpen = false; },
        setTune(t) { this.tune = t; document.documentElement.dataset.tune = t; if (@js((bool) $storage)) localStorage.setItem('{{ $storageKey }}-tune', t); this.tuneOpen = false; },
        init() { document.documentElement.dataset.theme = this.theme; document.documentElement.dataset.tune = this.tune; },
    }"
>
    {{-- Theme --}}
    <div class="flex items-center gap-2 relative" x-on:click.outside="themeOpen = false" x-on:keydown.escape.window="themeOpen = false">
        <label class="text-xs font-medium text-base-content/60">Theme</label>
        <button type="button" x-on:click="themeOpen = !themeOpen" x-bind:aria-expanded="themeOpen"
            class="text-xs px-2 py-1 rounded-[var(--radius-field)] tune-border border-base-300 bg-base-100 hover:bg-base-200 transition-colors flex items-center gap-1.5 cursor-pointer">
            <span x-bind:data-theme="theme" class="inline-flex shrink-0 items-center gap-1 px-1.5 py-0.5 rounded-[calc(var(--radius-field)*0.7)] bg-base-100 tune-border border-base-content/20">{!! $dots !!}</span>
            <span x-text="theme"></span>
            <span x-bind:class="themeOpen ? 'rotate-180' : ''" class="inline-flex transition-transform">{!! $chev !!}</span>
        </button>
        <ul x-show="themeOpen" x-cloak x-transition.opacity.duration.100ms role="listbox"
            class="absolute top-full right-0 mt-1 z-50 w-60 max-h-80 overflow-y-auto rounded-[var(--radius-box)] tune-border border-base-300 bg-base-100 shadow-[var(--shadow-box)] py-1">
            <template x-for="t in themes" x-bind:key="t">
                <li>
                    <button type="button" x-on:click="setTheme(t)" role="option" x-bind:aria-selected="theme === t"
                        class="flex items-center gap-2.5 w-full px-3 py-1.5 text-xs hover:bg-base-200 transition-colors text-left"
                        x-bind:class="theme === t ? 'bg-base-200 font-semibold' : ''">
                        <span x-bind:data-theme="t" class="inline-flex shrink-0 items-center gap-1 px-2 py-1.5 rounded-[calc(var(--radius-box)*0.6)] bg-base-100 tune-border border-base-content/20">{!! $dots !!}</span>
                        <span x-text="t"></span>
                        <span x-show="theme === t">{!! $check !!}</span>
                    </button>
                </li>
            </template>
        </ul>
    </div>

    {{-- Tune --}}
    <div class="flex items-center gap-2 relative" x-on:click.outside="tuneOpen = false" x-on:keydown.escape.window="tuneOpen = false">
        <label class="text-xs font-medium text-base-content/60">Tune</label>
        <button type="button" x-on:click="tuneOpen = !tuneOpen" x-bind:aria-expanded="tuneOpen"
            class="text-xs px-2 py-1 rounded-[var(--radius-field)] tune-border border-base-300 bg-base-100 hover:bg-base-200 transition-colors flex items-center gap-1.5 cursor-pointer">
            <span x-bind:data-tune="tune" class="leading-none" x-text="tune"></span>
            <span x-bind:class="tuneOpen ? 'rotate-180' : ''" class="inline-flex transition-transform">{!! $chev !!}</span>
        </button>
        <ul x-show="tuneOpen" x-cloak x-transition.opacity.duration.100ms role="listbox"
            class="absolute top-full right-0 mt-1 z-50 w-60 max-h-96 overflow-y-auto rounded-[var(--radius-box)] tune-border border-base-300 bg-base-100 shadow-[var(--shadow-box)] py-1">
            <template x-for="t in tunes" x-bind:key="t">
                <li>
                    <button type="button" x-on:click="setTune(t)" role="option" x-bind:aria-selected="tune === t"
                        class="flex items-center gap-2 w-full px-3 py-2 hover:bg-base-200 transition-colors text-left"
                        x-bind:class="tune === t ? 'bg-base-200' : ''">
                        <span x-bind:data-tune="t" class="text-sm leading-none" x-text="'Aa ' + t"></span>
                        <span x-show="tune === t">{!! $check !!}</span>
                    </button>
                </li>
            </template>
        </ul>
    </div>
</div>
