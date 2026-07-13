<?php

namespace SparrowhawkLabs\PinionUi\Linting;

/**
 * SpacingMigrator — rewrites optical (numeric) spacing utilities to the
 * nearest rhythmic t-shirt size, backing `ui:spacing-migrate`.
 *
 * This is the deliberate counterpart to the census (SpacingUsageScanner):
 * enforcement of rhythmic-vs-optical was cancelled because intent is not
 * machine-judgeable, but a *conversion* is explicitly invoked by a human who
 * reviews the diff — so "p-4 -> p-md" is a migration step, not a judgment.
 * Typical use: adopt pinion-ui on an existing Tailwind codebase, bulk-convert,
 * then re-opticalize the few genuine nudges the diff review surfaces.
 *
 * Mapping: a numeric suffix n means n x 4px; it converts to the t-shirt size
 * nearest in LOG space (spacing perception is ratio-based, and the ramp is
 * near-geometric, so linear distance ties constantly — e.g. p-5 = 20px sits
 * exactly between md 16 and lg 24 linearly, but 20 > sqrt(16*24) = 19.6 puts
 * it at lg). Log distance never ties on the 2px-granular Tailwind scale.
 * A candidate further than x1.5 away (only possible above 7xl = 160px, e.g.
 * p-64 = 256px) is NOT converted — that would change layout, not migrate it —
 * and is reported instead.
 *
 * Left untouched by design: t-shirt (already rhythmic), `*-0` / `*-auto` /
 * `*-reverse` (no magnitude), `*-px` (1px is inherently optical), arbitrary
 * values (`mt-[3px]` — a deliberate escape hatch), Blade interpolations, and
 * any line carrying the `pinion-lint-ignore` marker (same line or the line
 * above). Width-family utilities (w-* / max-w-* / basis-* / size-*) are out
 * of scope by the same prefix whitelist as the census.
 *
 * Deliberately PURE (no Laravel). Unlike the linter it needs precise byte
 * offsets for every class-string *and* every quoted literal inside dynamic
 * bindings, so it carries its own extraction (same patterns as
 * ClassVocabularyLinter::extractClassStrings, offset-preserving variant).
 */
final class SpacingMigrator
{
    /** t-shirt size => px, per the locked v0.5 ramp. */
    private const RAMP = [
        '3xs' => 2, '2xs' => 4, 'xs' => 8, 'sm' => 12, 'md' => 16, 'lg' => 24,
        'xl' => 32, '2xl' => 48, '3xl' => 64, '4xl' => 80, '5xl' => 96,
        '6xl' => 128, '7xl' => 160,
    ];

    /** Don't convert when the best candidate is further than this ratio away. */
    private const MAX_RATIO = 1.5;

    /** Spacing utility prefixes, longest-first so gap-x wins over gap. */
    private const PREFIXES = [
        'space-x', 'space-y', 'gap-x', 'gap-y', 'gap',
        'px', 'py', 'pt', 'pr', 'pb', 'pl', 'ps', 'pe', 'p',
        'mx', 'my', 'mt', 'mr', 'mb', 'ml', 'ms', 'me', 'm',
    ];

    private const IGNORE_MARKER = 'pinion-lint-ignore';

