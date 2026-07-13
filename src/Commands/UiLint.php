<?php

namespace SparrowhawkLabs\PinionUi\Commands;

use Illuminate\Console\Command;
use SparrowhawkLabs\PinionUi\Linting\ClassVocabularyLinter;
use SparrowhawkLabs\PinionUi\Linting\SpacingUsageScanner;

/**
 * `php artisan ui:lint` — scan Blade files for class-vocabulary violations:
 * excluded daisyUI component classes (silent no-ops) and fixed/hex colors
 * (which ignore data-theme). See AGENTS.md -> "Class vocabulary".
 *
 * Exits non-zero when violations are found, so it can gate CI / pre-commit /
 * a Claude Code PostToolUse hook.
 *
 * `--spacing` appends an INFORMATIONAL census of spacing tokens (rhythmic
 * t-shirt vs optical numeric/arbitrary — see SpacingUsageScanner). It never
 * affects the exit code: rhythmic-vs-optical is designer intent, not a
 * machine-checkable rule.
 */
class UiLint extends Command
{
    protected $signature = 'ui:lint
        {path?* : Files or directories to scan (default: resources/views)}
        {--spacing : Append a non-gating spacing usage census (rhythmic t-shirt vs optical numeric)}
        {--json : Output machine-readable JSON}';

    protected $description = 'Lint Blade markup against the pinion-ui class-vocabulary rule';

    public function handle(): int
    {
        $paths = $this->argument('path') ?: [base_path('resources/views')];
        $files = $this->collectBladeFiles($paths);

        if (empty($files)) {
            if ($this->option('json')) {
                $this->line(json_encode(['violations' => [], 'files' => 0], JSON_UNESCAPED_SLASHES));
            } else {
                $this->warn('No Blade files found to lint.');
            }

            return Command::SUCCESS;
        }

        $linter = new ClassVocabularyLinter();
        $all = [];
        foreach ($files as $file) {
            foreach ($linter->lintFile($file) as $v) {
                $all[] = $v;
            }
        }

        $spacing = $this->option('spacing') ? $this->spacingCensus($files) : null;

        if ($this->option('json')) {
            $payload = ['violations' => $all, 'files' => count($files)];
            if ($spacing !== null) {
                $payload['spacing'] = $spacing;
            }
            $this->line(json_encode($payload, JSON_UNESCAPED_SLASHES));

            return empty($all) ? Command::SUCCESS : Command::FAILURE;
        }

        if (empty($all)) {
            $this->info("✓ pinion-ui class vocabulary: clean (" . count($files) . " files).");
            if ($spacing !== null) {
                $this->renderSpacing($spacing);
            }

            return Command::SUCCESS;
        }

        $this->newLine();
        $this->error('  pinion-ui class-vocabulary violations');
        $this->line('  ─────────────────────────────────────');

        $byFile = [];
        foreach ($all as $v) {
            $byFile[$v['file']][] = $v;
        }
        foreach ($byFile as $file => $vs) {
            $this->newLine();
            $this->line('  <fg=cyan>' . $this->relative($file) . '</>');
            foreach ($vs as $v) {
                $tag = match ($v['kind']) {
                    'fixed-color' => '<fg=magenta>color</>',
                    'missing-data-theme' => '<fg=cyan>theme</>',
                    'missing-data-tune' => '<fg=blue>tune</>',
                    default => '<fg=yellow>daisyUI</>',
                };
                $this->line("    <fg=gray>L{$v['line']}</> {$tag}  <fg=red>{$v['token']}</>  <fg=gray>{$v['message']}</>");
            }
        }

        $this->newLine();
        $this->error('  ' . count($all) . ' violation(s) across ' . count($byFile) . ' file(s).');
        $this->line('  Rule: plain Tailwind, except daisyUI semantic colors (color) and pinion-ui tune classes/tokens (shape · space · size).');
        $this->line('  Full rule: vendor/sparrowhawk-labs/pinion-ui/AGENTS.md -> "Class vocabulary".');
        $this->newLine();

        if ($spacing !== null) {
            $this->renderSpacing($spacing);
        }

        return Command::FAILURE;
    }

    /**
     * Count spacing tokens across the given files.
     * Returns ['counts' => [kind => n], 'optical' => occurrence list].
     */
    private function spacingCensus(array $files): array
    {
        $scanner = new SpacingUsageScanner();
        $counts = ['rhythmic' => 0, 'numeric' => 0, 'arbitrary' => 0];
        $optical = [];
        foreach ($files as $file) {
            foreach ($scanner->scanFile($file) as $o) {
                $counts[$o['kind']]++;
                if ($o['kind'] !== 'rhythmic') {
                    $optical[] = $o;
                }
            }
        }

        return ['counts' => $counts, 'optical' => $optical];
    }

    private function renderSpacing(array $spacing): void
    {
        $c = $spacing['counts'];
        $total = array_sum($c);

        $this->newLine();
        $this->line('  spacing usage census <fg=gray>(informational — never fails the build)</>');
        $this->line('  ─────────────────────────────────────');

        if ($total === 0) {
            $this->line('  <fg=gray>no spacing tokens found.</>');
            $this->newLine();

            return;
        }

        $pct = fn (int $n) => str_pad((string) round($n / $total * 100), 3, ' ', STR_PAD_LEFT) . '%';
        $this->line("  rhythmic (t-shirt):   <fg=green>{$c['rhythmic']}</>  {$pct($c['rhythmic'])}");
        $this->line("  optical  (numeric):   <fg=yellow>{$c['numeric']}</>  {$pct($c['numeric'])}");
        $this->line("  optical  (arbitrary): <fg=yellow>{$c['arbitrary']}</>  {$pct($c['arbitrary'])}");

        if (! empty($spacing['optical'])) {
            $byFile = [];
            foreach ($spacing['optical'] as $o) {
                $byFile[$o['file']][] = "<fg=gray>L{$o['line']}</> {$o['token']}";
            }
            $this->newLine();
            $this->line('  optical locations:');
            foreach ($byFile as $file => $hits) {
                $this->line('    <fg=cyan>' . $this->relative($file) . '</>  ' . implode('  ', $hits));
            }
        }

        $this->line('  <fg=gray>Convention: t-shirt = rhythmic (tune-reactive), numeric = optical fixed nudge — AGENTS.md -> "Rhythmic vs optical spacing".</>');
        $this->newLine();
    }

    /** @param string[] $paths */
    private function collectBladeFiles(array $paths): array
    {
        $files = [];
        foreach ($paths as $path) {
            if (is_file($path)) {
                if (str_ends_with($path, '.blade.php')) {
                    $files[] = $path;
                }
                continue;
            }
            if (is_dir($path)) {
                $it = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
                );
                foreach ($it as $f) {
                    if ($f->isFile() && str_ends_with($f->getFilename(), '.blade.php')) {
                        $files[] = $f->getPathname();
                    }
                }
            }
        }

        return array_values(array_unique($files));
    }

    private function relative(string $path): string
    {
        $base = base_path();

        return str_starts_with($path, $base) ? ltrim(substr($path, strlen($base)), '/') : $path;
    }
}
