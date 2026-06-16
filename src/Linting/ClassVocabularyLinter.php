<?php

namespace SparrowhawkLabs\PinionUi\Linting;

/**
 * ClassVocabularyLinter — enforces the pinion-ui "class vocabulary" rule
 * (AGENTS.md -> "Class vocabulary"):
 *
 *   Every class string is plain Tailwind v4, with exactly TWO exceptions:
 *     1. Color            -> daisyUI *semantic* color classes (bg-primary, text-base-content, ...)
 *     2. Shape/space/size -> pinion-ui tune classes & tune tokens
 *
 * This linter catches the two ways that rule is silently broken in host markup:
 *
 *   (a) daisyUI *component* classes (.btn .card .badge .input ...). The pinion-ui
 *       preset loads daisyUI with an `exclude:` list (src/resources/css/pinion-ui.css),
 *       so these compile to NOTHING — a silent no-op style, the worst kind of bug
 *       because the markup *looks* styled. The source of truth for which parts are
 *       excluded is that `exclude:` list; the daisyUI *part* name (e.g. `button`,
 *       `fileinput`) is mapped here to the real CSS class root (`btn`, `file-input`).
 *
 *   (b) fixed-palette / hex colors (bg-blue-500, text-[#1d4ed8]). They ignore
 *       `data-theme`, so they break theming. Only daisyUI *semantic* colors track
 *       the theme.
 *
 * Deliberately PURE (no Laravel) so it runs headless in CI and unit tests. The
 * `ui:lint` artisan command is a thin wrapper that globs Blade files and calls this.
 *
 * False-positive discipline: several daisyUI part names collide with real Tailwind
 * utilities or common custom class words. Those are handled explicitly below
 * (`select-none` is Tailwind user-select; `list-disc` is Tailwind; `table-auto`
 * is Tailwind; `hero`/`footer`/`link`/`label`/`table`/`list` bare are too common to
 * flag — only their unmistakably-daisyUI compounds are). KEPT daisyUI parts
 * (avatar, divider, indicator, kbd, loading, mask, rating, progress, range,
 * skeleton, stat, timeline, breadcrumbs, collapse, join) are never flagged —
 * pinion-ui's own components depend on them, so they are not in the exclude list.
 */
final class ClassVocabularyLinter
{
    /**
     * Excluded daisyUI component roots with NO Tailwind collision on the root or
     * its `<root>-...` modifiers. Both the bare root and any `root-...` token are
     * flagged. (daisyUI part name -> class root, per pinion-ui.css `exclude:`.)
     */
    private const STRICT_ROOTS = [
        'btn', 'card', 'badge', 'alert', 'input', 'textarea', 'checkbox',
        'radio', 'toggle', 'modal', 'menu', 'navbar', 'dropdown', 'drawer',
        'tabs', 'tab', 'swap', 'tooltip', 'toast', 'fieldset', 'chat',
        'carousel', 'countdown', 'diff', 'dock', 'fab', 'steps', 'step',
        'file-input', 'radial-progress', 'validator',
        'mockup-code', 'mockup-window', 'mockup-phone', 'mockup-browser',
    ];

    /**
     * `select` is excluded by daisyUI, but Tailwind owns select-none/text/all/auto
     * (user-select). Flag the bare root and daisyUI-shaped modifiers; never these.
     */
    private const SELECT_TAILWIND = ['select-none', 'select-text', 'select-all', 'select-auto'];

    /**
     * Excluded daisyUI parts whose root word is a common custom/Tailwind token
     * (hero, footer, link, label, list, table). The bare root is NOT flagged
     * (too many false positives); only these unmistakably-daisyUI compounds are.
     */
    private const COMPOUND_ONLY = [
        'hero-content', 'hero-overlay',
        'footer-title', 'footer-center', 'footer-horizontal',
        'link-hover', 'link-primary', 'link-secondary', 'link-accent',
        'link-neutral', 'link-info', 'link-success', 'link-warning', 'link-error',
        'label-text', 'label-text-alt',
        'list-row',
        'table-zebra', 'table-pin-rows', 'table-pin-cols',
        'table-xs', 'table-sm', 'table-md', 'table-lg',
    ];

    /** Tailwind fixed palettes — using these for color ignores data-theme. */
    private const TW_PALETTES = [
        'slate', 'gray', 'zinc', 'neutral', 'stone', 'red', 'orange', 'amber',
        'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue',
        'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose',
    ];

