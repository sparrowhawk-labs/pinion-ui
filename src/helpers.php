<?php

if (!function_exists('pn_trans')) {
    /**
     * Look up a pinion-ui component string from
     * `config('pinion-ui.translations.<locale>.<key>')` where `<locale>` is
     * `config('pinion-ui.locale')`. Returns `$fallback` (or the key itself)
     * if the locale or key is missing — keeps Blade templates safe even
     * before the consumer publishes the config.
     */
    function pn_trans(string $key, ?string $fallback = null): string
    {
        // v0.7.3: when `pinion-ui.locale` is null (the new default), follow
        // the app's runtime locale — multi-locale apps that switch
        // App::setLocale() per request get matching component strings.
        // Setting the config (or PINION_UI_LOCALE) still pins one locale.
        // Lookup chain: {locale} → en → per-callsite $fallback → key.
        $locale = config('pinion-ui.locale') ?: app()->getLocale();
        $value = config("pinion-ui.translations.{$locale}.{$key}")
            ?? config("pinion-ui.translations.en.{$key}");

        if ($value === null) {
            return $fallback ?? $key;
        }

        return (string) $value;
    }
}

if (!function_exists('pn_theme_groups')) {
    /**
     * The shipped theme lineup (v0.6.0), grouped for pickers/docs.
     *
     * Reads the canonical `src/resources/themes/lineup.json` (the same file
     * that generates the theme CSS via gen-themes.mjs, so a picker built on
     * this helper can never drift from the shipped `[data-theme]` blocks)
     * and returns:
     *
     *   [
     *     'Brand'    => [['name' => 'pinion', 'light' => 'pinion', 'dark' => 'pinion-dark'], ...],
     *     'Mood'     => [...],
     *     'SaaS'     => [...],
     *     'Industry' => [...],
     *   ]
     *
     * Naming convention: `<name>` = light, `<name>-dark` = dark; only the
     * every theme follows `<name>` = light / `<name>-dark` = dark, including
     * the brand default pair `pinion` / `pinion-dark`.
     * The opt-in `reactive` theme is NOT part of the lineup (hand-maintained,
     * light-only, for the /visualize report tooling) and is not returned.
     */
    function pn_theme_groups(): array
    {
        static $groups = null;

        if ($groups !== null) {
            return $groups;
        }

        $categoryLabels = [
            'ブランド既定' => 'Brand',
            '美学系 (mood)' => 'Mood',
            'SaaS 実用' => 'SaaS',
            '業種特化' => 'Industry',
            'トーナル (tonal)' => 'Tonal',
        ];

        $lineup = json_decode(
            file_get_contents(__DIR__ . '/resources/themes/lineup.json'),
            true
        );

        $groups = array_fill_keys(array_values($categoryLabels), []);

        foreach ($lineup['themes'] as $theme) {
            $group = $categoryLabels[$theme['category']] ?? 'Industry';
            $groups[$group][] = [
                'name' => $theme['name'],
                'light' => $theme['name'],
                'dark' => $theme['name'] . '-dark',
                'cat' => $group,
            ];
        }

        return $groups;
    }
}

if (!function_exists('pn_tunes')) {
    /**
     * The shipped tune ids, in picker order. Single source for the switchers
     * and for pn_look_resolve()'s allowlist when the app doesn't narrow it.
     */
    function pn_tunes(): array
    {
        return ['default', 'minimal', 'sharp', 'corporate', 'tech', 'brutal', 'editorial', 'luxury', 'soft', 'pixel', 'draft', 'proto'];
    }
}

if (!function_exists('pn_backdrops')) {
    /**
     * <x-backdrop> patterns (v0.19.0). Each takes an optional size suffix
     * (`grid-s`, `flow-l`); `m` when omitted. `flow` is the animated one.
     */
    function pn_backdrops(): array
    {
        return ['none', 'flow', 'glow', 'grid', 'dot', 'ndot', 'ring', 'cross', 'rule', 'diag', 'paper', 'chev', 'sig'];
    }
}

