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
