<?php

return [

    /*
    |---------------------------------------------------------------------------
    | pinion-ui locale
    |---------------------------------------------------------------------------
    |
    | Selects which `translations` bucket below is used for the small set of
    | component-internal strings (pagination "Previous", select placeholder,
    | rating aria-label, etc).
    |
    | Default (null, since v0.7.3): follow the app's runtime locale
    | (`app()->getLocale()`), so multi-locale apps that call App::setLocale()
    | per request get matching component strings automatically. Set a locale
    | here (or PINION_UI_LOCALE) to pin one — e.g. a Japanese app with
    | English UI strings, without coupling the two.
    |
    | Bundled: `ja`, `en`, `zh-Hans`, `zh-Hant`. Add more by extending the
    | `translations` array. Lookup falls back {locale} → en → the literal
    | key when missing.
    |
    */

    'locale' => env('PINION_UI_LOCALE'),

    /*
    |--------------------------------------------------------------------------
    | Look — theme × tune for the whole app (v0.19.0)
    |--------------------------------------------------------------------------
    |
    | One place for the page's default look, the allowlist, and how the
    | visitor's choice is remembered. Read by pn_look() (server: put it on
    | <html>), <x-look-head /> (client: applies the remembered choice BEFORE
    | first paint, so there is no flash of the default theme), and the two
    | switchers (their lineup + storage key default to these values).
    |
    | themes  null = the whole lineup, or a list of FAMILIES (bare names —
    |         `glacier` allows both `glacier` and `glacier-dark`).
    | tunes   null = every tune, or a list of tune ids.
    | query   true = `?theme=` / `?tune=` override (allowlist-checked). A
    |         link you share wins over the recipient's remembered choice.
    | storage_key  localStorage prefix (`<key>-theme`, `<key>-tune`).
    | backdrop / backdrops  the default <x-backdrop> pattern and the `?bg=`
    |         allowlist (null = every pattern). `flow` is the animated one.
    |
    */

    'look' => [
        'themes' => null,
        'theme' => env('PINION_THEME', 'pinion'),
        'tunes' => null,
        'tune' => env('PINION_TUNE', 'default'),
        'query' => true,
        'storage_key' => 'pn',
        // <x-backdrop> with no pattern prop: 'none' | 'flow' | 'grid-s' … (pn_backdrops())
        'backdrop' => env('PINION_BACKDROP', 'none'),
        'backdrops' => null,
    ],


    /*
    |---------------------------------------------------------------------------
    | Component string translations
    |---------------------------------------------------------------------------
    |
    | Keys are nested by component, looked up via `pn_trans('select.placeholder')`
    | which maps to `translations.<locale>.select.placeholder`. Component props
    | (e.g. `prevLabel` on <x-pagination.full>) still override these — these
    | are only the *default* values when the caller does not supply one.
    |
    */

    'translations' => [

        'ja' => [
            'select' => [
                'placeholder' => '選択',
            ],
            'notification' => [
                'close' => '閉じる',
            ],
            'rating' => [
                'none' => '評価なし',
            ],
            'table_scroll' => [
                'prev' => '前へスクロール',
                'next' => '次へスクロール',
            ],
            'pagination' => [
                'prev' => '前へ',
                'next' => '次へ',
                'info' => '全 :total 件中 :first - :last 件',
                'aria' => 'ページネーション',
            ],
        ],

        'en' => [
            'select' => [
                'placeholder' => 'Select',
            ],
            'notification' => [
                'close' => 'Close',
            ],
            'rating' => [
                'none' => 'No rating',
            ],
            'table_scroll' => [
                'prev' => 'Scroll previous',
                'next' => 'Scroll next',
            ],
            'pagination' => [
                'prev' => 'Previous',
                'next' => 'Next',
                'info' => ':first–:last of :total',
                'aria' => 'Pagination',
            ],
        ],

        'zh-Hans' => [
            'select' => [
                'placeholder' => '请选择',
            ],
            'notification' => [
                'close' => '关闭',
            ],
            'rating' => [
                'none' => '未评分',
            ],
            'table_scroll' => [
                'prev' => '向前滚动',
                'next' => '向后滚动',
            ],
            'pagination' => [
                'prev' => '上一页',
                'next' => '下一页',
                'info' => '共 :total 条中第 :first–:last 条',
                'aria' => '分页',
            ],
        ],

        'zh-Hant' => [
            'select' => [
                'placeholder' => '請選擇',
            ],
            'notification' => [
                'close' => '關閉',
            ],
            'rating' => [
                'none' => '未評分',
            ],
            'table_scroll' => [
                'prev' => '向前捲動',
                'next' => '向後捲動',
            ],
            'pagination' => [
                'prev' => '上一頁',
                'next' => '下一頁',
                'info' => '共 :total 筆中第 :first–:last 筆',
                'aria' => '分頁',
            ],
        ],

    ],

];