if (!function_exists('pn_backdrop_parse')) {
    /**
     * `grid-s` → ['grid', 's']. null when the pattern is unknown, the size
     * is not s|m|l, or the pattern is outside `$allowed` (null = all).
     */
    function pn_backdrop_parse(string $value, ?array $allowed = null): ?array
    {
        if (!preg_match('/^([a-z]+)(?:-([sml]))?$/', $value, $m)) {
            return null;
        }
        $pattern = $m[1];
        if (!in_array($pattern, $allowed ?? pn_backdrops(), true) || !in_array($pattern, pn_backdrops(), true)) {
            return null;
        }

        return [$pattern, $m[2] ?? 'm'];
    }
}

if (!function_exists('pn_theme_groups_for')) {
    /**
     * pn_theme_groups() narrowed to the given theme FAMILIES (bare names:
     * `glacier`, not `glacier-dark`). null = the whole lineup. Groups left
     * empty by the filter are dropped, so the picker shows no bare headings.
     * Order follows the lineup, not the argument.
     */
    function pn_theme_groups_for(?array $families): array
    {
        if ($families === null) {
            return pn_theme_groups();
        }

        $out = [];
        foreach (pn_theme_groups() as $label => $items) {
            $kept = array_values(array_filter($items, fn ($t) => in_array($t['name'], $families, true)));
            if ($kept) {
                $out[$label] = $kept;
            }
        }

        return $out;
    }
}

if (!function_exists('pn_look_resolve')) {
    /**
     * Resolve the page's theme × tune (v0.19.0). Pure: no config, no request.
     *
     * `$look` is the `pinion-ui.look` config block; `$query` is the request's
     * query string. `?theme=` / `?tune=` win when `query` is on AND the value
     * is in the allowlist — anything else falls back to the default, so a
     * hand-typed URL can never put an unknown name into data-theme (which
     * would drop every color on the page).
     *
     * Light/dark is carried by the `-dark` suffix, so the allowlist is checked
     * against the FAMILY (`estate-dark` passes when `estate` is allowed).
     *
     * `?bg=` picks the <x-backdrop> pattern the same way (`look.backdrops`).
     *
     * @return array{theme: string, tune: string, backdrop: string}
     */
    function pn_look_resolve(array $look, array $query = []): array
    {
        $families = $look['themes'] ?? null;
        $tunes = $look['tunes'] ?? null;
        $theme = (string) ($look['theme'] ?? 'pinion');
        $tune = (string) ($look['tune'] ?? 'default');
        $backdrops = $look['backdrops'] ?? null;
        $backdrop = (string) ($look['backdrop'] ?? 'none');

        if (!($look['query'] ?? true)) {
            return ['theme' => $theme, 'tune' => $tune, 'backdrop' => $backdrop];
        }

        $allFamilies = [];
        foreach (pn_theme_groups() as $items) {
            foreach ($items as $t) {
                $allFamilies[] = $t['name'];
            }
        }
        $okFamilies = $families ?? $allFamilies;
        $okTunes = $tunes ?? pn_tunes();

        $q = is_string($query['theme'] ?? null) ? $query['theme'] : '';
        if ($q !== '' && in_array(preg_replace('/-dark$/', '', $q), $okFamilies, true)) {
            $theme = $q;
        }

        $q = is_string($query['tune'] ?? null) ? $query['tune'] : '';
        if ($q !== '' && in_array($q, $okTunes, true)) {
            $tune = $q;
        }

        $q = is_string($query['bg'] ?? null) ? $query['bg'] : '';
        if ($q !== '' && pn_backdrop_parse($q, $backdrops) !== null) {
            $backdrop = $q;
        }

        return ['theme' => $theme, 'tune' => $tune, 'backdrop' => $backdrop];
    }
}

if (!function_exists('pn_look')) {
    /**
     * pn_look_resolve() against the app's config and the current request.
     * Use it on <html>: `data-theme="{{ pn_look()['theme'] }}"`.
     * Never throws — a stale config cache without the `look` block (a new
     * config key after `config:cache`) falls back to the built-in defaults
     * instead of taking the whole app (and artisan) down with it.
     */
    function pn_look(): array
    {
        try {
            $look = (array) config('pinion-ui.look', []);
            $query = function_exists('request') ? (array) request()->query() : [];
        } catch (\Throwable) {
            $look = [];
            $query = [];
        }

        return pn_look_resolve($look, $query);
    }
}
