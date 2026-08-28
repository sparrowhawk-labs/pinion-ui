<?php

namespace SparrowhawkLabs\PinionUi\Compose;

class TableComposer
{
    public static function compose(array $props): array
    {
        $size  = $props['size'] ?? 'md';
        $card  = $props['card'] ?? true;
        $hover = $props['hover'] ?? true;

        // Vertical rhythm per density. Horizontal padding is uniform px-sm with
        // lg edge columns (see $table) so cell text aligns with an adjacent
        // x-card header/footer's p-lg content edge.
        [$thPy, $tdPy, $emptyPy] = match ($size) {
            'sm'    => ['py-xs', 'py-sm', 'py-lg'],
            'lg'    => ['py-sm', 'py-lg', 'py-2xl'],
            default => ['py-sm', 'py-md', 'py-xl'],
        };

        $text = $size === 'sm' ? 'text-xs' : 'text-sm';

        // card=true → the table owns its card face (same recipe as x-card default).
        // card=false → bare table, for embedding in an existing <x-card :padding="false">.
        $shell = $card
            ? 'rounded-[var(--radius-box)] tune-border border-base-content/10 bg-base-100 text-base-content overflow-x-auto'
            : 'overflow-x-auto';

        // First/last column reach the card edge with lg padding; inner columns
        // separate with px-sm. Selector-driven so consumer rows stay plain <tr>/<td>.
        $table = "w-full {$text} "
            . '[&_th:first-child]:pl-lg [&_th:last-child]:pr-lg '
            . '[&_td:first-child]:pl-lg [&_td:last-child]:pr-lg';

        // Head divider echoes the x-card section divider (tune-reactive width);
        // body rows use a fixed hairline so dense tables stay light in every tune.
        $headRow = 'border-b-[length:var(--border)] border-base-content/10 text-left text-xs text-base-content/45';

        $tbody = '[&>tr]:border-b [&>tr]:border-base-content/10 [&>tr:last-child]:border-b-0'
            . ($hover ? ' [&>tr]:transition-colors [&>tr:not(.pn-table-empty):hover]:bg-base-200/60' : '');

        return [
            'shell'   => $shell,
            'table'   => $table,
            'headRow' => $headRow,
            'tbody'   => $tbody,
            'th'      => "{$thPy} px-sm font-medium align-middle",
            'td'      => "{$tdPy} px-sm align-middle",
            'empty'   => "{$emptyPy} px-lg text-center",
        ];
    }
}
