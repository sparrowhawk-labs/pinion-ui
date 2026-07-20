@props([
    'position' => 'fixed',   // 'fixed' (floating top-right) | 'inline' (sits in flow)
    'compact' => false,      // icon-only triggers (sun-moon / color-dots chip / Aa) — for mobile or tight chrome
    'drop' => 'down',        // dropdown direction: 'down' | 'up' (use 'up' when the switcher sits at the bottom of the screen)
    'attribution' => true,   // show the pinion-ui attribution link pinned at each dropdown's top-right. Opt out with :attribution="false"
    'link' => 'github',      // attribution link target: 'github' (repo, default) | 'site' (pinion-ui.dev) | any URL
    'storage' => true,       // persist the choice to localStorage
    'storageKey' => 'pn',    // localStorage key prefix
    'themes' => null,        // override with a FLAT list of literal theme ids (disables the grouped lineup + mode toggle)
    'tunes' => null,         // override the tune list (array)  — default below
])

@php
    // <x-theme-tune-switcher> — a self-contained data-theme × data-tune switcher (pure
    // inline Alpine; needs Alpine on the page but NO ui:install). Each option previews its
    // own theme via `:data-theme` color dots / its own tune via `:data-tune` on the label.
    // The look matches the visualize playground switcher. (Distinct from <x-theme-switcher>,
    // which is a simple light/dark toggle button.)
    //
    // v0.6.0: the default list is the shipped 36-theme lineup, grouped
    // (Brand / Mood / SaaS / Industry) via pn_theme_groups() — the same
    // lineup.json that generates the theme CSS, so this picker cannot drift.
    // Each lineup entry is a light/dark PAIR (`<name>` / `<name>-dark`); a
    // sun/moon mode toggle switches the whole picker between the two columns.
    // Passing `:themes="[...]"` (flat literal ids) restores the old
    // ungrouped single-list behaviour and hides the mode toggle.
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
    $tuneList  = $tunes ?? ['default', 'minimal', 'sharp', 'corporate', 'tech', 'brutal', 'editorial', 'luxury', 'soft', 'pixel', 'draft'];
    // compact = icon-only triggers (dots chip / Aa / sun-moon), labels and value text hidden;
    // hover titles keep the current values discoverable. Attribution stays via the pinned dropdown links.
    $gap  = $compact ? 'gap-1.5' : 'gap-3';
    $pad  = $compact ? 'px-2 py-1.5' : 'px-3 py-2';
    $wrap = $position === 'inline'
        ? "relative inline-flex items-center {$gap}"
        : "fixed top-3 right-4 z-[900] flex items-center {$gap} {$pad} rounded-[var(--radius-box)] tune-border border-base-content/15 bg-base-100/90 backdrop-blur shadow-[var(--shadow-box)]";
    // drop=up flips both dropdowns above the trigger row (bottom-of-screen placements).
    $dropPos = $drop === 'up' ? 'bottom-full right-0 mb-1' : 'top-full right-0 mt-1';
    $chev  = '<svg class="size-3 text-base-content/50" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>';
    $check = '<svg class="ml-auto size-3 text-primary" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 01.006 1.414l-7.5 7.6a1 1 0 01-1.42.005l-3.5-3.5a1 1 0 011.414-1.414l2.79 2.79 6.794-6.886a1 1 0 011.416-.009z" clip-rule="evenodd"/></svg>';
    $dots  = '<span class="size-2 rounded-full bg-primary"></span><span class="size-2 rounded-full bg-secondary"></span><span class="size-2 rounded-full bg-accent"></span>';
    $sun   = '<svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>';
    $moon  = '<svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>';
    // GitHub mark (Simple Icons "github", inlined — kept intentionally small/quiet, this is
    // attribution, not promotion). Used by the pinned top-right link inside each dropdown.
    $githubMark = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 .5C5.73.5.5 5.73.5 12c0 5.08 3.29 9.39 7.86 10.91.57.1.78-.25.78-.55 0-.27-.01-1.16-.02-2.11-3.2.7-3.88-1.36-3.88-1.36-.53-1.34-1.29-1.7-1.29-1.7-1.05-.72.08-.71.08-.71 1.17.08 1.78 1.2 1.78 1.2 1.03 1.77 2.71 1.26 3.37.96.1-.75.4-1.26.73-1.55-2.55-.29-5.24-1.28-5.24-5.68 0-1.25.45-2.28 1.19-3.08-.12-.29-.51-1.46.11-3.04 0 0 .97-.31 3.18 1.18a11.1 11.1 0 015.8 0c2.2-1.49 3.17-1.18 3.17-1.18.63 1.58.24 2.75.12 3.04.74.8 1.18 1.83 1.18 3.08 0 4.41-2.69 5.38-5.25 5.67.42.36.78 1.08.78 2.18 0 1.57-.02 2.84-.02 3.23 0 .3.21.66.79.55A10.51 10.51 0 0023.5 12c0-6.27-5.23-11.5-11.5-11.5z"/></svg>';
    $linkHref = match ($link) {
        'github' => 'https://github.com/sparrowhawk-labs/pinion-ui',
        'site'   => 'https://pinion-ui.dev/',
        default  => $link,
    };
    // Attribution link — pinned OUTSIDE the scrollable <ul> at each dropdown's top-right,
    // so it stays visible while the list scrolls. Deliberately faint/small.
    $attrLink = <<<HTML
        <div class="flex justify-end px-2.5 pt-1.5 -mb-0.5">
            <a href="{$linkHref}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-1 text-[9px] leading-none text-base-content/25 hover:text-base-content/60 transition-colors"
                title="Built with pinion-ui" aria-label="Built with pinion-ui">
                <span class="size-2">{$githubMark}</span>
                <span>pinion-ui</span>
            </a>
        </div>
    HTML;
    $defaultTheme = $grouped ? 'pinion' : ($groupData[0]['items'][0]['light'] ?? 'pinion');
    // light/dark mode toggle — flips the whole lineup between the <name> / <name>-dark columns.
    // Always leads the bar (leftmost) in both full and compact: the most-used control sits first.
    // (Trade-off, accepted: clicking it while a theme list is open closes the list via click-outside.)
    // Only the dark-branch (sun) icon is x-cloak'd: pre-Alpine the light-default moon stays
    // visible, so the button never shows both icons stacked nor collapses empty for a frame.
    $modeBtn = <<<HTML
        <button type="button" x-on:click="setMode(mode === 'dark' ? 'light' : 'dark')"
            class="p-1 rounded-[var(--radius-field)] text-base-content/60 hover:bg-base-200 transition-colors cursor-pointer"
            x-bind:aria-label="mode === 'dark' ? 'Switch to light themes' : 'Switch to dark themes'"
            x-bind:title="mode === 'dark' ? 'Light themes' : 'Dark themes'">
            <span x-show="mode === 'dark'" x-cloak>{$sun}</span>
            <span x-show="mode !== 'dark'">{$moon}</span>
        </button>
    HTML;