    /**
     * Compute the migration for a source string.
     *
     * Returns:
     *   'source'  => string — the rewritten source (equals input when nothing changed)
     *   'changes' => list of ['line' => int, 'from' => string, 'to' => string, 'px' => int, 'target_px' => int]
     *   'skipped' => list of ['line' => int, 'token' => string, 'px' => int] — numeric but no close t-shirt
     */
    public function migrate(string $source): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $source) ?: [];
        $changes = [];
        $skipped = [];

        // collect [contentOffset, content] edits, apply back-to-front
        $edits = [];

        foreach ($this->extractSegments($source) as [$content, $offset]) {
            $line = substr_count($source, "\n", 0, min($offset, strlen($source))) + 1;

            $thisLine = $lines[$line - 1] ?? '';
            $prevLine = $lines[$line - 2] ?? '';
            if (str_contains($thisLine, self::IGNORE_MARKER) || str_contains($prevLine, self::IGNORE_MARKER)) {
                continue;
            }

            $new = $this->rewriteClassString($content, $line, $changes, $skipped);
            if ($new !== $content) {
                $edits[] = [$offset, strlen($content), $new];
            }
        }

        usort($edits, fn ($a, $b) => $b[0] <=> $a[0]);
        foreach ($edits as [$offset, $len, $new]) {
            $source = substr_replace($source, $new, $offset, $len);
        }

        return ['source' => $source, 'changes' => $changes, 'skipped' => $skipped];
    }

    /** Rewrite one class string; records changes/skips (by reference). */
    private function rewriteClassString(string $content, int $line, array &$changes, array &$skipped): string
    {
        $parts = preg_split('/(\s+)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];

        foreach ($parts as $i => $token) {
            if ($token === '' || trim($token) === '') {
                continue;
            }
            $result = $this->convertToken($token);
            if ($result === null) {
                continue;
            }
            [$to, $px, $targetPx] = $result;
            if ($to === false) {
                $skipped[] = ['line' => $line, 'token' => $token, 'px' => $px];
                continue;
            }
            $changes[] = ['line' => $line, 'from' => $token, 'to' => $to, 'px' => $px, 'target_px' => $targetPx];
            $parts[$i] = $to;
        }

        return implode('', $parts);
    }

    /**
     * Convert one class token.
     * Returns null when out of scope; [false, px, 0] when numeric but no close
     * t-shirt; [newToken, px, targetPx] when convertible.
     */
    private function convertToken(string $token): ?array
    {
        if (str_contains($token, '{{') || str_contains($token, '}}') || str_contains($token, '$')) {
            return null;
        }

        // split off variants (hover:, md:, ...), "!", and the negative "-"
        $variants = '';
        $base = $token;
        if (($pos = strrpos($base, ':')) !== false) {
            $variants = substr($base, 0, $pos + 1);
            $base = substr($base, $pos + 1);
        }
        $bang = '';
        if (str_starts_with($base, '!')) {
            $bang = '!';
            $base = substr($base, 1);
        }
        $trailingBang = '';
        if (str_ends_with($base, '!')) { // Tailwind v4 important
            $trailingBang = '!';
            $base = substr($base, 0, -1);
        }
        $neg = '';
        if (str_starts_with($base, '-')) {
            $neg = '-';
            $base = substr($base, 1);
        }

        foreach (self::PREFIXES as $prefix) {
            if (! str_starts_with($base, $prefix . '-')) {
                continue;
            }
            $suffix = substr($base, strlen($prefix) + 1);

            if (! preg_match('/^\d+(?:\.\d+)?$/', $suffix) || $suffix === '0') {
                return null; // t-shirt / px / auto / reverse / arbitrary / zero
            }

            $px = (float) $suffix * 4;
            [$size, $targetPx] = $this->nearest($px);
            if ($size === null) {
                return [false, (int) $px, 0];
            }

            return ["{$variants}{$bang}{$neg}{$prefix}-{$size}{$trailingBang}", (int) $px, $targetPx];
        }

        return null;
    }

    /** Nearest ramp size in log space; null when further than MAX_RATIO. */
    private function nearest(float $px): array
    {
        $best = null;
        $bestDist = INF;
        foreach (self::RAMP as $size => $rampPx) {
            $dist = abs(log($px) - log($rampPx));
            if ($dist < $bestDist) {
                $bestDist = $dist;
                $best = [$size, $rampPx];
            }
        }

        return $bestDist <= log(self::MAX_RATIO) ? $best : [null, 0];
    }

    /**
     * Extract every class-string segment with a precise byte offset:
     * static class="..."/'...' content, plus each quoted literal inside
     * :class / x-bind:class expressions and @class([...]) arrays. Same
     * patterns as ClassVocabularyLinter::extractClassStrings, but literals
     * inside dynamic bindings get their own absolute offsets so they can be
     * rewritten in place.
     */
    private function extractSegments(string $source): array
    {
        $out = [];

        if (preg_match_all('/\bclass\s*=\s*(["\'])(.*?)\1/s', $source, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[2] as $cap) {
                $out[] = [$cap[0], $cap[1]];
            }
        }

        foreach (['/(?::class|x-bind:class)\s*=\s*"([^"]*)"/s', '/@class\s*\(\s*\[(.*?)\]\s*\)/s'] as $pattern) {
            if (preg_match_all($pattern, $source, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[1] as $cap) {
                    [$expr, $exprOffset] = $cap;
                    if (preg_match_all('/"([^"]*)"|\'([^\']*)\'/', $expr, $lm, PREG_OFFSET_CAPTURE)) {
                        foreach ($lm[1] as $j => $dq) {
                            $lit = $dq[0] !== '' ? $dq : $lm[2][$j];
                            if ($lit[0] !== '') {
                                $out[] = [$lit[0], $exprOffset + $lit[1]];
                            }
                        }
                    }
                }
            }
        }

        return $out;
    }
}
