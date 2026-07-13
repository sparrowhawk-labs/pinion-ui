<?php

namespace SparrowhawkLabs\PinionUi\Linting;

/**
 * EjectTransformer — freeze one theme × tune × strength rendering into
 * vanilla Tailwind classes, backing `ui:eject`. The reverse direction of
 * SpacingMigrator: migrate to adopt pinion-ui, eject to leave (or to hand
 * a design off to a project that doesn't want the dependency) — no lock-in.
 *
 * Token values come from src/resources/eject-table.json — browser-measured
 * by packages/pinion-ui-css/harness/eject-table.mjs (NOT re-derived from
 * tune.css's base+delta×strength cascade in PHP, which would drift).
 *
 * What gets ejected:
 *   - t-shirt spacing (p-md, gap-lg, -mt-xs, …)  -> numeric spacing classes
 *   - single-token tune utilities (radius-box, tune-border, h-field-md,
 *     px-field-*, px-input-*, py-input-*, text-field-*) -> Tailwind equivalents
 *   - compound tune utilities (tune-btn-md, tune-input-lg, tune-card-pad, …)
 *     -> expanded to their per-property classes (variants are distributed:
 *     hover:tune-btn-md -> hover:h-9 hover:px-3.5 hover:text-[14px])
 *   - [var(--token)] arbitrary values -> measured values ([var(--radius-box)]
 *     -> [8px]); shadows become underscore-escaped arbitrary shadows
 *   - daisyUI semantic colors (bg-primary, text-base-content/60, …)
 *     -> hex arbitrary values with the opacity modifier preserved
 *
 * Output-class rules (correctness over prettiness):
 *   - lengths snap to the nearest quarter-step numeric (max 0.5px off,
 *     invisible; Tailwind v4 generates quarter steps like p-8.5 — verified —
 *     but nothing finer) ONLY on spacing-scale utilities (p/m/gap/space/h/
 *     min-h/w/size); font-size is ALWAYS text-[13px]-style arbitrary because
 *     named text-sm/base/… also set line-height, which the tune utilities
 *     do not; radius/shadow stay arbitrary because the named Tailwind scales
 *     are host-overridable.
 *
 * Deliberately NOT ejected (reported as skipped so nothing fails silently):
 *   - font-heading / font-body / font-mono-tune / font-weight-heading —
 *     per-tune font-families need host-side <link> loading and quoted family
 *     lists don't survive class-name escaping; handle manually
 *   - unknown [var(--…)] tokens; color names missing from the theme table
 *   - <x-…> component TAGS are structural Blade, out of scope by definition
 *
 * Round-trip guarantee: under default/md every t-shirt size sits exactly on
 * the 4px grid, so eject(migrate(x)) == x for exact-grid sources.
 * `pinion-lint-ignore` (same line or line above) is honored.
 * Deliberately PURE (no Laravel) — table slices are injected.
 */
final class EjectTransformer
{
    private const TSHIRT = [
        '3xs', '2xs', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl',
    ];

    /** Spacing utility prefixes, longest-first so gap-x wins over gap. */
    private const SPACING_PREFIXES = [
        'space-x', 'space-y', 'gap-x', 'gap-y', 'gap',
        'px', 'py', 'pt', 'pr', 'pb', 'pl', 'ps', 'pe', 'p',
        'mx', 'my', 'mt', 'mr', 'mb', 'ml', 'ms', 'me', 'm',
    ];

    /** Single-token tune utilities: class => [tailwind prefix kind, token]. */
    private const SINGLE = [
        'radius-box' => ['rounded', 'radius-box'],
        'radius-field' => ['rounded', 'radius-field'],
        'radius-selector' => ['rounded', 'radius-selector'],
        'tune-border' => ['border', 'border'],
        'h-field-xs' => ['h', 'h-field-xs'], 'h-field-sm' => ['h', 'h-field-sm'],
        'h-field-md' => ['h', 'h-field-md'], 'h-field-lg' => ['h', 'h-field-lg'],
        'px-field-xs' => ['px', 'px-field-xs'], 'px-field-sm' => ['px', 'px-field-sm'],
        'px-field-md' => ['px', 'px-field-md'], 'px-field-lg' => ['px', 'px-field-lg'],
        'px-input-xs' => ['px', 'px-input-xs'], 'px-input-sm' => ['px', 'px-input-sm'],
        'px-input-md' => ['px', 'px-input-md'], 'px-input-lg' => ['px', 'px-input-lg'],
        'py-input-xs' => ['py', 'py-input-xs'], 'py-input-sm' => ['py', 'py-input-sm'],
        'py-input-md' => ['py', 'py-input-md'], 'py-input-lg' => ['py', 'py-input-lg'],
        'text-field-xs' => ['text', 'text-field-xs'], 'text-field-sm' => ['text', 'text-field-sm'],
        'text-field-md' => ['text', 'text-field-md'], 'text-field-lg' => ['text', 'text-field-lg'],
    ];

