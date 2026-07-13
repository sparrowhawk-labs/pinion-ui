<?php

declare(strict_types=1);

/**
 * Unit tests for EjectTransformer. Pure PHP (no Laravel) — run headless:
 *   php tests/Lint/eject-run.php   (or: composer lint)
 *
 * Two layers: (1) rule tests against a hand-written mini table (values are
 * deliberately odd to prove they flow through), (2) round-trip tests against
 * the REAL shipped eject-table.json — under default/md every t-shirt size is
 * exactly on the 4px grid, so eject(migrate(x)) must equal x.
 */

require_once dirname(__DIR__, 2) . '/src/Linting/ClassSegments.php';
require_once dirname(__DIR__, 2) . '/src/Linting/SpacingMigrator.php';
require_once dirname(__DIR__, 2) . '/src/Linting/EjectTransformer.php';

use SparrowhawkLabs\PinionUi\Linting\EjectTransformer;
use SparrowhawkLabs\PinionUi\Linting\SpacingMigrator;

$pass = 0;
$fail = 0;

function check(EjectTransformer $t, string $name, string $src, string $expectSrc, int $expectSkipped = 0): void
{
    global $pass, $fail;
    $r = $t->eject($src);
    if ($r['source'] === $expectSrc && count($r['skipped']) === $expectSkipped) {
        $pass++;
        echo "  PASS  $name\n";
    } else {
        $fail++;
        echo "  FAIL  $name\n";
        echo "        expected: $expectSrc\n";
        echo "        got:      {$r['source']}  (skipped: " . count($r['skipped']) . ")\n";
    }
}

/* ── layer 1: mini table with odd values ──────────────────────────────────── */
$tokens = [
    'spacing-3xs' => 2, 'spacing-2xs' => 4, 'spacing-xs' => 8, 'spacing-sm' => 12,
    'spacing-md' => 14, 'spacing-lg' => 20, 'spacing-xl' => 32, 'spacing-2xl' => 48,
    'spacing-3xl' => 64, 'spacing-4xl' => 64, 'spacing-5xl' => 96, 'spacing-6xl' => 128, 'spacing-7xl' => 160,
    'h-field-sm' => 32, 'h-field-md' => 36, 'h-field-lg' => 44, 'h-field-xs' => 28,
    'px-field-xs' => 10, 'px-field-sm' => 12, 'px-field-md' => 14, 'px-field-lg' => 20,
    'px-input-xs' => 8, 'px-input-sm' => 10, 'px-input-md' => 12, 'px-input-lg' => 14,
    'py-input-xs' => 4, 'py-input-sm' => 6, 'py-input-md' => 8, 'py-input-lg' => 10,
    'text-field-xs' => 12, 'text-field-sm' => 13, 'text-field-md' => 14, 'text-field-lg' => 16,
    'border' => 1, 'radius-box' => 8, 'radius-field' => 6, 'radius-selector' => 16,
    'shadow-box' => 'rgba(0, 0, 0, 0.06) 0px 1px 2px 0px', 'shadow-field' => 'none',
    // deliberately off-grid to prove quarter snapping
    'spacing-9xs-test' => 33.75,
];
$colors = ['primary' => '#e08814', 'primary-content' => '#ffffff', 'base-100' => '#fcfaf6', 'base-content' => '#131110', 'error' => '#cc2233'];
$t = new EjectTransformer($tokens, $colors);

// t-shirt spacing -> numeric (tune-reactive value, not the default ramp)
check($t, 'spacing md=14px -> p-3.5', '<div class="p-md">x</div>', '<div class="p-3.5">x</div>');
check($t, 'spacing lg=20px -> gap-5', '<div class="gap-lg mt-lg space-y-xs">x</div>', '<div class="gap-5 mt-5 space-y-2">x</div>');
check($t, 'variants + negative preserved', '<div class="hover:p-md md:-mt-lg">x</div>', '<div class="hover:p-3.5 md:-mt-5">x</div>');

// single-token tune utilities
check($t, 'h-field / text-field', '<div class="h-field-md text-field-md">x</div>', '<div class="h-9 text-[14px]">x</div>');
check($t, 'radius + tune-border', '<div class="radius-box tune-border">x</div>', '<div class="rounded-[8px] border">x</div>');

