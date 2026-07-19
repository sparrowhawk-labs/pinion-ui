@props([
    'themes' => ['pinion', 'pinion-dark'],
    'size' => 'md',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'w-8 h-8',
        'lg' => 'w-12 h-12',
        default => 'w-10 h-10',
    };

    $iconSize = match($size) {
        'sm' => 'w-4 h-4',
        'lg' => 'w-6 h-6',
        default => 'w-5 h-5',
    };
@endphp

<button
    x-data="{
        theme: document.documentElement.getAttribute('data-theme') || '{{ $themes[0] }}',
        themes: @js($themes),
        toggle() {
            const idx = this.themes.indexOf(this.theme);
            this.theme = this.themes[(idx + 1) % this.themes.length];
            document.documentElement.setAttribute('data-theme', this.theme);
            localStorage.setItem('theme', this.theme);
        },
        init() {
            const saved = localStorage.getItem('theme');
            if (saved && this.themes.includes(saved)) {
                this.theme = saved;
                document.documentElement.setAttribute('data-theme', this.theme);
            }
        }
    }"
    @click="toggle()"
    {{ $attributes->merge([
        'class' => "$sizeClasses inline-flex items-center justify-center rounded-[var(--radius-field)] text-base-content hover:bg-base-200 transition-colors cursor-pointer",
        'type' => 'button',
        'aria-label' => 'Toggle theme',
    ]) }}
>
    {{-- Sun icon (shown in dark theme — v0.6.0 lineup convention: dark themes end in "-dark").
         x-cloak'd (moon is not): pre-Alpine only the light-default moon shows, instead of
         both icons stacked — mirrors <x-theme-tune-switcher>'s mode button. --}}
    <svg
        x-show="theme === 'dark' || theme.endsWith('-dark')"
        x-cloak
        x-transition
        class="{{ $iconSize }}"
        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
    >
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
    </svg>

    {{-- Moon icon (shown in light theme) --}}
    <svg
        x-show="theme !== 'dark' && !theme.endsWith('-dark')"
        x-transition
        class="{{ $iconSize }}"
        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
    >
        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
    </svg>
</button>
