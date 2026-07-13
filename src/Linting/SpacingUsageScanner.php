<?php

namespace SparrowhawkLabs\PinionUi\Linting;

/**
 * SpacingUsageScanner — a non-gating census of spacing tokens, backing
 * `ui:lint --spacing`.
 *
 * Counts how spacing utilities split between the two sanctioned scales
 * (AGENTS.md -> "Rhythmic vs optical spacing"):
 *
 *   rhythmic  -> t-shirt suffixes (p-md, gap-lg, space-y-xl) — tune-reactive
 *   optical   -> numeric (p-4, mt-1.5, p-px) and arbitrary (mt-[3px]) — fixed
 *
 * This is deliberately NOT a lint rule: whether a numeric token is a lazy
 * rhythm substitute or a legitimate optical nudge is designer intent, which
 * no token-level check can recover. Flagging it would be false-positive noise
 * that erodes trust in the real (gating) vocabulary rules. The census instead
 * makes drift visible ("why doesn't tune move my spacing?" -> look at the
 * optical share) without ever failing a build. It counts everything —
 * `pinion-lint-ignore` markers are not honored, since suppressed lines would
 * skew the ratio.
 *
 * Scope: padding / margin / gap / space-* prefixes ONLY. Width-family
 * utilities (`w-*` / `min-w-*` / `max-w-*` / `basis-*` / `size-*`) share the
 * t-shirt suffix names but resolve to the CONTAINER scale, not spacing (see
 * tune.css CONTAINER-SCALE COMPENSATION) — the prefix whitelist keeps them
 * out by construction. `*-0`, `*-auto`, `*-reverse`, `*-full` carry no
 * tune-relevant magnitude and are skipped.
 *
 * Deliberately PURE (no Laravel), like ClassVocabularyLinter, whose
 * extractClassStrings() it reuses so both tools see the exact same class
 * strings (static class=, :class / x-bind:class literals, @class arrays).
 */
final class SpacingUsageScanner
{
    private const TSHIRT = [
        '3xs', '2xs', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl',
    ];

    /** Spacing utility prefixes, longest-first so gap-x wins over gap. */
    private const PREFIXES = [
        'space-x', 'space-y', 'gap-x', 'gap-y', 'gap',
        'px', 'py', 'pt', 'pr', 'pb', 'pl', 'ps', 'pe', 'p',
        'mx', 'my', 'mt', 'mr', 'mb', 'ml', 'ms', 'me', 'm',
    ];

    /**
     * Scan a source string. Returns a list of occurrences:
     *   ['line' => int, 'token' => string (as written, incl. variants/negative),
     *    'kind' => 'rhythmic'|'numeric'|'arbitrary']
     */
    public function scan(string $source): array
    {
        $extractor = new ClassVocabularyLinter();
        $out = [];

        foreach ($extractor->extractClassStrings($source) as [$classStr, $offset]) {
            $line = substr_count($source, "\n", 0, min($offset, strlen($source))) + 1;

            foreach (preg_split('/\s+/', trim($classStr)) ?: [] as $raw) {
                $kind = $this->classify($raw);
                if ($kind !== null) {
                    $out[] = ['line' => $line, 'token' => $raw, 'kind' => $kind];
                }
            }
        }

        return $out;
    }

    /** Scan a file on disk; occurrences gain a 'file' key. */
    public function scanFile(string $path): array
    {
        $src = @file_get_contents($path);
        if ($src === false) {
            return [];
        }

        return array_map(fn ($o) => ['file' => $path] + $o, $this->scan($src));
    }

    /** Classify one class token; null if it isn't a magnitude-bearing spacing utility. */
    private function classify(string $raw): ?string
    {
        if ($raw === '' || str_contains($raw, '{{') || str_contains($raw, '}}') || str_contains($raw, '$')) {
            return null;
        }

        // strip variant prefixes (hover:, md:, ...) then "!" and the negative "-"
        $base = $raw;
        if (($pos = strrpos($base, ':')) !== false) {
            $base = substr($base, $pos + 1);
        }
        $base = ltrim($base, '!');
        if (str_starts_with($base, '-')) {
            $base = substr($base, 1);
        }

        foreach (self::PREFIXES as $prefix) {
            if (! str_starts_with($base, $prefix . '-')) {
                continue;
            }
            $suffix = substr($base, strlen($prefix) + 1);

            if (in_array($suffix, self::TSHIRT, true)) {
                return 'rhythmic';
            }
            if ($suffix === 'px' || preg_match('/^\d+(?:\.\d+)?$/', $suffix)) {
                return $suffix === '0' ? null : 'numeric';
            }
            if (str_starts_with($suffix, '[') && str_ends_with($suffix, ']')) {
                return 'arbitrary';
            }

            return null; // auto / reverse / full / unknown — not a magnitude
        }

        return null;
    }
}
