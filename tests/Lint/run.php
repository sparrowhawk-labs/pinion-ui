<?php

declare(strict_types=1);

/**
 * Unit tests for ClassVocabularyLinter. Pure PHP (no Laravel) — run headless:
 *   php tests/Lint/run.php   (or: composer lint)
 *
 * Each case asserts the exact multiset of flagged tokens. The hard part of this
 * linter is NOT flagging Tailwind utilities that share a prefix with an excluded
 * daisyUI part (select-none, list-disc, table-auto, collapse, filter) and not
 * flagging KEPT daisyUI parts (progress, timeline, join, ...). Those negatives
 * are the bulk of the cases below.
 */

require_once dirname(__DIR__, 2) . '/src/Linting/ClassVocabularyLinter.php';

use SparrowhawkLabs\PinionUi\Linting\ClassVocabularyLinter;

$linter = new ClassVocabularyLinter();
$pass = 0;
$fail = 0;

/** @param string[] $expectTokens */
function check(ClassVocabularyLinter $linter, string $name, string $src, array $expectTokens): void
{
    global $pass, $fail;
    $got = array_map(fn ($v) => $v['token'], $linter->lint($src));
    sort($got);
    sort($expectTokens);
    if ($got === $expectTokens) {
        $pass++;
        echo "  PASS  $name\n";
    } else {
        $fail++;
        echo "  FAIL  $name\n";
        echo '        expected: [' . implode(', ', $expectTokens) . "]\n";
        echo '        got:      [' . implode(', ', $got) . "]\n";
    }
}

// ── POSITIVE: daisyUI component classes (excluded → no-op) ──────────────────
check($linter, 'btn root + modifier', '<button class="btn btn-primary">x</button>', ['btn', 'btn-primary']);
check($linter, 'badge', '<span class="badge badge-error">x</span>', ['badge', 'badge-error']);
check($linter, 'card parts', '<div class="card card-body card-bordered">x</div>', ['card', 'card-body', 'card-bordered']);
check($linter, 'input', '<input class="input input-bordered">', ['input', 'input-bordered']);
check($linter, 'alert', '<div class="alert alert-warning">x</div>', ['alert', 'alert-warning']);
check($linter, 'menu', '<ul class="menu menu-vertical">x</ul>', ['menu', 'menu-vertical']);
check($linter, 'modal', '<div class="modal modal-box">x</div>', ['modal', 'modal-box']);
check($linter, 'tabs + tab', '<div class="tabs"><a class="tab tab-active">x</a></div>', ['tabs', 'tab', 'tab-active']);
check($linter, 'file-input', '<input class="file-input file-input-bordered">', ['file-input', 'file-input-bordered']);
check($linter, 'radial-progress', '<div class="radial-progress">70</div>', ['radial-progress']);
check($linter, 'legacy btn-group', '<div class="btn-group">x</div>', ['btn-group']);

// ── POSITIVE: select (bare + daisyUI modifier, NOT the Tailwind ones) ───────
check($linter, 'select daisyUI', '<select class="select select-bordered select-sm">x</select>', ['select', 'select-bordered', 'select-sm']);

// ── POSITIVE: compound-only words ──────────────────────────────────────────
check($linter, 'hero-content only', '<div class="hero hero-content">x</div>', ['hero-content']);
check($linter, 'table-zebra only', '<table class="table table-zebra">x</table>', ['table-zebra']);
check($linter, 'list-row only', '<ul class="list list-row">x</ul>', ['list-row']);

// ── POSITIVE: fixed / hex colors ───────────────────────────────────────────
check($linter, 'palette colors', '<div class="bg-blue-500 text-red-700 border-gray-200">x</div>', ['bg-blue-500', 'text-red-700', 'border-gray-200']);
check($linter, 'hex colors', '<div class="bg-[#1d4ed8] text-[#fff]">x</div>', ['bg-[#1d4ed8]', 'text-[#fff]']);
check($linter, 'palette w/ opacity', '<div class="bg-rose-500/30">x</div>', ['bg-rose-500/30']);
check($linter, 'neutral numbered is Tailwind', '<div class="bg-neutral-500">x</div>', ['bg-neutral-500']);
check($linter, 'gradient from-', '<div class="from-blue-500 to-emerald-600">x</div>', ['from-blue-500', 'to-emerald-600']);

