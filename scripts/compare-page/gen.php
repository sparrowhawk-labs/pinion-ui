#!/usr/bin/env php
<?php
/**
 * compare-page/gen.php — spec JSON → index.html
 *
 *   php scripts/compare-page/gen.php <spec.json> <out-dir>
 *
 * Renders a before / after comparison grid: one section per theme (light /
 * dark pairs), one column per semantic color, one row per component state.
 * Every cell stacks the OLD markup above the NEW markup so a 1px rim or a
 * shifted fill is visible side by side. The page carries a tune switcher and
 * a ×2 zoom toggle. See README.md for the spec format.
 *
 * Markup sources per row (pick one, all get `{c}` / `{tone}` / `{id}` expanded):
 *   html     — literal template
 *   compose  — a src/Compose/<Class>Composer::compose(props) result, inserted
 *              into `html` through `{{key}}` placeholders (the real classes)
 *   blade    — a `'key' => '...'` entry from a Blade component's variant map,
 *              inserted through `{{variant}}`
 * The OLD markup is the NEW markup with `rewrite` pairs applied (new → old),
 * unless the row gives an explicit `old` template. `html_by_color` /
 * `old_by_color` override the template for one color (e.g. a neutral variant
 * that is written differently in the component).
 */

declare(strict_types=1);

if ($argc < 3) {
    fwrite(STDERR, "usage: php gen.php <spec.json> <out-dir>\n");
    exit(1);
}

[$_, $specPath, $outDir] = $argv;
$root = dirname(__DIR__, 2);

$spec = json_decode((string) file_get_contents($specPath), true, 512, JSON_THROW_ON_ERROR);
foreach (glob("$root/src/Compose/*.php") as $php) {
    require_once $php;
}

// ---------------------------------------------------------------- defaults
$colors = $spec['colors'] ?? ['primary', 'secondary', 'accent', 'neutral', 'info', 'success', 'warning', 'error'];
$themes = $spec['themes'] ?? [
    ['pinion', 'ブランド既定 · light'], ['pinion-dark', 'ブランド既定 · dark'],
    ['pop', 'Mood · light'], ['pop-dark', 'Mood · dark'],
    ['vapor', 'Mood · light'], ['vapor-dark', 'Mood · dark'],
    ['kids', 'Mood · light'], ['kids-dark', 'Mood · dark'],
    ['signal', 'Tonal · light'], ['signal-dark', 'Tonal · dark'],
    ['frost', 'Tonal · light'], ['frost-dark', 'Tonal · dark'],
];
$tunes = $spec['tunes'] ?? [['default', 'default'], ['brutal', 'brutal（太枠）'], ['pixel', 'pixel']];
$oldLabel = $spec['old'] ?? '旧';
$newLabel = $spec['new'] ?? '新';
$pageRewrite = $spec['rewrite'] ?? [];
$toneNeutral = $spec['tone_neutral'] ?? null;

$seq = 0;

// ---------------------------------------------------------------- helpers
function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function expandVars(string $s, array $vars): string
{
    return str_replace(array_keys($vars), array_values($vars), $s);
}

/** Expand a row's markup template for one color. `{id}` is left in place. */
function expandTemplate(array $row, array $vars, string $root): string
{
    $html = $row['html'] ?? '';
    $html = expandVars($html, $vars);

    if (isset($row['compose'])) {
        $class = 'SparrowhawkLabs\\PinionUi\\Compose\\' . $row['compose']['class'] . 'Composer';
        if (!class_exists($class)) {
            throw new RuntimeException("Composer not found: $class");
        }
        $props = [];
        foreach ($row['compose']['props'] ?? [] as $k => $v) {
            $props[$k] = is_string($v) ? expandVars($v, $vars) : $v;
        }
        $parts = $class::compose($props);
        foreach ($parts as $k => $v) {
            if (is_string($v)) {
                $html = str_replace('{{' . $k . '}}', $v, $html);
            }
        }
    }

    if (isset($row['blade'])) {
        $file = "$root/src/resources/views/components/{$row['blade']['file']}.blade.php";
        $src = (string) file_get_contents($file);
        $key = expandVars($row['blade']['key'], $vars);
        if (!preg_match("/'" . preg_quote($key, '/') . "'\s*=>\s*'([^']*)'/", $src, $m)) {
            throw new RuntimeException("variant '$key' not found in $file");
        }
        $html = str_replace('{{variant}}', $m[1], $html);
    }

    if (preg_match('/\{\{[a-zA-Z_]+\}\}/', $html, $left)) {
        throw new RuntimeException("unresolved placeholder {$left[0]} in row '{$row['label']}'");
    }

    return $html;
}

function applyRewrite(string $html, array $pairs, array $vars): string
{
    foreach ($pairs as [$from, $to]) {
        $html = str_replace(expandVars($from, $vars), expandVars($to, $vars), $html);
    }
    return $html;
}

function variantBlock(string $label, string $markup): string
{
    return '<div class="flex items-center gap-2"><span class="text-[10px] opacity-50 w-3">' . h($label) . '</span>' . $markup . '</div>';
}

