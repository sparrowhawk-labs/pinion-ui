#!/usr/bin/env php
<?php
/**
 * compare-page/index.php — specs/*.json の meta から比較ページ一覧の Markdown を生成する
 *
 *   php scripts/compare-page/index.php                 # 標準出力に md
 *   php scripts/compare-page/index.php --write         # ~/claude/docs/visualized/src/pinion-compare-index.md に書く
 *   php scripts/compare-page/index.php --write --rebuild   # さらに visualize-build --rebuild で html を更新
 *   php scripts/compare-page/index.php --out <md> [--html <html>] [--pages <dir>]
 *
 * 各 spec の `meta`:
 *   name      一覧に出す短い題（32 字以内）
 *   version   出荷した版（v0.25.1）か「候補」（未決の比較）
 *   compared  何を比べたか（64 字以内）
 *   page      出力ディレクトリ名（<pages>/<page>/index.html）
 *   scale     省略可。無ければ spec の themes × colors × rows と <page>/shots/ の枚数から作る
 * 並びは「候補」→ 版の新しい順。meta の無い spec は一覧に出ない（警告を出す）。
 */

declare(strict_types=1);

$opts = ['out' => null, 'html' => null, 'pages' => null, 'write' => false, 'rebuild' => false];
for ($i = 1; $i < $argc; $i++) {
    $a = $argv[$i];
    if ($a === '--write' || $a === '--rebuild') { $opts[substr($a, 2)] = true; continue; }
    if (in_array($a, ['--out', '--html', '--pages'], true)) { $opts[substr($a, 2)] = $argv[++$i] ?? null; continue; }
    fwrite(STDERR, "unknown option $a\n"); exit(1);
}

$home = getenv('HOME') ?: '';
$pages = $opts['pages'] ?? "$home/claude/docs/visualized";
$outMd = $opts['out'] ?? "$pages/src/pinion-compare-index.md";
$outHtml = $opts['html'] ?? "$pages/pinion-compare-index.html";

$rows = [];
foreach (glob(__DIR__ . '/specs/*.json') as $path) {
    $spec = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $m = $spec['meta'] ?? null;
    if (!$m) { fwrite(STDERR, "no meta: " . basename($path) . " — 一覧に出ない\n"); continue; }
    foreach (['name', 'version', 'compared', 'page'] as $k) {
        if (empty($m[$k])) { fwrite(STDERR, "meta.$k missing: " . basename($path) . "\n"); exit(1); }
    }
    if (mb_strlen($m['name']) > 32) { fwrite(STDERR, "meta.name > 32 字: " . basename($path) . "\n"); exit(1); }
    if (mb_strlen($m['compared']) > 64) { fwrite(STDERR, "meta.compared > 64 字: " . basename($path) . "\n"); exit(1); }

    $scale = $m['scale'] ?? null;
    if ($scale === null) {
        $themes = count($spec['themes'] ?? []);
        $colors = count($spec['colors'] ?? ['primary', 'secondary', 'accent', 'neutral', 'info', 'success', 'warning', 'error']);
        $nrows = count($spec['rows'] ?? []);
        $shots = count(glob("$pages/{$m['page']}/shots/*.png") ?: []);
        $scale = "$themes テーマ × $colors 色 × $nrows 行" . ($shots ? "・shots $shots" : '');
    }
    $exists = is_file("$pages/{$m['page']}/index.html");
    if (!$exists) { fwrite(STDERR, "page not built: $pages/{$m['page']}/index.html (" . basename($path) . ")\n"); }
    $rows[] = ['spec' => basename($path, '.json'), 'scale' => $scale] + $m;
}

usort($rows, function ($a, $b) {
    $ka = $a['version'] === '候補' ? PHP_INT_MAX : (int) str_replace('.', '', sprintf('%03d%03d%03d', ...array_map('intval', explode('.', ltrim($a['version'], 'v')) + [0, 0, 0])));
    $kb = $b['version'] === '候補' ? PHP_INT_MAX : (int) str_replace('.', '', sprintf('%03d%03d%03d', ...array_map('intval', explode('.', ltrim($b['version'], 'v')) + [0, 0, 0])));
    return $kb <=> $ka ?: strcmp($a['name'], $b['name']);
});

$md = "## 一覧（新しい順）\n\n| ページ | 版 | 何を比べたか | 規模・証跡 | spec |\n|---|---|---|---|---|\n";
foreach ($rows as $r) {
    $md .= sprintf("| [%s](%s/index.html) | %s | %s | %s | `%s.json` |\n", $r['name'], $r['page'], $r['version'], $r['compared'], $r['scale'], $r['spec']);
}
$md .= <<<MD

## 読み方

- 各セルは上が旧・下が新。差が出るのは ink ≠ 色のテーマ（dark の neutral・pop light など）
- 上の固定バーで tune（default / brutal / pixel）と ×2 拡大を切り替えられる
- `shots/` は ×2 のスクリーンショット。ページが開けない時の証跡
- 版が「候補」の行は判断待ちの比較（案①②③を同じページに並べたもの）

## 生成と更新

- spec は pinion-ui の `scripts/compare-page/specs/*.json`（README に使い方）
- ページの生成は `build.sh <spec> <出力先>` → `shots.sh <出力先> <テーマ…>`
- この一覧は spec の `meta` から `index.php --write --rebuild` で生成する
- 手で表に行を足さない。新しいページは spec に `meta` を書いて生成し直す

MD;

if (!$opts['write']) { echo $md; exit(0); }
file_put_contents($outMd, $md);
echo "wrote $outMd (" . count($rows) . " rows)\n";
if ($opts['rebuild']) {
    passthru('visualize-build --rebuild ' . escapeshellarg($outHtml), $rc);
    exit($rc);
}
