<?php

/**
 * pinion-ui — PostToolUse hook (installed into .claude/hooks/ by `php artisan ui:install`).
 *
 * After the agent edits a Blade file, run `php artisan ui:lint` on just that file
 * and, if it finds class-vocabulary violations (excluded daisyUI component classes
 * or fixed/hex colors), feed them back INTO THE AGENT'S CONTEXT via
 * `hookSpecificOutput.additionalContext` so the model fixes them in the same loop.
 *
 * Why this exact shape: Claude Code only injects a PostToolUse hook's output into
 * the model when the hook **exits 0 AND prints JSON** carrying `additionalContext`.
 * A non-zero exit with text on stdout is dropped from the model's context (shown to
 * the user only). So we ALWAYS exit 0 and carry the violations inside the JSON.
 *
 * Pure PHP (no jq, no bash escaping) — PHP is guaranteed present in a Laravel app.
 */

$raw = stream_get_contents(STDIN) ?: '';
$in = json_decode($raw, true) ?: [];

$file = $in['tool_input']['file_path'] ?? '';
if (!is_string($file) || !preg_match('/\.blade\.php$/', $file)) {
    exit(0); // not a Blade edit — nothing to do
}

$root = getenv('CLAUDE_PROJECT_DIR') ?: getcwd();
$artisan = $root . '/artisan';

// Only act inside a Laravel app that actually has pinion-ui (so `ui:lint` exists).
if (!is_file($artisan) || !is_dir($root . '/vendor/sparrowhawk-labs/pinion-ui')) {
    exit(0);
}

$cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisan)
     . ' ui:lint ' . escapeshellarg($file) . ' 2>&1';
exec($cmd, $lines, $code);

if ($code === 0) {
    exit(0); // clean — stay silent
}

$report = trim(implode("\n", $lines));

echo json_encode([
    'hookSpecificOutput' => [
        'hookEventName' => 'PostToolUse',
        'additionalContext' =>
            "pinion-ui class-vocabulary lint found violations in the Blade file you just edited:\n\n"
            . $report
            . "\n\nFix them before continuing — use a pinion-ui <x-…> component, a tune class/token for "
            . "shape·space·size, and a daisyUI *semantic* color (bg-primary, text-base-content, …) instead of "
            . "excluded daisyUI component classes (.btn/.card/.badge/…) or fixed/hex colors (bg-blue-500, #1d4ed8). "
            . "Full rule: vendor/sparrowhawk-labs/pinion-ui/AGENTS.md → \"Class vocabulary\". "
            . "Suppress an intentional exception with a `pinion-lint-ignore` comment.",
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

exit(0);