    /** Compound tune utilities: class => [[prefix kind, token], ...] (mirrors tune.css @utility defs). */
    private const COMPOUND = [
        'tune-btn-xs' => [['h', 'h-field-xs'], ['px', 'px-field-xs'], ['text', 'text-field-sm']],
        'tune-btn-sm' => [['h', 'h-field-sm'], ['px', 'px-field-sm'], ['text', 'text-field-sm']],
        'tune-btn-md' => [['h', 'h-field-md'], ['px', 'px-field-md'], ['text', 'text-field-md']],
        'tune-btn-lg' => [['h', 'h-field-lg'], ['px', 'px-field-lg'], ['text', 'text-field-lg']],
        'tune-input-sm' => [['h', 'h-field-sm'], ['px', 'px-input-sm'], ['text', 'text-field-sm']],
        'tune-input-md' => [['h', 'h-field-md'], ['px', 'px-input-md'], ['text', 'text-field-md']],
        'tune-input-lg' => [['h', 'h-field-lg'], ['px', 'px-input-lg'], ['text', 'text-field-lg']],
        'tune-textarea-sm' => [['px', 'px-input-sm'], ['py', 'py-input-sm'], ['text', 'text-field-sm']],
        'tune-textarea-md' => [['px', 'px-input-md'], ['py', 'py-input-md'], ['text', 'text-field-md']],
        'tune-textarea-lg' => [['px', 'px-input-lg'], ['py', 'py-input-lg'], ['text', 'text-field-lg']],
        'tune-tab-sm' => [['h', 'h-field-sm'], ['px', 'px-field-sm'], ['text', 'text-field-sm']],
        'tune-tab-md' => [['h', 'h-field-md'], ['px', 'px-field-md'], ['text', 'text-field-md']],
        'tune-tab-lg' => [['h', 'h-field-lg'], ['px', 'px-field-lg'], ['text', 'text-field-lg']],
        'tune-alert-md' => [['min-h', 'h-field-md'], ['p', 'px-field-md'], ['text', 'text-field-md']],
        'tune-accordion-header' => [['min-h', 'h-field-md'], ['px', 'px-field-md'], ['text', 'text-field-md']],
        'tune-accordion-body' => [['px', 'px-field-md'], ['py', 'spacing-sm'], ['text', 'text-field-md']],
        'tune-dropdown-trigger' => [['h', 'h-field-md'], ['px', 'px-field-md'], ['text', 'text-field-md']],
        'tune-menu-item' => [['min-h', 'h-field-sm'], ['px', 'px-field-md'], ['text', 'text-field-md']],
        'tune-card-pad' => [['p', 'px-field-lg']],
        'tune-modal-body' => [['text', 'text-field-md']],
        'tune-modal-title' => [['text', 'text-field-lg']],
    ];

    /** Tune font utilities — reported, not converted (host must handle font loading). */
    private const FONT_UTILITIES = ['font-heading', 'font-body', 'font-mono-tune', 'font-weight-heading'];

    /** Color-bearing utility prefixes (mirrors ClassVocabularyLinter::COLOR_PROPS). */
    private const COLOR_PROPS = [
        'bg', 'text', 'border', 'ring', 'ring-offset', 'from', 'via', 'to',
        'fill', 'stroke', 'divide', 'outline', 'decoration', 'placeholder',
        'caret', 'accent', 'shadow',
    ];

    private const IGNORE_MARKER = 'pinion-lint-ignore';

    /** Longest-first semantic color names, built from the injected theme colors. */
    private array $colorNames;

    /**
     * @param array $tokens one tune|strength slice of eject-table.json (token => px float | shadow string)
     * @param array $colors one theme slice (semantic name => #hex), may be []
     */
    public function __construct(private array $tokens, private array $colors = [])
    {
        $this->colorNames = array_keys($colors);
        usort($this->colorNames, fn ($a, $b) => strlen($b) <=> strlen($a));
    }

