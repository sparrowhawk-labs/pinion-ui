@props([
    'locales' => [],         // optional Lang section: [['code','label','href'], …] (same shape as x-lang-switcher); empty = section hidden
    'current' => null,       // active locale code
    'drop' => 'down',        // panel direction: 'down' | 'up'
    'width' => 'w-72',       // panel width
    'attribution' => true,   // show the pinion-ui attribution link pinned at the panel's top-right. Opt out with :attribution="false"
    'link' => 'github',      // attribution link target: 'github' (repo, default) | 'site' (pinion-ui.dev) | any URL
    'storage' => true,       // persist the choice to localStorage
    'storageKey' => 'pn',    // localStorage key prefix
    'themes' => null,        // override with a FLAT list of literal theme ids (disables the grouped lineup + mode toggle)
    'tunes' => null,         // override the tune list (array)
])

@php
    // <x-settings-switcher> — theme × tune × lang consolidated into ONE trigger + panel,
    // for tight chrome (mobile navbars) where the separate <x-theme-tune-switcher> +
    // <x-lang-switcher> pair would wrap onto extra rows. Same self-contained inline-Alpine
    // pattern and the same storage keys as <x-theme-tune-switcher> (share `storage-key`
    // when both are on the page — e.g. desktop switchers + this behind a `lg:hidden`).
    //
    // Theme semantics mirror <x-theme-tune-switcher> v0.6.0: the default list is the
    // shipped lineup grouped via pn_theme_groups(), each entry a light/dark pair, with a
    // sun/moon mode toggle in the Theme section heading. `:themes="[...]"` (flat literal
    // ids) restores an ungrouped single list and hides the mode toggle.
    $grouped = $themes === null;
    if ($grouped) {
        $groupData = [];
        foreach (pn_theme_groups() as $label => $items) {
            $groupData[] = ['label' => $label, 'items' => array_values($items)];
        }
    } else {
        $groupData = [[
            'label' => null,
            'items' => array_map(
                fn ($t) => ['name' => $t, 'light' => $t, 'dark' => $t],
                array_values($themes)
            ),
        ]];
    }
    $tuneList = $tunes ?? ['default', 'minimal', 'sharp', 'corporate', 'tech', 'brutal', 'editorial', 'luxury', 'soft', 'pixel', 'draft'];
    $activeCode = collect($locales)->first(fn ($l) => ($l['code'] ?? null) === $current)['code']
        ?? collect($locales)->first(fn ($l) => $l['active'] ?? false)['code']
        ?? null;
    $dropPos = $drop === 'up' ? 'bottom-full right-0 mb-1' : 'top-full right-0 mt-1';
    $check = '<svg class="ml-auto size-3 text-primary shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 01.006 1.414l-7.5 7.6a1 1 0 01-1.42.005l-3.5-3.5a1 1 0 011.414-1.414l2.79 2.79 6.794-6.886a1 1 0 011.416-.009z" clip-rule="evenodd"/></svg>';
    $dots  = '<span class="size-2 rounded-full bg-primary"></span><span class="size-2 rounded-full bg-secondary"></span><span class="size-2 rounded-full bg-accent"></span>';
    $sun   = '<svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>';
    $moon  = '<svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>';
    // Trigger: sliders glyph + live theme color-dots chip (compact by nature — this
    // component exists for chrome where labels don't fit).
    $sliders = '<svg class="size-4 text-base-content/60" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><line x1="4" y1="6" x2="16" y2="6"/><circle cx="8" cy="6" r="1.6" fill="currentColor" stroke="none"/><line x1="4" y1="10" x2="16" y2="10"/><circle cx="13" cy="10" r="1.6" fill="currentColor" stroke="none"/><line x1="4" y1="14" x2="16" y2="14"/><circle cx="10" cy="14" r="1.6" fill="currentColor" stroke="none"/></svg>';
    $githubMark = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 .5C5.73.5.5 5.73.5 12c0 5.08 3.29 9.39 7.86 10.91.57.1.78-.25.78-.55 0-.27-.01-1.16-.02-2.11-3.2.7-3.88-1.36-3.88-1.36-.53-1.34-1.29-1.7-1.29-1.7-1.05-.72.08-.71.08-.71 1.17.08 1.78 1.2 1.78 1.2 1.03 1.77 2.71 1.26 3.37.96.1-.75.4-1.26.73-1.55-2.55-.29-5.24-1.28-5.24-5.68 0-1.25.45-2.28 1.19-3.08-.12-.29-.51-1.46.11-3.04 0 0 .97-.31 3.18 1.18a11.1 11.1 0 015.8 0c2.2-1.49 3.17-1.18 3.17-1.18.63 1.58.24 2.75.12 3.04.74.8 1.18 1.83 1.18 3.08 0 4.41-2.69 5.38-5.25 5.67.42.36.78 1.08.78 2.18 0 1.57-.02 2.84-.02 3.23 0 .3.21.66.79.55A10.51 10.51 0 0023.5 12c0-6.27-5.23-11.5-11.5-11.5z"/></svg>';
    $linkHref = match ($link) {
        'github' => 'https://github.com/sparrowhawk-labs/pinion-ui',
        'site'   => 'https://pinion-ui.dev/',
        default  => $link,
    };
    $defaultTheme = $grouped ? 'pinion' : ($groupData[0]['items'][0]['light'] ?? 'pinion');
