<?php

declare(strict_types=1);

/**
 * Unit tests for SpacingUsageScanner. Pure PHP (no Laravel) — run headless:
 *   php tests/Lint/spacing-run.php   (or: composer lint)
 *
 * The hard part is NOT counting width-family utilities (w-md, max-w-6xl,
 * basis-lg, size-md) that share t-shirt suffixes but resolve the CONTAINER
 * scale, and skipping magnitude-free suffixes (auto, reverse, 0). Those
 * negatives are the bulk of the cases below.
 */

require_once dirname(__DIR__, 2) . '/src/Linting/ClassVocabularyLinter.php';
require_once dirname(__DIR__, 2) . '/src/Linting/SpacingUsageScanner.php';

use SparrowhawkLabs\PinionUi\Linting\SpacingUsageScanner;

$scanner = new SpacingUsageScanner();
$pass = 0;
$fail = 0;

/** @param array<string,string> $expect token => kind */
function check(SpacingUsageScanner $scanner, string $name, string $src, array $expect): void
{
    global $pass, $fail;
    $got = [];
    foreach ($scanner->scan($src) as $o) {
        $got[$o['token']] = $o['kind'];
    }
    ksort($got);
    ksort($expect);
    if ($got === $expect) {
        $pass++;
        echo "  PASS  $name\n";
    } else {
        $fail++;
        echo "  FAIL  $name\n";
        echo '        expected: ' . json_encode($expect) . "\n";
        echo '        got:      ' . json_encode($got) . "\n";
    }
}

// ── rhythmic: t-shirt suffixes on all four spacing families ─────────────────
check($scanner, 'padding t-shirt', '<div class="p-md px-lg py-4xl pt-2xs">x</div>',
    ['p-md' => 'rhythmic', 'px-lg' => 'rhythmic', 'py-4xl' => 'rhythmic', 'pt-2xs' => 'rhythmic']);
check($scanner, 'margin + gap + space t-shirt', '<div class="mt-xl gap-lg gap-x-sm space-y-3xs">x</div>',
    ['mt-xl' => 'rhythmic', 'gap-lg' => 'rhythmic', 'gap-x-sm' => 'rhythmic', 'space-y-3xs' => 'rhythmic']);
check($scanner, 'logical props + negative margin', '<div class="ps-md pe-lg -mt-sm -mx-2xl">x</div>',
    ['ps-md' => 'rhythmic', 'pe-lg' => 'rhythmic', '-mt-sm' => 'rhythmic', '-mx-2xl' => 'rhythmic']);
check($scanner, 'variant prefixes counted', '<div class="hover:p-lg md:gap-xl dark:lg:space-y-md">x</div>',
    ['hover:p-lg' => 'rhythmic', 'md:gap-xl' => 'rhythmic', 'dark:lg:space-y-md' => 'rhythmic']);

// ── optical: numeric and arbitrary ──────────────────────────────────────────
check($scanner, 'numeric', '<div class="p-4 mt-1.5 gap-2 space-y-8">x</div>',
    ['p-4' => 'numeric', 'mt-1.5' => 'numeric', 'gap-2' => 'numeric', 'space-y-8' => 'numeric']);
check($scanner, 'px keyword is numeric', '<div class="p-px mt-px">x</div>',
    ['p-px' => 'numeric', 'mt-px' => 'numeric']);
check($scanner, 'arbitrary', '<div class="mt-[3px] p-[0.4rem]">x</div>',
    ['mt-[3px]' => 'arbitrary', 'p-[0.4rem]' => 'arbitrary']);
check($scanner, 'negative numeric', '<div class="-mt-1 -space-y-2">x</div>',
    ['-mt-1' => 'numeric', '-space-y-2' => 'numeric']);

// ── NEGATIVE: width family shares t-shirt names but is CONTAINER scale ──────
check($scanner, 'width family never counted', '<div class="w-md max-w-6xl min-w-sm basis-lg size-md w-4 max-w-[10rem]">x</div>', []);

// ── NEGATIVE: magnitude-free suffixes ───────────────────────────────────────
check($scanner, 'zero / auto / reverse skipped', '<div class="p-0 m-auto mx-auto space-y-reverse gap-0">x</div>', []);

// ── NEGATIVE: non-spacing utilities with colliding prefixes ─────────────────
check($scanner, 'prefix collisions', '<div class="placeholder-gray-500 menu-vertical mask-squircle text-sm pointer-events-none">x</div>', []);

// ── NEGATIVE: Blade interpolation skipped, static part kept ─────────────────
check($scanner, 'blade interpolation', '<div class="{{ $classes }} p-md">x</div>', ['p-md' => 'rhythmic']);

// ── dynamic bindings go through the shared extractor ────────────────────────
check($scanner, ':class ternary', '<a :class="$open ? \'p-lg\' : \'p-2\'">x</a>',
    ['p-lg' => 'rhythmic', 'p-2' => 'numeric']);
check($scanner, '@class array', '@class([\'gap-md\', \'mt-1\' => $nudge])',
    ['gap-md' => 'rhythmic', 'mt-1' => 'numeric']);

// ── mixed: one of each kind ─────────────────────────────────────────────────
check($scanner, 'mixed kinds', '<section class="py-4xl space-y-lg mt-1 pb-[2px]">x</section>',
    ['py-4xl' => 'rhythmic', 'space-y-lg' => 'rhythmic', 'mt-1' => 'numeric', 'pb-[2px]' => 'arbitrary']);

echo "\n--- Spacing scanner: $pass pass, $fail fail ---\n";
exit($fail > 0 ? 1 : 0);
