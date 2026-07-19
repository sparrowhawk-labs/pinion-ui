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
        $locale = config('pinion-ui.locale', 'ja');
        $value = config("pinion-ui.translations.{$locale}.{$key}");

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
