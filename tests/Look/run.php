<?php

declare(strict_types=1);

// pn_look_resolve / pn_backdrop_parse / pn_theme_groups_for — pure, no Laravel.
require dirname(__DIR__, 2) . '/src/helpers.php';

$pass = 0;
$fail = 0;
function check(string $name, $got, $want): void
{
    global $pass, $fail;
    if ($got === $want) {
        $pass++;
        echo "PASS  $name\n";
    } else {
        $fail++;
        echo "FAIL  $name\n  want: " . json_encode($want) . "\n  got:  " . json_encode($got) . "\n";
    }
}

$look = ['themes' => ['glacier', 'carbon'], 'theme' => 'glacier-dark', 'tunes' => ['proto', 'tech'], 'tune' => 'proto', 'query' => true, 'backdrop' => 'flow', 'backdrops' => ['flow', 'grid', 'none']];

check('defaults', pn_look_resolve($look, []), ['theme' => 'glacier-dark', 'tune' => 'proto', 'backdrop' => 'flow']);
check('allowed ?theme= wins', pn_look_resolve($look, ['theme' => 'carbon'])['theme'], 'carbon');
check('-dark checked by family', pn_look_resolve($look, ['theme' => 'carbon-dark'])['theme'], 'carbon-dark');
check('outside allowlist falls back', pn_look_resolve($look, ['theme' => 'estate'])['theme'], 'glacier-dark');
check('unknown name falls back', pn_look_resolve($look, ['theme' => '"><script>'])['theme'], 'glacier-dark');
check('array query ignored', pn_look_resolve($look, ['theme' => ['x']])['theme'], 'glacier-dark');
check('tune allowlist', pn_look_resolve($look, ['tune' => 'pixel'])['tune'], 'proto');
check('tune allowed', pn_look_resolve($look, ['tune' => 'tech'])['tune'], 'tech');
check('?bg= with size', pn_look_resolve($look, ['bg' => 'grid-s'])['backdrop'], 'grid-s');
check('?bg= outside allowlist', pn_look_resolve($look, ['bg' => 'sig'])['backdrop'], 'flow');
check('query off ignores URL', pn_look_resolve(['query' => false] + $look, ['theme' => 'carbon'])['theme'], 'glacier-dark');
check('null allowlist = whole lineup', pn_look_resolve(['theme' => 'pinion'], ['theme' => 'estate-dark'])['theme'], 'estate-dark');
check('empty look = built-in defaults', pn_look_resolve([], []), ['theme' => 'pinion', 'tune' => 'default', 'backdrop' => 'none']);

check('parse flow', pn_backdrop_parse('flow'), ['flow', 'm']);
check('parse grid-l', pn_backdrop_parse('grid-l'), ['grid', 'l']);
check('parse bad size', pn_backdrop_parse('grid-x'), null);
check('parse unknown', pn_backdrop_parse('lava'), null);

$g = pn_theme_groups_for(['glacier', 'estate']);
$names = [];
foreach ($g as $items) {
    foreach ($items as $t) {
        $names[] = $t['name'];
    }
}
sort($names);
check('groups narrowed to families', $names, ['estate', 'glacier']);
check('empty groups dropped', count($g) <= 2, true);
check('null families = full lineup', pn_theme_groups_for(null), pn_theme_groups());

echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