@endphp

<div
    {{ $attributes->class([$wrap]) }}
    x-data="{
        theme: @js((bool) $storage) ? (localStorage.getItem('{{ $storageKey }}-theme') || document.documentElement.dataset.theme || @js($defaultTheme)) : (document.documentElement.dataset.theme || @js($defaultTheme)),
        tune: @js((bool) $storage) ? (localStorage.getItem('{{ $storageKey }}-tune') || document.documentElement.dataset.tune || 'default') : (document.documentElement.dataset.tune || 'default'),
        groups: @js($groupData),
        grouped: @js($grouped),
        tunes: @js(array_values($tuneList)),
        mode: 'light',
        themeOpen: false, tuneOpen: false,
        pairOf(id) {
            for (const g of this.groups) for (const t of g.items) {
                if (t.light === id || t.dark === id) return t;
            }
            return null;
        },
        catOf(id) { return this.pairOf(id)?.cat ?? null; },
        catColor: { Brand: 'bg-primary', Mood: 'bg-accent', SaaS: 'bg-info', Industry: 'bg-secondary' },
        idFor(t) { return this.mode === 'dark' ? t.dark : t.light; },
        apply(id) {
            this.theme = id;
            document.documentElement.dataset.theme = id;
            if (@js((bool) $storage)) localStorage.setItem('{{ $storageKey }}-theme', id);
        },
        setTheme(t) { this.apply(this.idFor(t)); this.themeOpen = false; },
        setMode(m) {
            this.mode = m;
            const pair = this.pairOf(this.theme);
            if (pair) this.apply(this.idFor(pair));
        },
        isActive(t) { return this.theme === t.light || this.theme === t.dark; },
        setTune(t) { this.tune = t; document.documentElement.dataset.tune = t; if (@js((bool) $storage)) localStorage.setItem('{{ $storageKey }}-tune', t); this.tuneOpen = false; },
        init() {
            const pair = this.pairOf(this.theme);
            if (pair && pair.dark === this.theme && pair.dark !== pair.light) this.mode = 'dark';
            document.documentElement.dataset.theme = this.theme;
            document.documentElement.dataset.tune = this.tune;
        },
    }"