@endphp

{{-- tune-exempt: switcher chrome renders tune-neutral (see tune.css); per-option
     previews inside the panel still track their OWN data-tune/data-theme --}}
<div
    {{ $attributes->class(['tune-exempt relative']) }}
    x-data="{
        theme: @js((bool) $storage) ? (localStorage.getItem('{{ $storageKey }}-theme') || document.documentElement.dataset.theme || @js($defaultTheme)) : (document.documentElement.dataset.theme || @js($defaultTheme)),
        tune: @js((bool) $storage) ? (localStorage.getItem('{{ $storageKey }}-tune') || document.documentElement.dataset.tune || 'default') : (document.documentElement.dataset.tune || 'default'),
        groups: @js($groupData),
        grouped: @js($grouped),
        tunes: @js(array_values($tuneList)),
        mode: 'light',
        open: false,
        pairOf(id) {
            for (const g of this.groups) for (const t of g.items) {
                if (t.light === id || t.dark === id) return t;
            }
            return null;
        },
        catColor: { Brand: 'bg-primary', Mood: 'bg-accent', SaaS: 'bg-info', Industry: 'bg-secondary' },
        idFor(t) { return this.mode === 'dark' ? t.dark : t.light; },
        apply(id) {
            this.theme = id;
            document.documentElement.dataset.theme = id;
            if (@js((bool) $storage)) localStorage.setItem('{{ $storageKey }}-theme', id);
        },
        setTheme(t) { this.apply(this.idFor(t)); },
        setMode(m) {
            this.mode = m;
            const pair = this.pairOf(this.theme);
            if (pair) this.apply(this.idFor(pair));
        },
        isActive(t) { return this.theme === t.light || this.theme === t.dark; },
        setTune(t) { this.tune = t; document.documentElement.dataset.tune = t; if (@js((bool) $storage)) localStorage.setItem('{{ $storageKey }}-tune', t); },
        init() {
            const pair = this.pairOf(this.theme);
            if (pair && pair.dark === this.theme && pair.dark !== pair.light) this.mode = 'dark';
            document.documentElement.dataset.theme = this.theme;
            document.documentElement.dataset.tune = this.tune;
        },
    }"
    x-on:click.outside="open = false" x-on:keydown.escape.window="open = false"
