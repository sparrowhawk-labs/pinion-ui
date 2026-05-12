<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$fixturesDir = "$root/tests/fixtures/compose";
$composerDir = "$root/src/Compose";

if (!is_dir($fixturesDir)) {
    fwrite(STDERR, "fixtures dir not found: $fixturesDir\n");
    exit(1);
}

// Pre-load shared helpers so dependent Composers can find them.
foreach (glob("$composerDir/*.php") as $php) {
    require_once $php;
}

$pass = 0;
$fail = 0;
$skipped = 0;

foreach (glob("$fixturesDir/*.json") as $fixturePath) {
    $name = basename($fixturePath, '.json');
    // hyphenated fixture name → PascalCase Composer (file-upload → FileUploadComposer)
    $class = str_replace(' ', '', ucwords(str_replace('-', ' ', $name))) . 'Composer';
    $classPath = "$composerDir/$class.php";

    if (!file_exists($classPath)) {
        echo "SKIP  $name (no $class.php)\n";
        $skipped++;
        continue;
    }

    require_once $classPath;
    $fqcn = "SparrowhawkLabs\\PinionUi\\Compose\\$class";

    if (!class_exists($fqcn)) {
        echo "SKIP  $name ($fqcn not found after require)\n";
        $skipped++;
        continue;
    }

    $cases = json_decode(file_get_contents($fixturePath), true);
    if (!is_array($cases)) {
        echo "SKIP  $name (invalid fixture json)\n";
        $skipped++;
        continue;
    }

    echo "\n=== $name ===\n";
    foreach ($cases as $case) {
        $got = $fqcn::compose($case['props'] ?? []);
        $expected = $case['expected'] ?? [];
        $mismatch = [];
        foreach ($expected as $k => $v) {
            if (($got[$k] ?? null) !== $v) {
                $mismatch[$k] = ['expected' => $v, 'got' => $got[$k] ?? '(missing)'];
            }
        }

        if (empty($mismatch)) {
            $pass++;
            echo "  PASS  {$case['name']}\n";
        } else {
            $fail++;
            echo "  FAIL  {$case['name']}\n";
            foreach ($mismatch as $k => $diff) {
                echo "    [$k]\n      expected: {$diff['expected']}\n      got:      {$diff['got']}\n";
            }
        }
    }
}

echo "\n--- Summary: $pass pass, $fail fail, $skipped skipped ---\n";
exit($fail > 0 ? 1 : 0);
