@props([
    'color' => 'info',
    'appearance' => 'bordered-left',
    'title' => null,
    'dismissible' => false,
    'icon' => null,
    'size' => 'md',
])

@php
    $defaultIcon = match($color) {
        'success' => 'check-circle',
        'warning' => 'danger-triangle',
        'error'   => 'close-circle',
        default   => 'info-circle',
    };
    $iconType = $icon ?? $defaultIcon;

    $sizeClasses = match($size) {
        'sm' => 'min-h-[var(--h-field-sm)] px-[var(--px-field-sm)] py-[var(--px-field-sm)] text-[length:var(--text-field-sm)]',
        'lg' => 'min-h-[var(--h-field-lg)] px-[var(--px-field-lg)] py-[var(--px-field-lg)] text-[length:var(--text-field-lg)]',
        default => 'min-h-[var(--h-field-md)] px-[var(--px-field-md)] py-[var(--px-field-md)] text-[length:var(--text-field-md)]',
    };

    $variantClasses = match("{$appearance}-{$color}") {
        // solid
        'solid-primary'   => 'bg-primary text-primary-content tune-border border-primary',
        'solid-secondary' => 'bg-secondary text-secondary-content tune-border border-secondary',
        'solid-accent'    => 'bg-accent text-accent-content tune-border border-accent',
        'solid-neutral'   => 'bg-neutral text-neutral-content tune-border border-neutral',
        'solid-info'      => 'bg-info text-info-content tune-border border-info',
        'solid-success'   => 'bg-success text-success-content tune-border border-success',
        'solid-warning'   => 'bg-warning text-warning-content tune-border border-warning',
        'solid-error'     => 'bg-error text-error-content tune-border border-error',

        // outline
        'outline-primary'   => 'bg-transparent text-primary tune-border border-primary',
        'outline-secondary' => 'bg-transparent text-secondary tune-border border-secondary',
        'outline-accent'    => 'bg-transparent text-accent tune-border border-accent',
        'outline-neutral'   => 'bg-transparent text-base-content tune-border border-base-content/10',
        'outline-info'      => 'bg-transparent text-info tune-border border-info',
        'outline-success'   => 'bg-transparent text-success tune-border border-success',
        'outline-warning'   => 'bg-transparent text-warning tune-border border-warning',
        'outline-error'     => 'bg-transparent text-error tune-border border-error',

        // base-100 / base-200 / base-300 — neutral surface bg, no colour on
        // the wrapper. Title / body classes apply text-base-content (mute);
        // icon picks up the colour via $iconColorClass.
        'base-100-primary', 'base-100-secondary', 'base-100-accent', 'base-100-neutral',
        'base-100-info', 'base-100-success', 'base-100-warning', 'base-100-error'
            => 'bg-base-100 tune-border border-base-content/10',

        'base-200-primary', 'base-200-secondary', 'base-200-accent', 'base-200-neutral',
        'base-200-info', 'base-200-success', 'base-200-warning', 'base-200-error'
            => 'bg-base-200 tune-border border-base-content/10',

        'base-300-primary', 'base-300-secondary', 'base-300-accent', 'base-300-neutral',
        'base-300-info', 'base-300-success', 'base-300-warning', 'base-300-error'
            => 'bg-base-300 tune-border border-base-content/10',

        // soft / vivid — share the same `bg-{color}/15 + border-{color}/40`
        // tint shell. The two only differ in the title / body text strategy
        // (set in $titleTextClass below). `soft` uses `text-base-content` for
        // calm, neutral readability; `vivid` uses a 20-% base-content / 80-%
        // hue mix for a saturated colour identity. Icon gets $iconColorClass
        // explicitly for both so it carries the accent.
        'soft-primary',  'vivid-primary'  => 'bg-primary/15 tune-border border-primary/40',
        'soft-secondary','vivid-secondary'=> 'bg-secondary/15 tune-border border-secondary/40',
        'soft-accent',   'vivid-accent'   => 'bg-accent/15 tune-border border-accent/40',
        'soft-neutral',  'vivid-neutral'  => 'bg-base-content/15 tune-border border-base-content/20',
        'soft-info',     'vivid-info'     => 'bg-info/15 tune-border border-info/40',
        'soft-success',  'vivid-success'  => 'bg-success/15 tune-border border-success/40',
        'soft-warning',  'vivid-warning'  => 'bg-warning/15 tune-border border-warning/40',
        'soft-error',    'vivid-error'    => 'bg-error/15 tune-border border-error/40',

        // ghost (transparent, colored text, no border)
        'ghost-primary'   => 'bg-transparent text-primary tune-border border-transparent',
        'ghost-secondary' => 'bg-transparent text-secondary tune-border border-transparent',
        'ghost-accent'    => 'bg-transparent text-accent tune-border border-transparent',
        'ghost-neutral'   => 'bg-transparent text-base-content tune-border border-transparent',
        'ghost-info'      => 'bg-transparent text-info tune-border border-transparent',
        'ghost-success'   => 'bg-transparent text-success tune-border border-transparent',
        'ghost-warning'   => 'bg-transparent text-warning tune-border border-transparent',
        'ghost-error'     => 'bg-transparent text-error tune-border border-transparent',

        // link (underlined colored text)
        'link-primary'   => 'bg-transparent text-primary underline underline-offset-2 tune-border border-transparent',
        'link-secondary' => 'bg-transparent text-secondary underline underline-offset-2 tune-border border-transparent',
        'link-accent'    => 'bg-transparent text-accent underline underline-offset-2 tune-border border-transparent',
        'link-neutral'   => 'bg-transparent text-base-content underline underline-offset-2 tune-border border-transparent',
        'link-info'      => 'bg-transparent text-info underline underline-offset-2 tune-border border-transparent',
        'link-success'   => 'bg-transparent text-success underline underline-offset-2 tune-border border-transparent',
        'link-warning'   => 'bg-transparent text-warning underline underline-offset-2 tune-border border-transparent',
        'link-error'     => 'bg-transparent text-error underline underline-offset-2 tune-border border-transparent',

        // bordered-top (surface bg + thick top accent bar)
        'bordered-top-primary'   => 'bg-base-100 text-base-content tune-border border-base-content/10 border-t-4 border-t-primary',
        'bordered-top-secondary' => 'bg-base-100 text-base-content tune-border border-base-content/10 border-t-4 border-t-secondary',
        'bordered-top-accent'    => 'bg-base-100 text-base-content tune-border border-base-content/10 border-t-4 border-t-accent',
        'bordered-top-neutral'   => 'bg-base-100 text-base-content tune-border border-base-content/10 border-t-4 border-t-neutral',
        'bordered-top-info'      => 'bg-base-100 text-base-content tune-border border-base-content/10 border-t-4 border-t-info',
        'bordered-top-success'   => 'bg-base-100 text-base-content tune-border border-base-content/10 border-t-4 border-t-success',
        'bordered-top-warning'   => 'bg-base-100 text-base-content tune-border border-base-content/10 border-t-4 border-t-warning',
        'bordered-top-error'     => 'bg-base-100 text-base-content tune-border border-base-content/10 border-t-4 border-t-error',

        // bordered-left (surface bg + thick left accent bar)
        'bordered-left-primary'   => 'bg-base-100 text-base-content tune-border border-base-content/10 border-l-4 border-l-primary',
        'bordered-left-secondary' => 'bg-base-100 text-base-content tune-border border-base-content/10 border-l-4 border-l-secondary',
        'bordered-left-accent'    => 'bg-base-100 text-base-content tune-border border-base-content/10 border-l-4 border-l-accent',
        'bordered-left-neutral'   => 'bg-base-100 text-base-content tune-border border-base-content/10 border-l-4 border-l-neutral',
        'bordered-left-info'      => 'bg-base-100 text-base-content tune-border border-base-content/10 border-l-4 border-l-info',
        'bordered-left-success'   => 'bg-base-100 text-base-content tune-border border-base-content/10 border-l-4 border-l-success',
        'bordered-left-warning'   => 'bg-base-100 text-base-content tune-border border-base-content/10 border-l-4 border-l-warning',
        'bordered-left-error'     => 'bg-base-100 text-base-content tune-border border-base-content/10 border-l-4 border-l-error',

        default => 'bg-info/15 text-info tune-border border-info/40',
    };

    $iconColorClass = match($color) {
        'primary'   => 'text-primary',
        'secondary' => 'text-secondary',
        'accent'    => 'text-accent',
        'neutral'   => 'text-neutral',
        'info'      => 'text-info',
        'success'   => 'text-success',
        'warning'   => 'text-warning',
        'error'     => 'text-error',
        default     => 'text-info',
    };

    // Tint-style appearances (soft / vivid / base-N) and bordered-* all need
    // an explicit icon colour because the wrapper doesn't carry one. solid /
    // outline / ghost / link inherit text-{color} from $variantClasses.
    $isTintLike = in_array($appearance, ['soft', 'vivid', 'base-100', 'base-200', 'base-300'], true);
    $iconWrapClasses = (str_starts_with($appearance, 'bordered') || $isTintLike)
        ? "shrink-0 mt-0.5 $iconColorClass"
        : 'shrink-0 mt-0.5';

    // Title + body text strategies for tint surfaces.
    //
    // soft / base-100 / base-200 / base-300 — Strategy B (`text-base-content`).
    //   Calm foreground readability; the *bg* + *icon* carry colour identity.
    //   Light theme primary/secondary used to read as invisible text-on-tint
    //   (`text-{color}-content` resolves to a ~93 % L pale-ish hue) — using
    //   foreground avoids that entirely.
    //
    // vivid — Strategy E (`color-mix({color}, base-content 20%)`). Saturated
    //   colour that flips per theme via base-content (dark anchor in light,
    //   light anchor in dark). Same hue family as the bg tint; "loud" tone.
    //
    // Body in either mode uses `opacity-80` for the title vs body hierarchy.
    if ($appearance === 'vivid') {
        $titleTextClass = match ($color) {
            'primary'   => 'text-[color-mix(in_oklch,var(--color-primary),var(--color-base-content)_20%)]',
            'secondary' => 'text-[color-mix(in_oklch,var(--color-secondary),var(--color-base-content)_20%)]',
            'accent'    => 'text-[color-mix(in_oklch,var(--color-accent),var(--color-base-content)_20%)]',
            'neutral'   => 'text-[color-mix(in_oklch,var(--color-neutral),var(--color-base-content)_20%)]',
            'info'      => 'text-[color-mix(in_oklch,var(--color-info),var(--color-base-content)_20%)]',
            'success'   => 'text-[color-mix(in_oklch,var(--color-success),var(--color-base-content)_20%)]',
            'warning'   => 'text-[color-mix(in_oklch,var(--color-warning),var(--color-base-content)_20%)]',
            'error'     => 'text-[color-mix(in_oklch,var(--color-error),var(--color-base-content)_20%)]',
            default     => 'text-[color-mix(in_oklch,var(--color-info),var(--color-base-content)_20%)]',
        };
    } elseif ($appearance === 'soft' || in_array($appearance, ['base-100', 'base-200', 'base-300'], true)) {
        $titleTextClass = 'text-base-content';
    } else {
        $titleTextClass = '';
    }
    $bodyTextClass = $titleTextClass ? "$titleTextClass opacity-80" : '';
@endphp

<div
    {{ $attributes->merge(['class' => "flex gap-inline rounded-[var(--radius-box)] $sizeClasses $variantClasses", 'role' => 'alert']) }}
    @if($dismissible) x-data="{ show: true }" x-show="show" x-transition @endif
>
    <div class="{{ $iconWrapClasses }}">
        <x-i :type="$iconType" variant="linear" class="w-[1.25em] h-[1.25em]" />
    </div>

    <div class="flex-1">
        @if($title)
            <p class="font-semibold {{ $titleTextClass }}">{{ $title }}</p>
        @endif
        <div class="{{ $title ? 'mt-1' : '' }} {{ $bodyTextClass }}">
            {{ $slot }}
        </div>
    </div>

    @if($dismissible)
        <button
            type="button"
            class="shrink-0 opacity-60 hover:opacity-100 transition-opacity cursor-pointer"
            @click="show = false"
            aria-label="Close"
        >
            {{-- Plain X close glyph. Solar icons only ship close-{circle,square}
                 variants — the user-facing close affordance reads better as
                 a bare X without a surrounding shape, so we inline a small SVG. --}}
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-[1em] h-[1em]" aria-hidden="true">
                <path d="M18 6L6 18" />
                <path d="M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