// compound expansion with variant distribution
check($t, 'tune-btn-md expands', '<button class="tune-btn-md">x</button>', '<button class="h-9 px-3.5 text-[14px]">x</button>');
check($t, 'tune-btn-xs uses text-field-sm', '<button class="tune-btn-xs">x</button>', '<button class="h-7 px-2.5 text-[13px]">x</button>');
check($t, 'variant distributes over expansion', '<button class="md:tune-card-pad">x</button>', '<button class="md:p-5">x</button>');
check($t, 'tune-textarea-md', '<textarea class="tune-textarea-md">x</textarea>', '<textarea class="px-3 py-2 text-[14px]">x</textarea>');

// var() arbitrary substitution + beautify
check($t, 'rounded-[var(--radius-box)]', '<div class="rounded-[var(--radius-box)]">x</div>', '<div class="rounded-[8px]">x</div>');
check($t, 'h-[var(--h-field-md)] snaps numeric', '<div class="h-[var(--h-field-md)]">x</div>', '<div class="h-9">x</div>');
check($t, 'shadow var escapes spaces', '<div class="shadow-[var(--shadow-box)]">x</div>', '<div class="shadow-[rgba(0,0,0,0.06)_0px_1px_2px_0px]">x</div>');
check($t, 'unknown var reported', '<div class="w-[var(--nope)]">x</div>', '<div class="w-[var(--nope)]">x</div>', 1);

// quarter snapping on off-grid values
check($t, 'off-grid 33.75px -> 8.5', '<div class="pt-[var(--spacing-9xs-test)]">x</div>', '<div class="pt-8.5">x</div>');

// theme colors, opacity preserved
check($t, 'bg-primary', '<div class="bg-primary text-primary-content">x</div>', '<div class="bg-[#e08814] text-[#ffffff]">x</div>');
check($t, 'opacity modifier', '<div class="bg-primary/10 border-base-content/60">x</div>', '<div class="bg-[#e08814]/10 border-[#131110]/60">x</div>');
check($t, 'longest name wins', '<span class="text-base-content bg-base-100">x</span>', '<span class="text-[#131110] bg-[#fcfaf6]">x</span>');
check($t, 'variant color', '<a class="hover:bg-error/20">x</a>', '<a class="hover:bg-[#cc2233]/20">x</a>');

// fonts + untouched vanilla
check($t, 'fonts reported not converted', '<h1 class="font-heading font-weight-heading">x</h1>', '<h1 class="font-heading font-weight-heading">x</h1>', 2);
check($t, 'vanilla passes through', '<div class="flex p-4 text-sm rounded-lg bg-white">x</div>', '<div class="flex p-4 text-sm rounded-lg bg-white">x</div>');
check($t, 'ignore marker', '<div class="p-md">x</div> {{-- pinion-lint-ignore --}}', '<div class="p-md">x</div> {{-- pinion-lint-ignore --}}');

// dynamic bindings
check($t, ':class ternary', '<a :class="$on ? \'bg-primary\' : \'p-md\'">x</a>', '<a :class="$on ? \'bg-[#e08814]\' : \'p-3.5\'">x</a>');
check($t, '@class array', "@class(['tune-btn-sm', 'gap-lg' => \$x])", "@class(['h-8 px-3 text-[13px]', 'gap-5' => \$x])");

/* ── layer 2: round-trip against the REAL table ──────────────────────────── */
$table = json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/src/resources/eject-table.json'), true);
$real = new EjectTransformer($table['tokens']['default|md'], $table['colors']['pinion']);
$migrator = new SpacingMigrator();

$vanilla = '<section class="py-20 space-y-6"><div class="p-4 gap-2 mt-8 mb-3 px-12"><span class="mt-0.5 -ml-1">x</span></div></section>';
$tshirt = $migrator->migrate($vanilla)['source'];
$back = $real->eject($tshirt)['source'];
if ($back === $vanilla) {
    $pass++;
    echo "  PASS  round-trip: eject(migrate(x)) == x under default|md\n";
} else {
    $fail++;
    echo "  FAIL  round-trip\n        original: $vanilla\n        t-shirt:  $tshirt\n        back:     $back\n";
}

// real-table spot values: tech|md compresses spacing
$tech = new EjectTransformer($table['tokens']['tech|md'], $table['colors']['pinion']);
$r = $tech->eject('<div class="p-md tune-btn-md">x</div>')['source'];
if ($r === '<div class="p-3.5 h-9 px-3 text-[13px]">x</div>') {
    $pass++;
    echo "  PASS  real table: tech|md p-md=14px, btn px 12px, text 13px\n";
} else {
    $fail++;
    echo "  FAIL  real table tech|md\n        got: $r\n";
}

echo "\n--- Eject transformer: $pass pass, $fail fail ---\n";
exit($fail > 0 ? 1 : 0);
