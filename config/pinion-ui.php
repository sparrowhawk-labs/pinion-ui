<?php

return [

    /*
    |---------------------------------------------------------------------------
    | pinion-ui locale
    |---------------------------------------------------------------------------
    |
    | Selects which `translations` bucket below is used for the small set of
    | component-internal strings (pagination "Previous", select placeholder,
    | rating aria-label, etc). Independent of Laravel's `config('app.locale')`
    | on purpose — you may want a Japanese app with English UI strings, or
    | the opposite, without coupling the two.
    |
    | Supported by default: `ja`, `en`. Add more locales by extending the
    | `translations` array. Falls back to the literal key if the locale or
    | key is missing.
    |
    */

    'locale' => env('PINION_UI_LOCALE', 'ja'),

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

    ],

];
