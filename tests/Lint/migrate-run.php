<?php

declare(strict_types=1);

/**
 * Unit tests for SpacingMigrator. Pure PHP (no Laravel) — run headless:
 *   php tests/Lint/migrate-run.php   (or: composer lint)
 *
 * The hard parts: log-space nearest mapping (linear distance ties constantly
 * on the near-geometric ramp — p-5=20px between md 16 / lg 24), the x1.5 cap
 * (p-64=256px must NOT collapse to 7xl=160px), offset-precise rewriting
 * inside dynamic bindings, and leaving optical escapes (px / 0 / arbitrary /
 * ignore-marked lines) and width-family utilities untouched.
 */

require_once dirname(__DIR__, 2) . '/src/Linting/ClassSegments.php';
require_once dirname(__DIR__, 2) . '/src/Linting/SpacingMigrator.php';

use SparrowhawkLabs\PinionUi\Linting\SpacingMigrator;

$m = new SpacingMigrator();
$pass = 0;
$fail = 0;

function check(SpacingMigrator $m, string $name, string $src, string $expectSrc, int $expectChanges = -1, int $expectSkipped = 0): void
{
    global $pass, $fail;
    $r = $m->migrate($src);
    $ok = $r['source'] === $expectSrc
        && ($expectChanges < 0 || count($r['changes']) === $expectChanges)
        && count($r['skipped']) === $expectSkipped;
    if ($ok) {
        $pass++;
        echo "  PASS  $name\n";
    } else {
        $fail++;
        echo "  FAIL  $name\n";
        echo "        expected: $expectSrc\n";
        echo "        got:      {$r['source']}\n";
        echo '        changes: ' . count($r['changes']) . " skipped: " . count($r['skipped']) . "\n";
    }
}

// ── exact matches (the 13-point mapping) ────────────────────────────────────
check($m, 'exact: p-4 -> p-md', '<div class="p-4">x</div>', '<div class="p-md">x</div>', 1);
check($m, 'exact: full ladder',
    '<div class="p-0.5 p-1 p-2 p-3 p-4 p-6 p-8 p-12 p-16 p-20 p-24 p-32 p-40">x</div>',
    '<div class="p-3xs p-2xs p-xs p-sm p-md p-lg p-xl p-2xl p-3xl p-4xl p-5xl p-6xl p-7xl">x</div>', 13);

// ── log-space nearest (all of these tie linearly) ───────────────────────────
check($m, 'p-5 (20px) -> lg not md', '<div class="p-5">x</div>', '<div class="p-lg">x</div>', 1);
check($m, 'gap-1.5 (6px) -> xs not 2xs', '<div class="gap-1.5">x</div>', '<div class="gap-xs">x</div>', 1);
check($m, 'py-10 (40px) -> 2xl not xl', '<div class="py-10">x</div>', '<div class="py-2xl">x</div>', 1);
check($m, 'mt-2.5 (10px) -> sm not xs', '<div class="mt-2.5">x</div>', '<div class="mt-sm">x</div>', 1);
check($m, 'p-14 (56px) -> 3xl not 2xl', '<div class="p-14">x</div>', '<div class="p-3xl">x</div>', 1);
check($m, 'p-7 (28px, above geomean 27.71) -> xl', '<div class="p-7">x</div>', '<div class="p-xl">x</div>', 1);
check($m, 'p-9 (36px, below geomean 39.19) -> xl', '<div class="p-9">x</div>', '<div class="p-xl">x</div>', 1);

// ── x1.5 cap: report, do not convert ────────────────────────────────────────
check($m, 'p-64 (256px) skipped', '<div class="p-64">x</div>', '<div class="p-64">x</div>', 0, 1);
check($m, 'p-96 (384px) skipped', '<div class="p-96">x</div>', '<div class="p-96">x</div>', 0, 1);
check($m, 'p-56 (224px) still converts to 7xl', '<div class="p-56">x</div>', '<div class="p-7xl">x</div>', 1);