>
    <button type="button" x-on:click="open = !open" x-bind:aria-expanded="open"
        aria-label="Theme, tune &amp; language settings"
        class="text-xs px-2 py-1.5 rounded border border-base-300 bg-base-100 hover:bg-base-200 transition-colors flex items-center gap-1.5 cursor-pointer">
        {!! $sliders !!}
        <span x-bind:data-theme="theme" class="inline-flex shrink-0 items-center gap-0.5 px-1 py-0.5 rounded bg-base-100 border border-base-content/20">
            <span class="size-1.5 rounded-full bg-primary"></span>
            <span class="size-1.5 rounded-full bg-secondary"></span>
            <span class="size-1.5 rounded-full bg-accent"></span>
        </span>
    </button>

    <div x-show="open" x-cloak x-transition.opacity.duration.100ms
        class="absolute {{ $dropPos }} z-50 {{ $width }} rounded-md border border-base-300 bg-base-100 shadow-lg overflow-hidden">
        @if ($attribution)
        {{-- Attribution link — pinned OUTSIDE the scrollable body at the panel's
             top-right, so it stays visible while the sections scroll. --}}
        <div class="flex justify-end px-2.5 pt-1.5 -mb-0.5">
            <a href="{{ $linkHref }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-1 text-[9px] leading-none text-base-content/25 hover:text-base-content/60 transition-colors"
                title="Built with pinion-ui" aria-label="Built with pinion-ui">
                <span class="size-2">{!! $githubMark !!}</span>
                <span>pinion-ui</span>
            </a>
        </div>
        @endif
        <div class="max-h-[70vh] overflow-y-auto divide-y divide-base-200">
            {{-- Theme --}}
            <div class="px-3 py-2.5">
                <div class="flex items-center justify-between mb-1.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-base-content/40">Theme</p>
                    @if ($grouped)
                        <button type="button" x-on:click="setMode(mode === 'dark' ? 'light' : 'dark')"
                            class="p-1 rounded text-base-content/60 hover:bg-base-200 transition-colors cursor-pointer"
                            x-bind:aria-label="mode === 'dark' ? 'Switch to light themes' : 'Switch to dark themes'"
                            x-bind:title="mode === 'dark' ? 'Light themes' : 'Dark themes'">
                            <span x-show="mode === 'dark'" x-cloak>{!! $sun !!}</span>
                            <span x-show="mode !== 'dark'">{!! $moon !!}</span>
                        </button>
                    @endif
                </div>
                <ul role="listbox">
                    <template x-for="g in groups" x-bind:key="g.label ?? 'flat'">
                        <li>
                            <template x-if="g.label">
                                <div class="flex items-center gap-1.5 px-1 pt-1.5 pb-1 text-[10px] font-semibold uppercase tracking-wide text-base-content/40">
                                    <span class="size-1.5 rounded-full" x-bind:class="catColor[g.label]"></span>
                                    <span x-text="g.label"></span>
                                </div>
                            </template>
                            <ul class="space-y-0.5">
                                <template x-for="t in g.items" x-bind:key="t.name">
                                    <li>
                                        <button type="button" x-on:click="setTheme(t)" role="option" x-bind:aria-selected="isActive(t)"
                                            class="flex items-center gap-2.5 w-full px-2 py-1.5 rounded text-xs hover:bg-base-200 transition-colors text-left"
                                            x-bind:class="isActive(t) ? 'bg-base-200 font-semibold' : ''">
                                            <span x-bind:data-theme="idFor(t)" class="inline-flex shrink-0 items-center gap-1 px-2 py-1.5 rounded-md bg-base-100 border border-base-content/20">{!! $dots !!}</span>
                                            <span x-text="grouped ? t.name : idFor(t)"></span>
                                            <span x-show="isActive(t)">{!! $check !!}</span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </li>
                    </template>
                </ul>
            </div>
            {{-- Tune --}}
            <div class="px-3 py-2.5">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-base-content/40 mb-1.5">Tune</p>
                <ul class="space-y-0.5" role="listbox">
                    <template x-for="t in tunes" x-bind:key="t">
                        <li>
                            <button type="button" x-on:click="setTune(t)" role="option" x-bind:aria-selected="tune === t"
                                class="flex items-center gap-2 w-full px-2 py-1.5 rounded hover:bg-base-200 transition-colors text-left"
                                x-bind:class="tune === t ? 'bg-base-200' : ''">
                                <span x-bind:data-tune="t" style="font-family: var(--font-heading); font-weight: var(--font-weight-heading, 700);" class="text-sm leading-none" x-text="'Aa ' + t"></span>
                                <span x-show="tune === t">{!! $check !!}</span>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>
            {{-- Lang (server-rendered links so static exporters / crawlers see every locale) --}}
            @if (count($locales))
            <div class="px-3 py-2.5">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-base-content/40 mb-1.5">Lang</p>
                <ul class="space-y-0.5" role="listbox">
                    @foreach ($locales as $loc)
                        <li>
                            <a href="{{ $loc['href'] ?? '#' }}" role="option"
                                aria-selected="{{ ($loc['code'] ?? null) === $activeCode ? 'true' : 'false' }}"
                                class="flex items-center gap-2.5 w-full px-2 py-1.5 rounded text-xs hover:bg-base-200 transition-colors text-left {{ ($loc['code'] ?? null) === $activeCode ? 'bg-base-200 font-semibold' : '' }}">
                                <span>{{ $loc['label'] ?? ($loc['code'] ?? '') }}</span>
                                @if (($loc['code'] ?? null) === $activeCode)
                                    {!! $check !!}
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>
