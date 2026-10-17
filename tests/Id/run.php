<?php

declare(strict_types=1);

// pn_id / pn_id_reset — pure PHP path (no container → static store).
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

pn_id_reset();
check('first occurrence is _1', pn_id('email'), 'email_1');
check('same base counts up', pn_id('email'), 'email_2');
check('other base has its own counter', pn_id('input'), 'input_1');
check('existing base keeps counting', pn_id('email'), 'email_3');
check('brackets sanitised', pn_id('user[email]'), 'user_email_1');
check('sanitised base shares the counter with its clean form', pn_id('user_email'), 'user_email_2');
check('radio base name_value', pn_id('plan_pro'), 'plan_pro_1');
check('trailing separator trimmed (radio with null value)', pn_id('plan_'), 'plan_1');
check('unicode collapses to a single underscore', pn_id('氏名 ふりがな'), 'pn_1');
check('empty base falls back to pn', pn_id(''), 'pn_2');
check('dash and underscore are kept', pn_id('first-name_x'), 'first-name_x_1');

pn_id_reset();
check('reset restarts at _1', pn_id('email'), 'email_1');
check('reset clears every base', pn_id('input'), 'input_1');

echo "\n--- Summary: $pass pass, $fail fail ---\n";
exit($fail > 0 ? 1 : 0);