    /**
     * Returns:
     *   'source'  => rewritten source
     *   'changes' => [['line','from','to'], ...]
     *   'skipped' => [['line','token','reason'], ...]
     */
    public function eject(string $source): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $source) ?: [];
        $changes = [];
        $skipped = [];
        $edits = [];

        foreach (ClassSegments::extract($source) as [$content, $offset]) {
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
            if (isset($result['skip'])) {
                $skipped[] = ['line' => $line, 'token' => $token, 'reason' => $result['skip']];
                continue;
            }
            $changes[] = ['line' => $line, 'from' => $token, 'to' => $result['to']];
            $parts[$i] = $result['to'];
        }

        return implode('', $parts);
    }

    /** Convert one token; null = out of scope, ['skip' => reason] or ['to' => replacement]. */
    private function convertToken(string $token): ?array
    {
        if (str_contains($token, '{{') || str_contains($token, '}}') || str_contains($token, '$')) {
            return null;
        }

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
        if (str_ends_with($base, '!')) {
            $trailingBang = '!';
            $base = substr($base, 0, -1);
        }
        $neg = '';
        if (str_starts_with($base, '-')) {
            $neg = '-';
            $base = substr($base, 1);
        }
        $wrap = fn (string $b) => "{$variants}{$bang}{$neg}{$b}{$trailingBang}";

        // 1) tune font utilities — manual territory
        if (in_array($base, self::FONT_UTILITIES, true)) {
            return ['skip' => 'per-tune font — load the family and set font-family manually'];
        }

        // 2) compound tune utilities -> expanded class list (variants distributed)
        if (isset(self::COMPOUND[$base])) {
            $out = [];
            foreach (self::COMPOUND[$base] as [$kind, $tokenName]) {
                $out[] = $wrap($this->emit($kind, $tokenName));
            }

            return ['to' => implode(' ', $out)];
        }

        // 3) single-token tune utilities
        if (isset(self::SINGLE[$base])) {
            [$kind, $tokenName] = self::SINGLE[$base];

            return ['to' => $wrap($this->emit($kind, $tokenName))];
        }

        // 4) t-shirt spacing -> numeric
        foreach (self::SPACING_PREFIXES as $prefix) {
            if (str_starts_with($base, $prefix . '-')) {
                $suffix = substr($base, strlen($prefix) + 1);
                if (in_array($suffix, self::TSHIRT, true)) {
                    return ['to' => $wrap($prefix . '-' . $this->quarter((float) $this->tokens["spacing-{$suffix}"]))];
                }
                break; // spacing prefix with non-t-shirt suffix: already vanilla
            }
        }

        // 5) [var(--token)] arbitrary values
        if (preg_match('/^(.*)\[var\(--([a-z0-9-]+)\)\]$/', $base, $m)) {
            [, $head, $varName] = $m;
            if (array_key_exists($varName, $this->tokens)) {
                $value = $this->tokens[$varName];
                $literal = is_numeric($value) ? $this->trimZeros((float) $value) . 'px' : $this->escapeArbitrary((string) $value);

                return ['to' => $wrap($this->beautifyArbitrary($head, $literal))];
            }

            return ['skip' => "unknown token var(--{$varName}) — not in the eject table"];
        }

        // 6) semantic theme colors
        foreach ($this->colorNames as $name) {
            foreach (self::COLOR_PROPS as $prop) {
                if ($base === "{$prop}-{$name}" || str_starts_with($base, "{$prop}-{$name}/")) {
                    $opacity = substr($base, strlen("{$prop}-{$name}"));

                    return ['to' => $wrap("{$prop}-[{$this->colors[$name]}]{$opacity}")];
                }
            }
        }

        return null;
    }

    /** Emit the Tailwind class for one [kind, token] pair. */
    private function emit(string $kind, string $tokenName): string
    {
        $value = $this->tokens[$tokenName];

        return match ($kind) {
            // spacing-scale kinds: numeric quarter-step (max 0.5px off, always a valid class)
            'h', 'min-h', 'p', 'px', 'py' => $kind . '-' . $this->quarter((float) $value),
            // font-size: arbitrary ONLY — named text-* would also set line-height
            'text' => 'text-[' . $this->trimZeros((float) $value) . 'px]',
            // border width: named steps where Tailwind has them
            'border' => match ((float) $value) {
                1.0 => 'border', 2.0 => 'border-2', 4.0 => 'border-4', 8.0 => 'border-8',
                default => 'border-[' . $this->trimZeros((float) $value) . 'px]',
            },
            // radius: arbitrary — Tailwind's named radius scale is host-overridable
            'rounded' => 'rounded-[' . $this->trimZeros((float) $value) . 'px]',
            default => throw new \LogicException("unhandled emit kind '{$kind}'"),
        };
    }

    /**
     * px -> nearest quarter-step Tailwind numeric suffix ("9", "8.5", "0.75").
     * A quarter step in suffix units is exactly 1px, so snapping = round(px)/4
     * (max error 0.5px, invisible).
     */
    private function quarter(float $px): string
    {
        return $this->trimZeros(round($px) / 4);
    }

    private function trimZeros(float $n): string
    {
        return rtrim(rtrim(number_format($n, 4, '.', ''), '0'), '.');
    }

    /** Escape a CSS value (shadow list) for use inside a Tailwind arbitrary value. */
    private function escapeArbitrary(string $value): string
    {
        return str_replace([', ', ' '], [',', '_'], trim($value));
    }

    /** After var() substitution, snap h-/w-/spacing-family arbitraries to numeric. */
    private function beautifyArbitrary(string $head, string $literal): string
    {
        // $head is e.g. "h-", "rounded-", "shadow-" (still carries the trailing dash)
        $prefix = rtrim($head, '-');
        $spacingLike = in_array($prefix, array_merge(self::SPACING_PREFIXES, ['h', 'min-h', 'w', 'min-w', 'max-w', 'size']), true);
        if ($spacingLike && preg_match('/^([\d.]+)px$/', $literal, $m)) {
            return $prefix . '-' . $this->quarter((float) $m[1]);
        }

        return $head . '[' . $literal . ']';
    }
}