function cell(array $row, string $c, array $ctx): string
{
    global $seq;
    $dash = '<span class="text-xs opacity-40">—</span>';

    if (!empty($row['only']) && !in_array($c, $row['only'], true)) {
        return '<td class="p-2 align-top"><div class="flex flex-col gap-2">'
            . variantBlock('旧', $dash) . variantBlock('新', $dash) . '</div></td>';
    }

    $toneNeutral = $row['tone_neutral'] ?? $ctx['tone_neutral'];
    $tone = ($c === 'neutral' && $toneNeutral) ? $toneNeutral : $c;
    $vars = ['{c}' => $c, '{tone}' => $tone, '{C}' => ucfirst($c)];

    if (isset($row['html_by_color'][$c])) {
        $row['html'] = $row['html_by_color'][$c];
    }
    if (isset($row['old_by_color'][$c])) {
        $row['old'] = $row['old_by_color'][$c];
    }
    $newT = expandTemplate($row, $vars, $ctx['root']);
    if (isset($row['old'])) {
        $oldT = expandTemplate(['html' => $row['old'], 'label' => $row['label']] + array_intersect_key($row, ['compose' => 1, 'blade' => 1]), $vars, $ctx['root']);
    } else {
        $oldT = applyRewrite($newT, $row['rewrite'] ?? $ctx['rewrite'], $vars);
    }

    $old = str_replace('{id}', 'o' . (++$seq), $oldT);
    $new = str_replace('{id}', 'n' . (++$seq), $newT);

    return '<td class="p-2 align-top"><div class="flex flex-col gap-2">'
        . variantBlock('旧', $old) . variantBlock('新', $new) . '</div></td>';
}

// ---------------------------------------------------------------- render
$ctx = ['root' => $root, 'rewrite' => $pageRewrite, 'tone_neutral' => $toneNeutral];

$tuneButtons = '';
foreach ($tunes as $i => [$tune, $label]) {
    $tuneButtons .= '<button data-tune-btn="' . h($tune) . '"' . ($i === 0 ? ' aria-pressed="true"' : '') . '>' . h($label) . '</button>';
}
$themeLinks = '';
foreach ($themes as [$theme]) {
    $themeLinks .= ' <a href="#' . h($theme) . '" class="underline">' . h($theme) . '</a>';
}

$thead = '<thead><tr><th class="p-2"></th>';
foreach ($colors as $c) {
    $thead .= '<th class="text-left font-normal text-xs p-2 align-bottom"><div class="font-semibold">' . h($c) . '</div><div class="opacity-60">' . h($oldLabel) . ' ／ ' . h($newLabel) . '</div></th>';
}
$thead .= '</tr></thead>';

$sections = '';
foreach ($themes as [$theme, $note]) {
    $sections .= '<section data-theme="' . h($theme) . '" class="bg-base-200 text-base-content p-4 rounded-lg" id="' . h($theme) . '">'
        . '<h2 class="text-lg font-semibold mb-1">' . h($theme) . ' <span class="text-xs font-normal opacity-60">' . h($note) . '</span></h2>' . "\n"
        . '<div class="bg-base-100 rounded-lg overflow-x-auto p-2"><table class="text-sm w-full min-w-[1400px] border-separate border-spacing-0">' . $thead . '<tbody>' . "\n";
    foreach ($spec['rows'] as $row) {
        $sections .= '<tr class="border-t border-base-content/10"><th class="text-left font-normal text-xs opacity-70 p-2 whitespace-nowrap align-top">' . h($row['label']) . '</th>' . "\n";
        foreach ($colors as $c) {
            $sections .= cell($row, $c, $ctx) . "\n";
        }
        $sections .= '</tr>' . "\n";
    }
    $sections .= '</tbody></table></div></section>' . "\n";
}

$intro = $spec['intro'] ?? '';
if (is_array($intro)) {
    $intro = implode("\n", array_map(fn ($p) => "<p>$p</p>", $intro));
}

$firstTheme = $themes[0][0];
$firstTune = $tunes[0][0];
$title = $spec['title'] ?? 'pinion-ui compare';
$heading = $spec['heading'] ?? $title;

$html = <<<HTML
<!DOCTYPE html>
<html lang="ja" data-theme="{$firstTheme}" data-tune="{$firstTune}">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$title}</title>
<link rel="stylesheet" href="page.css">
<style>#bar{position:sticky;top:0;z-index:50;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;padding:.5rem .75rem;background:#111;color:#eee;font:13px system-ui,sans-serif}#bar a{color:#9cf}#bar button{font:inherit;padding:.25rem .75rem;border-radius:.375rem;border:1px solid #555;background:#222;color:#eee;cursor:pointer}#bar button[aria-pressed=true]{background:#2563eb;border-color:#2563eb}main.zoom{zoom:2}</style>
</head>
<body class="bg-base-200">
<div id="bar"><b>{$heading}</b> 各セルの上が旧、下が新。tune:
{$tuneButtons} 拡大: <button id="zoom">×2</button> <span style="margin-left:auto">{$themeLinks}</span></div>
<main class="p-4 space-y-6 max-w-[1800px] mx-auto">
<section class="bg-base-100 text-base-content p-4 rounded-lg text-sm space-y-1">
{$intro}
</section>
{$sections}</main>
<script>
document.querySelectorAll('[data-tune-btn]').forEach(b => b.addEventListener('click', () => {
  document.documentElement.setAttribute('data-tune', b.dataset.tuneBtn);
  document.querySelectorAll('[data-tune-btn]').forEach(x => x.setAttribute('aria-pressed', x === b));
}));
document.getElementById('zoom').addEventListener('click', e => { const m = document.querySelector('main'); m.classList.toggle('zoom'); e.target.setAttribute('aria-pressed', m.classList.contains('zoom')); });
</script>
</body></html>
HTML;

if (!is_dir($outDir) && !mkdir($outDir, 0777, true)) {
    fwrite(STDERR, "cannot create $outDir\n");
    exit(1);
}
file_put_contents("$outDir/index.html", $html);
echo "wrote $outDir/index.html (" . count($themes) . ' themes × ' . count($colors) . ' colors × ' . count($spec['rows']) . " rows)\n";