// ── variants / important / negative preserved ───────────────────────────────
check($m, 'variants', '<div class="hover:p-4 md:lg:gap-2">x</div>', '<div class="hover:p-md md:lg:gap-xs">x</div>', 2);
check($m, 'negative margin', '<div class="-mt-1 md:-mx-6">x</div>', '<div class="-mt-2xs md:-mx-lg">x</div>', 2);
check($m, 'important v3 leading', '<div class="!p-4">x</div>', '<div class="!p-md">x</div>', 1);
check($m, 'important v4 trailing', '<div class="p-4!">x</div>', '<div class="p-md!">x</div>', 1);

// ── untouched: optical escapes and non-magnitudes ───────────────────────────
check($m, 'px / 0 / auto / reverse / arbitrary untouched',
    '<div class="p-px p-0 m-auto space-y-reverse mt-[3px]">x</div>',
    '<div class="p-px p-0 m-auto space-y-reverse mt-[3px]">x</div>', 0);
check($m, 't-shirt already rhythmic', '<div class="p-md gap-lg">x</div>', '<div class="p-md gap-lg">x</div>', 0);
check($m, 'width family untouched', '<div class="w-4 max-w-6 min-w-12 basis-8 size-16 h-4">x</div>',
    '<div class="w-4 max-w-6 min-w-12 basis-8 size-16 h-4">x</div>', 0);
check($m, 'blade interpolation untouched', '<div class="{{ $pad }} p-4">x</div>', '<div class="{{ $pad }} p-md">x</div>', 1);

// ── ignore marker (same line / line above) ──────────────────────────────────
check($m, 'ignore marker same line', '<div class="p-4">x</div> {{-- pinion-lint-ignore --}}', '<div class="p-4">x</div> {{-- pinion-lint-ignore --}}', 0);
check($m, 'ignore marker line above', "{{-- pinion-lint-ignore --}}\n<div class=\"p-4\">x</div>", "{{-- pinion-lint-ignore --}}\n<div class=\"p-4\">x</div>", 0);
check($m, 'ignore is line-scoped', "{{-- pinion-lint-ignore --}}\n<div class=\"p-4\">x</div>\n\n<div class=\"p-6\">y</div>", "{{-- pinion-lint-ignore --}}\n<div class=\"p-4\">x</div>\n\n<div class=\"p-lg\">y</div>", 1);

// ── dynamic bindings rewritten in place ─────────────────────────────────────
check($m, ':class ternary', '<a :class="$open ? \'p-4 font-bold\' : \'p-2\'">x</a>', '<a :class="$open ? \'p-md font-bold\' : \'p-xs\'">x</a>', 2);
check($m, '@class array', "@class(['gap-2', 'mt-6' => \$x])", "@class(['gap-xs', 'mt-lg' => \$x])", 2);
check($m, 'x-bind:class', '<div x-bind:class="active ? \'py-8\' : \'py-2\'">x</div>', '<div x-bind:class="active ? \'py-xl\' : \'py-xs\'">x</div>', 2);

// ── offset integrity: many edits in one file, lengths differ ────────────────
check($m, 'multiple edits keep alignment',
    "<div class=\"p-4 gap-2\">\n  <span class=\"mt-1.5 mb-12\">a</span>\n  <p class=\"px-20\">b</p>\n</div>",
    "<div class=\"p-md gap-xs\">\n  <span class=\"mt-xs mb-2xl\">a</span>\n  <p class=\"px-4xl\">b</p>\n</div>", 5);

// ── multibyte content around class strings ──────────────────────────────────
check($m, 'multibyte safe', '<p class="p-4">日本語テキスト</p><div class="mt-8">見出し</div>', '<p class="p-md">日本語テキスト</p><div class="mt-xl">見出し</div>', 2);

echo "\n--- Spacing migrator: $pass pass, $fail fail ---\n";
exit($fail > 0 ? 1 : 0);
