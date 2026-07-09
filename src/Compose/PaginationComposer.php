<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class PaginationComposer
{
    public static function compose(array $props): array
    {
        $current     = max(1, (int) ($props['current'] ?? 1));
        $last        = max(1, (int) ($props['last'] ?? 1));
        $onEachSide  = max(1, (int) ($props['onEachSide'] ?? 1));
        $size        = $props['size'] ?? 'md';
        $color       = $props['color'] ?? 'primary';
        $appearance  = $props['appearance'] ?? 'soft';

        $window = self::windowPages($current, $last, $onEachSide);

        return [
            'wrapper'      => 'flex flex-col sm:flex-row items-center justify-between gap-4 py-4 w-full',
            'wrapperSimple'=> 'inline-flex flex-col items-center gap-3',
            'nav'          => 'flex shadow-[var(--shadow-box)]',
            'itemBase'     => self::itemBase($size),
            'itemActive'   => self::itemActive($appearance, $color),
            'itemIdle'     => self::itemIdle(),
            'itemDisabled' => self::itemDisabled(),
            'itemStatic'   => self::itemStatic(),
            'infoText'     => 'text-sm text-base-content/60',
            'pages'        => implode(',', $window['pages']),
            'showFirst'    => $window['showFirst'] ? '1' : '0',
            'showLast'     => $window['showLast'] ? '1' : '0',
            'showDotsLeft' => $window['showDotsLeft'] ? '1' : '0',
            'showDotsRight'=> $window['showDotsRight'] ? '1' : '0',
        ];
    }

    public static function windowPages(int $current, int $last, int $onEachSide = 1): array
    {
        $current    = max(1, $current);
        $last       = max(1, $last);
        $onEachSide = max(1, $onEachSide);

        $start = max(1, $current - $onEachSide);
        $end   = min($last, $current + $onEachSide);

        return [
            'start'         => $start,
            'end'           => $end,
            'pages'         => range($start, $end),
            'showFirst'     => $start > 1,
            'showLast'      => $end < $last,
            'showDotsLeft'  => $start > 2,
            'showDotsRight' => $end < $last - 1,
        ];
    }

    public static function buildUrl(?string $baseUrl, int $page, string $pageParam = 'page', bool $preserveQuery = true, array $existingQuery = []): string
    {
        if (! $baseUrl) {
            return '#';
        }
        $query = $preserveQuery ? $existingQuery : [];
        $query[$pageParam] = $page;
        return $baseUrl.'?'.http_build_query($query);
    }

    private static function itemBase(string $size): string
    {
        return FieldVariants::join(
            'rounded-none first:rounded-l-[var(--radius-field)] last:rounded-r-[var(--radius-field)]',
            'inline-flex items-center justify-center font-medium transition-colors',
            'border-[length:var(--border)] border-l-0 first:border-l',
            'focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1',
            self::sizeClasses($size),
        );
    }

    private static function sizeClasses(string $size): string
    {
        return match ($size) {
            'sm' => 'h-[var(--h-field-sm)] min-w-[var(--h-field-sm)] px-[var(--px-field-sm)] text-[length:var(--text-field-sm)] gap-1',
            'lg' => 'h-[var(--h-field-lg)] min-w-[var(--h-field-lg)] px-[var(--px-field-lg)] text-[length:var(--text-field-lg)] gap-2.5',
            default => 'h-[var(--h-field-md)] min-w-[var(--h-field-md)] px-[var(--px-field-md)] text-[length:var(--text-field-md)] gap-2',
        };
    }

    private static function itemActive(string $appearance, string $color): string
    {
        return match ("{$appearance}-{$color}") {
            'solid-primary'   => 'bg-primary text-primary-content border-primary focus-visible:ring-primary cursor-default relative z-10',
            'solid-secondary' => 'bg-secondary text-secondary-content border-secondary focus-visible:ring-secondary cursor-default relative z-10',
            'solid-accent'    => 'bg-accent text-accent-content border-accent focus-visible:ring-accent cursor-default relative z-10',
            'solid-neutral'   => 'bg-neutral text-neutral-content border-neutral focus-visible:ring-neutral cursor-default relative z-10',
            'solid-info'      => 'bg-info text-info-content border-info focus-visible:ring-info cursor-default relative z-10',
            'solid-success'   => 'bg-success text-success-content border-success focus-visible:ring-success cursor-default relative z-10',
            'solid-warning'   => 'bg-warning text-warning-content border-warning focus-visible:ring-warning cursor-default relative z-10',
            'solid-error'     => 'bg-error text-error-content border-error focus-visible:ring-error cursor-default relative z-10',

            'outline-primary'   => 'bg-base-100 text-primary border-primary focus-visible:ring-primary cursor-default relative z-10',
            'outline-secondary' => 'bg-base-100 text-secondary border-secondary focus-visible:ring-secondary cursor-default relative z-10',
            'outline-accent'    => 'bg-base-100 text-accent border-accent focus-visible:ring-accent cursor-default relative z-10',
            'outline-neutral'   => 'bg-base-100 text-base-content border-base-content focus-visible:ring-base-content cursor-default relative z-10',
            'outline-info'      => 'bg-base-100 text-info border-info focus-visible:ring-info cursor-default relative z-10',
            'outline-success'   => 'bg-base-100 text-success border-success focus-visible:ring-success cursor-default relative z-10',
            'outline-warning'   => 'bg-base-100 text-warning border-warning focus-visible:ring-warning cursor-default relative z-10',
            'outline-error'     => 'bg-base-100 text-error border-error focus-visible:ring-error cursor-default relative z-10',

            'soft-primary'   => 'bg-primary/15 text-primary border-primary/40 focus-visible:ring-primary cursor-default relative z-10',
            'soft-secondary' => 'bg-secondary/15 text-secondary border-secondary/40 focus-visible:ring-secondary cursor-default relative z-10',
            'soft-accent'    => 'bg-accent/15 text-accent border-accent/40 focus-visible:ring-accent cursor-default relative z-10',
            'soft-neutral'   => 'bg-base-content/15 text-base-content border-base-content/20 focus-visible:ring-base-content cursor-default relative z-10',
            'soft-info'      => 'bg-info/15 text-info border-info/40 focus-visible:ring-info cursor-default relative z-10',
            'soft-success'   => 'bg-success/15 text-success border-success/40 focus-visible:ring-success cursor-default relative z-10',
            'soft-warning'   => 'bg-warning/15 text-warning border-warning/40 focus-visible:ring-warning cursor-default relative z-10',
            'soft-error'     => 'bg-error/15 text-error border-error/40 focus-visible:ring-error cursor-default relative z-10',

            default => 'bg-primary/15 text-primary border-primary/40 focus-visible:ring-primary cursor-default relative z-10',
        };
    }

    private static function itemIdle(): string
    {
        return 'bg-base-100 text-base-content border-base-content/10 hover:bg-base-200 focus-visible:ring-base-content cursor-pointer';
    }

    private static function itemDisabled(): string
    {
        return 'bg-base-100 text-base-content/30 border-base-content/10 cursor-not-allowed pointer-events-none';
    }

    private static function itemStatic(): string
    {
        return 'bg-base-100 text-base-content/50 border-base-content/10 cursor-default pointer-events-none';
    }
}
