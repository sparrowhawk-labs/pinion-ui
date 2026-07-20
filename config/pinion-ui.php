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