    private const TW_SCALES = ['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'];

    /** Color-bearing utility prefixes. */
    private const COLOR_PROPS = [
        'bg', 'text', 'border', 'ring', 'ring-offset', 'from', 'via', 'to',
        'fill', 'stroke', 'divide', 'outline', 'decoration', 'placeholder',
        'caret', 'accent', 'shadow',
    ];

    /** Marker that suppresses a line (and the line above it). */
    private const IGNORE_MARKER = 'pinion-lint-ignore';

    /**
     * Lint a source string. Returns a list of violations:
     *   ['line' => int, 'token' => string,
     *    'kind' => 'daisyui-component'|'fixed-color'|'missing-data-theme'|'missing-data-tune',
     *    'message' => string]
     */
    public function lint(string $source): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $source) ?: [];
        $violations = [];

        foreach ($this->extractClassStrings($source) as [$classStr, $offset]) {
            $line = substr_count($source, "\n", 0, min($offset, strlen($source))) + 1;

            // skip lines explicitly marked (this line or the one above)
            $thisLine = $lines[$line - 1] ?? '';
            $prevLine = $lines[$line - 2] ?? '';
            if (str_contains($thisLine, self::IGNORE_MARKER) || str_contains($prevLine, self::IGNORE_MARKER)) {
                continue;
            }

            foreach (preg_split('/\s+/', trim($classStr)) ?: [] as $raw) {
                if ($raw === '') {
                    continue;
                }
                $v = $this->classify($raw);
                if ($v !== null) {
                    $violations[] = ['line' => $line, 'token' => $raw] + $v;
                }
            }
        }

        return array_merge($violations, $this->lintRootAttributes($source));
    }

    /**
     * Flag a root `<html>` tag missing the theme × tune cascade attributes
     * (invariant: theme via data-theme × tune via data-tune × component). Without
     * them the cascade silently breaks — colors stop tracking the theme and tune
     * tokens (shape/space/size) don't apply, so the page *looks* styled but is off.
     * Only fires on files that actually contain an `<html>` tag (i.e. layouts).
     */
    private function lintRootAttributes(string $source): array
    {
        if (! preg_match('/<html\b[^>]*>/i', $source, $m, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $tag = $m[0][0];
        $offset = $m[0][1];
        $line = substr_count($source, "\n", 0, min($offset, strlen($source))) + 1;

        $lines = preg_split('/\r\n|\r|\n/', $source) ?: [];
        $thisLine = $lines[$line - 1] ?? '';
        if (str_contains($thisLine, self::IGNORE_MARKER)) {
            return [];
        }

        $out = [];
        if (! preg_match('/\bdata-theme\s*=/i', $tag)) {
            $out[] = [
                'line' => $line, 'token' => 'data-theme', 'kind' => 'missing-data-theme',
                'message' => '<html> is missing data-theme — colors will not track the theme. '
                    . 'Add e.g. data-theme="pinion".',
            ];
        }
        if (! preg_match('/\bdata-tune\s*=/i', $tag)) {
            $out[] = [
                'line' => $line, 'token' => 'data-tune', 'kind' => 'missing-data-tune',
                'message' => '<html> is missing data-tune — tune tokens (shape/space/size) will not cascade. '
                    . 'Add e.g. data-tune="default".',
            ];
        }

        return $out;
    }

    /** Lint a file on disk. */
    public function lintFile(string $path): array
    {
        $src = @file_get_contents($path);
        if ($src === false) {
            return [];
        }

        return array_map(fn ($v) => ['file' => $path] + $v, $this->lint($src));
    }

    /**
     * Pull every class string out of the source: static `class="..."`/`'...'`,
     * plus dynamic `:class` / `x-bind:class` / `@class([...])` (their quoted
     * literals and array keys). Returns [classString, byteOffset] pairs.
     */
    private function extractClassStrings(string $source): array
    {
        $out = [];

        // static: class="..." and class='...'
        if (preg_match_all('/\bclass\s*=\s*(["\'])(.*?)\1/s', $source, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[2] as $cap) {
                $out[] = [$cap[0], $cap[1]];
            }
        }

        // dynamic Alpine/Livewire binding: :class="..." / x-bind:class="..."
        // The value is a JS/PHP expression; pull the quoted string literals out of it.
        if (preg_match_all('/(?::class|x-bind:class)\s*=\s*"([^"]*)"/s', $source, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[1] as $cap) {
                foreach ($this->innerLiterals($cap[0]) as $lit) {
                    $out[] = [$lit, $cap[1]];
                }
            }
        }

        // @class([...]) directive: keys and values that are string literals
        if (preg_match_all('/@class\s*\(\s*\[(.*?)\]\s*\)/s', $source, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[1] as $cap) {
                foreach ($this->innerLiterals($cap[0]) as $lit) {
                    $out[] = [$lit, $cap[1]];
                }
            }
        }

        return $out;
    }

    /** Extract single- and double-quoted substrings from an expression. */
    private function innerLiterals(string $expr): array
    {
        $lits = [];
        if (preg_match_all('/"([^"]*)"|\'([^\']*)\'/', $expr, $m)) {
            foreach ($m[1] as $i => $dq) {
                $lits[] = $dq !== '' ? $dq : $m[2][$i];
            }
        }

        return array_filter($lits, fn ($s) => $s !== '');
    }

    /** Classify a single class token; null if it's fine. */
    private function classify(string $raw): ?array
    {
        // strip Blade noise tokens
        if ($raw === '' || str_contains($raw, '{{') || str_contains($raw, '}}') || str_contains($raw, '$')) {
            return null;
        }

        // strip variant prefixes (hover:, md:, dark:, group-hover:, ...) and leading "!"
        $base = $raw;
        if (($pos = strrpos($base, ':')) !== false) {
            $base = substr($base, $pos + 1);
        }
        $base = ltrim($base, '!');
        if ($base === '') {
            return null;
        }

        if ($hit = $this->componentViolation($base)) {
            return $hit;
        }
        if ($hit = $this->colorViolation($base)) {
            return $hit;
        }

        return null;
    }

    private function componentViolation(string $base): ?array
    {
        // select: allow the four Tailwind user-select utilities
        if (in_array($base, self::SELECT_TAILWIND, true)) {
            return null;
        }
        if ($base === 'select' || str_starts_with($base, 'select-')) {
            return $this->component($base, 'select');
        }

        foreach (self::STRICT_ROOTS as $root) {
            if ($base === $root || str_starts_with($base, $root . '-')) {
                return $this->component($base, $root);
            }
        }

        if (in_array($base, self::COMPOUND_ONLY, true)) {
            $root = explode('-', $base)[0];

            return $this->component($base, $root);
        }

        return null;
    }

    private function component(string $base, string $root): array
    {
        return [
            'kind' => 'daisyui-component',
            'message' => "daisyUI component class '{$base}' is excluded from the build (no-op). "
                . "Use the <x-{$this->xName($root)}> component, or compose with plain Tailwind + a tune class.",
        ];
    }

    private function colorViolation(string $base): ?array
    {
        // arbitrary hex value: bg-[#1d4ed8], text-[#fff]/50, border-[#abc]
        if (preg_match('/#[0-9a-fA-F]{3,8}\b/', $base)) {
            return [
                'kind' => 'fixed-color',
                'message' => "fixed hex color '{$base}' ignores data-theme. "
                    . 'Use a daisyUI semantic color (bg-primary, text-base-content, ...).',
            ];
        }

        // fixed Tailwind palette: bg-blue-500, text-red-700/50, ring-offset-gray-200
        $palettes = implode('|', self::TW_PALETTES);
        $props = implode('|', array_map('preg_quote', self::COLOR_PROPS));
        $scales = implode('|', self::TW_SCALES);
        if (preg_match('/^(?:' . $props . ')-(?:' . $palettes . ')-(?:' . $scales . ')(?:\/\d+)?$/', $base)) {
            return [
                'kind' => 'fixed-color',
                'message' => "fixed palette color '{$base}' ignores data-theme. "
                    . 'Use a daisyUI semantic color (bg-primary, bg-base-200, text-error, ...).',
            ];
        }

        return null;
    }

    /** Best-effort map from class root to pinion-ui component tag for the hint. */
    private function xName(string $root): string
    {
        return match ($root) {
            'btn' => 'button',
            'file-input' => 'file-upload',
            'radial-progress' => 'progress',
            'tabs', 'tab' => 'tabs',
            'steps', 'step' => 'stepper',
            'mockup-code', 'mockup-window', 'mockup-phone', 'mockup-browser' => 'mockup',
            default => $root,
        };
    }
}