// ── POSITIVE: variants strip correctly ─────────────────────────────────────
check($linter, 'variant prefixes', '<button class="hover:btn-primary md:bg-blue-500 dark:lg:text-red-600">x</button>', ['hover:btn-primary', 'md:bg-blue-500', 'dark:lg:text-red-600']);

// ── POSITIVE: dynamic bindings ─────────────────────────────────────────────
check($linter, ':class ternary', '<a :class="$active ? \'btn-primary\' : \'btn-ghost\'">x</a>', ['btn-primary', 'btn-ghost']);
check($linter, '@class array', '@class([\'badge\', \'badge-error\' => $hasError])', ['badge', 'badge-error']);

// ── NEGATIVE: plain Tailwind is fine ───────────────────────────────────────
check($linter, 'plain utilities', '<div class="flex items-center gap-2 px-3 py-1.5 text-sm font-semibold rounded-md">x</div>', []);
check($linter, 'semantic colors', '<div class="bg-primary text-primary-content bg-base-200 text-base-content border-base-300 text-error bg-success/10 bg-neutral text-neutral-content">x</div>', []);
check($linter, 'tune classes & tokens', '<div class="tune-border tune-btn-md tune-card-pad rounded-[var(--radius-box)] h-[var(--h-field-md)]">x</div>', []);
check($linter, 'text-base font size', '<p class="text-base text-balance">x</p>', []);

// ── NEGATIVE: Tailwind utilities that collide with excluded daisyUI parts ───
check($linter, 'select-none etc (Tailwind)', '<div class="select-none select-text select-all select-auto">x</div>', []);
check($linter, 'list-* (Tailwind)', '<ul class="list-disc list-inside list-decimal">x</ul>', []);
check($linter, 'table-auto/fixed (Tailwind)', '<table class="table table-auto table-fixed">x</table>', []);
check($linter, 'collapse + filter (Tailwind/kept)', '<div class="collapse filter backdrop-blur">x</div>', []);

// ── NEGATIVE: KEPT daisyUI parts (pinion-ui components depend on them) ──────
check($linter, 'kept parts', '<div class="progress timeline join stat indicator avatar divider kbd loading skeleton range mask rating breadcrumbs">x</div>', []);
check($linter, 'progress-primary kept', '<progress class="progress progress-primary">x</progress>', []);

// ── NEGATIVE: bare common words (compound-only roots, not flagged bare) ─────
check($linter, 'bare common words', '<div class="hero footer link label list table status">x</div>', []);

// ── NEGATIVE: fully dynamic / Blade interpolation ──────────────────────────
check($linter, 'blade interpolation', '<div class="{{ $classes }}">x</div>', []);
check($linter, 'partial dynamic keeps static', '<button class="btn {{ $extra }}">x</button>', ['btn']);

// ── NEGATIVE: ignore marker suppresses ─────────────────────────────────────
check($linter, 'ignore marker (line above)', "{{-- pinion-lint-ignore --}}\n<button class=\"btn btn-primary\">x</button>", []);
check($linter, 'ignore marker (same line)', '<button class="btn btn-primary">x</button> {{-- pinion-lint-ignore --}}', []);

// ── data-theme × data-tune cascade root (<html>) ───────────────────────────
check($linter, 'html missing both attrs', '<html lang="ja"><head></head><body></body></html>', ['data-theme', 'data-tune']);
check($linter, 'html missing data-tune only', '<html lang="ja" data-theme="pinion"><body></body></html>', ['data-tune']);
check($linter, 'html missing data-theme only', '<html data-tune="default"><body></body></html>', ['data-theme']);
check($linter, 'html with both → clean', '<html lang="ja" data-theme="pinion" data-tune="default"><body></body></html>', []);
check($linter, 'non-layout file is unaffected', '<div class="flex p-4"><x-button>x</x-button></div>', []);
check($linter, 'html ignore marker suppresses', '<html lang="ja"> {{-- pinion-lint-ignore --}}', []);
// Blade expr with `->` (its `>`) in an <html> attr must NOT truncate the tag scan.
check($linter, 'html Blade -> in attr, both present → clean', '<html lang="{{ app()->getLocale() }}" data-theme="pinion" data-tune="default"></html>', []);
check($linter, 'html Blade -> in attr, missing tune', '<html lang="{{ app()->getLocale() }}" data-theme="pinion"></html>', ['data-tune']);

echo "\n--- Lint linter: $pass pass, $fail fail ---\n";
exit($fail > 0 ? 1 : 0);
