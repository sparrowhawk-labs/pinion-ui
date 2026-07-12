@props([
    'color' => 'neutral',
    'appearance' => 'default',
    'padding' => true,
    'divider' => true,
    'hoverable' => false,
    'as' => 'div',
])

@php
    $base = 'rounded-[var(--radius-box)] tune-border overflow-hidden';

    $variantClasses = match("{$appearance}-{$color}") {
        // default: neutral surface, subtle border (color ignored)
        'default-primary', 'default-secondary', 'default-accent', 'default-neutral',
        'default-info', 'default-success', 'default-warning', 'default-error'
            => 'bg-base-100 text-base-content border-base-content/10',

        // elevated: surface + shadow, no border (color ignored)
        'elevated-primary', 'elevated-secondary', 'elevated-accent', 'elevated-neutral',
        'elevated-info', 'elevated-success', 'elevated-warning', 'elevated-error'
            => 'bg-base-100 text-base-content border-base-content/10 shadow-[var(--shadow-box)]',

        // filled: base-200 tinted, no border (color ignored)
        'filled-primary', 'filled-secondary', 'filled-accent', 'filled-neutral',
        'filled-info', 'filled-success', 'filled-warning', 'filled-error'
            => 'bg-base-200 text-base-content border-transparent',

        // ghost: fully transparent (color ignored)
        'ghost-primary', 'ghost-secondary', 'ghost-accent', 'ghost-neutral',
        'ghost-info', 'ghost-success', 'ghost-warning', 'ghost-error'
            => 'bg-transparent text-base-content border-transparent',

        // outline: transparent bg + colored border, base-content text
        'outline-primary'   => 'bg-transparent text-base-content border-primary',
        'outline-secondary' => 'bg-transparent text-base-content border-secondary',
        'outline-accent'    => 'bg-transparent text-base-content border-accent',
        'outline-neutral'   => 'bg-transparent text-base-content border-base-content/30',
        'outline-info'      => 'bg-transparent text-base-content border-info',
        'outline-success'   => 'bg-transparent text-base-content border-success',
        'outline-warning'   => 'bg-transparent text-base-content border-warning',
        'outline-error'     => 'bg-transparent text-base-content border-error',

        // soft: tinted bg + colored text + matching-tone border
        'soft-primary'   => 'bg-primary/10 text-primary border-primary/20',
        'soft-secondary' => 'bg-secondary/10 text-secondary border-secondary/20',
        'soft-accent'    => 'bg-accent/10 text-accent border-accent/20',
        'soft-neutral'   => 'bg-base-content/10 text-base-content border-base-content/20',
        'soft-info'      => 'bg-info/10 text-info border-info/20',
        'soft-success'   => 'bg-success/10 text-success border-success/20',
        'soft-warning'   => 'bg-warning/10 text-warning border-warning/20',
        'soft-error'     => 'bg-error/10 text-error border-error/20',

        // solid: filled with color + content text
        'solid-primary'   => 'bg-primary text-primary-content border-primary',
        'solid-secondary' => 'bg-secondary text-secondary-content border-secondary',
        'solid-accent'    => 'bg-accent text-accent-content border-accent',
        'solid-neutral'   => 'bg-neutral text-neutral-content border-neutral',
        'solid-info'      => 'bg-info text-info-content border-info',
        'solid-success'   => 'bg-success text-success-content border-success',
        'solid-warning'   => 'bg-warning text-warning-content border-warning',
        'solid-error'     => 'bg-error text-error-content border-error',

        // base-100: primary surface bg + colored text + base-content/10 border
        'base-100-primary'   => 'bg-base-100 text-primary border-base-content/10',
        'base-100-secondary' => 'bg-base-100 text-secondary border-base-content/10',
        'base-100-accent'    => 'bg-base-100 text-accent border-base-content/10',
        'base-100-neutral'   => 'bg-base-100 text-base-content border-base-content/10',
        'base-100-info'      => 'bg-base-100 text-info border-base-content/10',
        'base-100-success'   => 'bg-base-100 text-success border-base-content/10',
        'base-100-warning'   => 'bg-base-100 text-warning border-base-content/10',
        'base-100-error'     => 'bg-base-100 text-error border-base-content/10',

        // base-200: secondary surface bg
        'base-200-primary'   => 'bg-base-200 text-primary border-base-content/10',
        'base-200-secondary' => 'bg-base-200 text-secondary border-base-content/10',
        'base-200-accent'    => 'bg-base-200 text-accent border-base-content/10',
        'base-200-neutral'   => 'bg-base-200 text-base-content border-base-content/10',
        'base-200-info'      => 'bg-base-200 text-info border-base-content/10',
        'base-200-success'   => 'bg-base-200 text-success border-base-content/10',
        'base-200-warning'   => 'bg-base-200 text-warning border-base-content/10',
        'base-200-error'     => 'bg-base-200 text-error border-base-content/10',

        // base-300: tertiary surface bg
        'base-300-primary'   => 'bg-base-300 text-primary border-base-content/10',
        'base-300-secondary' => 'bg-base-300 text-secondary border-base-content/10',
        'base-300-accent'    => 'bg-base-300 text-accent border-base-content/10',
        'base-300-neutral'   => 'bg-base-300 text-base-content border-base-content/10',
        'base-300-info'      => 'bg-base-300 text-info border-base-content/10',
        'base-300-success'   => 'bg-base-300 text-success border-base-content/10',
        'base-300-warning'   => 'bg-base-300 text-warning border-base-content/10',
        'base-300-error'     => 'bg-base-300 text-error border-base-content/10',

        // bordered-top: surface + thick top accent bar
        'bordered-top-primary'   => 'bg-base-100 text-base-content border-base-content/10 border-t-4 border-t-primary',
        'bordered-top-secondary' => 'bg-base-100 text-base-content border-base-content/10 border-t-4 border-t-secondary',
        'bordered-top-accent'    => 'bg-base-100 text-base-content border-base-content/10 border-t-4 border-t-accent',
        'bordered-top-neutral'   => 'bg-base-100 text-base-content border-base-content/10 border-t-4 border-t-neutral',
        'bordered-top-info'      => 'bg-base-100 text-base-content border-base-content/10 border-t-4 border-t-info',
        'bordered-top-success'   => 'bg-base-100 text-base-content border-base-content/10 border-t-4 border-t-success',
        'bordered-top-warning'   => 'bg-base-100 text-base-content border-base-content/10 border-t-4 border-t-warning',
        'bordered-top-error'     => 'bg-base-100 text-base-content border-base-content/10 border-t-4 border-t-error',

        default => 'bg-base-100 text-base-content border-base-content/10',
    };

    $hover = $hoverable ? 'transition-shadow hover:shadow-[var(--shadow-box)] cursor-pointer' : '';

    // Two layout modes:
    //  divider=true  → each section owns p-lg, hr sits between sections (outerPad is empty)
    //  divider=false → single outer p-lg, sections flow with text-gap between them
    $outerPad   = (!$divider && $padding) ? 'p-lg' : '';
    $sectionPad = ($divider && $padding) ? 'p-lg' : '';
    $headerDivider = $divider ? 'border-b-[length:var(--border)] border-base-content/10' : '';
    $footerDivider = $divider ? 'border-t-[length:var(--border)] border-base-content/10' : '';
    $headerGap = !$divider ? 'mb-md' : '';
    $footerGap = !$divider ? 'mt-md' : '';

    // Full-bleed divider fix.
    //   The card root is a border-box with a `tune-border` of width var(--border)
    //   (brutal = 2.5px). Its direct children's content box is therefore inset
    //   from the card OUTER edge by one border width on each inline side. A
    //   header/footer's `border-b` / `border-t` spans only that inset width, so
    //   the divider line stops var(--border) short of each side border and reads
    //   as NARROWER than the card.
    //   Fix: on the bordered sections only, pull the inline edges back out by
    //   var(--border) with negative inline margin so the section (and thus its
    //   border line) reaches flush to the card's inner border face, then add the
    //   same var(--border) back to inline padding so the *content* doesn't move.
    //   Works with overflow-hidden + rounded corners (the root still clips) and
    //   scales with the tune border width automatically.
    $dividerBleed = $divider
        ? ($padding
            // padded: keep vertical p-lg, widen inline padding by the border
            ? '-mx-[var(--border)] py-lg px-[calc(var(--spacing-lg)+var(--border))]'
            // unpadded: just restore the content position lost to the pull-out
            : '-mx-[var(--border)] px-[var(--border)]')
        : '';
@endphp

<{{ $as }} {{ $attributes->merge(['class' => "$base $variantClasses $hover $outerPad flex flex-col"]) }}>
    @if(isset($header))
        <div class="{{ $divider ? $dividerBleed : $sectionPad }} {{ $headerDivider }} {{ $headerGap }}">
            {{ $header }}
        </div>
    @endif

    <div class="{{ $divider ? $dividerBleed : $sectionPad }} flex-1">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="{{ $divider ? $dividerBleed : $sectionPad }} {{ $footerDivider }} {{ $footerGap }}">
            {{ $footer }}
        </div>
    @endif
</{{ $as }}>