>
    @if ($grouped)
        {{-- light/dark toggle leads the bar (full and compact alike) --}}
        {!! $modeBtn !!}
    @endif

    {{-- Theme --}}
    <div class="flex items-center gap-2 relative" x-on:click.outside="themeOpen = false" x-on:keydown.escape.window="themeOpen = false">
        @unless ($compact)
            <label class="text-xs font-medium text-base-content/60">Theme</label>
        @endunless
        <button type="button" x-on:click="themeOpen = !themeOpen" x-bind:aria-expanded="themeOpen" x-bind:title="theme" aria-label="Theme"
            class="text-xs px-2 py-1 rounded-[var(--radius-field)] tune-border border-base-300 bg-base-100 hover:bg-base-200 transition-colors flex items-center gap-1.5 cursor-pointer">
            <span x-bind:data-theme="theme" class="inline-flex shrink-0 items-center gap-1 px-1.5 py-0.5 rounded-[calc(var(--radius-field)*0.7)] bg-base-100 tune-border border-base-content/20">{!! $dots !!}</span>
            @unless ($compact)
                <span x-text="theme"></span>
                {{-- category chip — the lineup uses bare names (no mood- prefix since
                     v0.7.0), so the selected theme's category is surfaced here --}}
                <template x-if="grouped && catOf(theme)">
                    <span class="inline-flex items-center gap-1 text-[9px] font-semibold uppercase tracking-wide text-base-content/45">
                        <span class="size-1.5 rounded-full" x-bind:class="catColor[catOf(theme)]"></span>
                        <span x-text="catOf(theme)"></span>
                    </span>
                </template>
                <span x-bind:class="themeOpen ? 'rotate-180' : ''" class="inline-flex transition-transform">{!! $chev !!}</span>
            @endunless
        </button>
        <div x-show="themeOpen" x-cloak x-transition.opacity.duration.100ms
            class="absolute {{ $dropPos }} z-50 w-64 rounded-[var(--radius-box)] tune-border border-base-300 bg-base-100 shadow-[var(--shadow-box)] overflow-hidden">
        @if ($attribution)
            {!! $attrLink !!}
        @endif
        <ul role="listbox" class="max-h-80 overflow-y-auto py-1">
            <template x-for="g in groups" x-bind:key="g.label ?? 'flat'">
                <li>
                    <template x-if="g.label">
                        <div class="flex items-center gap-1.5 px-3 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-wide text-base-content/40">
                            <span class="size-1.5 rounded-full" x-bind:class="catColor[g.label]"></span>
                            <span x-text="g.label"></span>
                        </div>
                    </template>
                    <ul>
                        <template x-for="t in g.items" x-bind:key="t.name">
                            <li>
                                <button type="button" x-on:click="setTheme(t)" role="option" x-bind:aria-selected="isActive(t)"
                                    class="flex items-center gap-2.5 w-full px-3 py-1.5 text-xs hover:bg-base-200 transition-colors text-left"
                                    x-bind:class="isActive(t) ? 'bg-base-200 font-semibold' : ''">
                                    <span x-bind:data-theme="idFor(t)" class="inline-flex shrink-0 items-center gap-1 px-2 py-1.5 rounded-[calc(var(--radius-box)*0.6)] bg-base-100 tune-border border-base-content/20">{!! $dots !!}</span>
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
    </div>

    {{-- Tune --}}
    <div class="flex items-center gap-2 relative" x-on:click.outside="tuneOpen = false" x-on:keydown.escape.window="tuneOpen = false">
        @unless ($compact)
            <label class="text-xs font-medium text-base-content/60">Tune</label>
        @endunless
        <button type="button" x-on:click="tuneOpen = !tuneOpen" x-bind:aria-expanded="tuneOpen" x-bind:title="tune" aria-label="Tune"
            class="text-xs px-2 py-1 rounded-[var(--radius-field)] tune-border border-base-300 bg-base-100 hover:bg-base-200 transition-colors flex items-center gap-1.5 cursor-pointer">
            @if ($compact)
                <span x-bind:data-tune="tune" class="text-[13px] font-medium leading-none">Aa</span>
            @else
                <span x-bind:data-tune="tune" class="leading-none" x-text="tune"></span>
                <span x-bind:class="tuneOpen ? 'rotate-180' : ''" class="inline-flex transition-transform">{!! $chev !!}</span>
            @endif
        </button>
        <div x-show="tuneOpen" x-cloak x-transition.opacity.duration.100ms
            class="absolute {{ $dropPos }} z-50 w-60 rounded-[var(--radius-box)] tune-border border-base-300 bg-base-100 shadow-[var(--shadow-box)] overflow-hidden">
        @if ($attribution)
            {!! $attrLink !!}
        @endif
        <ul role="listbox" class="max-h-96 overflow-y-auto py-1">
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
</div>
