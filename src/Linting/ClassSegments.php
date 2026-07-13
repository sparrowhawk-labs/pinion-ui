<?php

namespace SparrowhawkLabs\PinionUi\Linting;

/**
 * ClassSegments — offset-precise extraction of every class-string segment
 * from Blade source, shared by the rewriting tools (SpacingMigrator,
 * EjectTransformer). Same patterns as ClassVocabularyLinter's extraction,
 * but each quoted literal inside dynamic bindings gets its own absolute
 * byte offset so it can be rewritten in place.
 */
final class ClassSegments
{
    /** Returns [[content, byteOffset], ...] for static class= plus dynamic-binding literals. */
    public static function extract(string $source): array
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
