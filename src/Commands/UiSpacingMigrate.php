<?php

namespace SparrowhawkLabs\PinionUi\Commands;

use Illuminate\Console\Command;
use SparrowhawkLabs\PinionUi\Linting\SpacingMigrator;

/**
 * `php artisan ui:spacing-migrate` — convert optical (numeric) spacing
 * utilities to the nearest rhythmic t-shirt size (`p-4` -> `p-md`,
 * `py-10` -> `py-2xl`), so an existing static-Tailwind page becomes
 * tune-reactive. Dry-run by default; `--write` applies.
 *
 * Mapping/skip rules live in SpacingMigrator (log-space nearest, x1.5 cap,
 * `pinion-lint-ignore` respected). Review the diff after `--write` and
 * re-opticalize any genuine fixed nudges the bulk pass swept up — see
 * AGENTS.md -> "Rhythmic vs optical spacing".
 */
class UiSpacingMigrate extends Command
{
    protected $signature = 'ui:spacing-migrate
        {path?* : Files or directories to migrate (default: resources/views)}
        {--write : Apply the replacements (default is a dry-run preview)}
        {--json : Output machine-readable JSON}';

    protected $description = 'Convert numeric spacing utilities to the nearest tune-reactive t-shirt size (dry-run by default)';

    public function handle(): int
    {
        $paths = $this->argument('path') ?: [base_path('resources/views')];
        $files = $this->collectBladeFiles($paths);
        $write = (bool) $this->option('write');

        $migrator = new SpacingMigrator();
        $changes = [];
        $skipped = [];
        $written = [];

        foreach ($files as $file) {
            $src = @file_get_contents($file);
            if ($src === false) {
                continue;
            }
            $result = $migrator->migrate($src);
            foreach ($result['changes'] as $c) {
                $changes[] = ['file' => $file] + $c;
            }
            foreach ($result['skipped'] as $s) {
                $skipped[] = ['file' => $file] + $s;
            }
            if ($write && $result['source'] !== $src) {
                file_put_contents($file, $result['source']);
                $written[] = $file;
            }
        }

        if ($this->option('json')) {
            $this->line(json_encode([
                'files' => count($files),
                'changes' => $changes,
                'skipped' => $skipped,
                'written' => $write ? array_map(fn ($f) => $this->relative($f), $written) : [],
                'dry_run' => ! $write,
            ], JSON_UNESCAPED_SLASHES));

            return Command::SUCCESS;
        }

        $this->newLine();
        $mode = $write ? 'applied' : 'dry-run — use --write to apply';
        $this->line("  spacing migration — numeric → nearest t-shirt <fg=gray>({$mode})</>");
        $this->line('  ─────────────────────────────────────');

        if (empty($changes) && empty($skipped)) {
            $this->info('  ✓ nothing to migrate (' . count($files) . ' files scanned).');
            $this->newLine();

            return Command::SUCCESS;
        }

        $byFile = [];
        foreach ($changes as $c) {
            $byFile[$c['file']][] = $c;
        }
        foreach ($byFile as $file => $cs) {
            $this->newLine();
            $this->line('  <fg=cyan>' . $this->relative($file) . '</>');
            foreach ($cs as $c) {
                $pxNote = $c['px'] === $c['target_px']
                    ? "{$c['px']}px, exact"
                    : "{$c['px']}px → {$c['target_px']}px";
                $this->line("    <fg=gray>L{$c['line']}</> {$c['from']} → <fg=green>{$c['to']}</>  <fg=gray>({$pxNote})</>");
            }
        }

        if (! empty($skipped)) {
            $this->newLine();
            $this->line('  no close t-shirt (left untouched):');
            foreach ($skipped as $s) {
                $this->line('    <fg=cyan>' . $this->relative($s['file']) . "</> <fg=gray>L{$s['line']}</> <fg=yellow>{$s['token']}</> <fg=gray>({$s['px']}px)</>");
            }
        }

        $this->newLine();
        $summary = count($changes) . ' replacement(s) across ' . count($byFile) . ' file(s).';
        if ($write) {
            $this->info("  ✓ {$summary} " . count($written) . ' file(s) written.');
            $this->line('  <fg=gray>Review the diff — re-opticalize genuine fixed nudges (AGENTS.md -> "Rhythmic vs optical spacing").</>');
        } else {
            $this->line("  {$summary}");
        }
        $this->newLine();

        return Command::SUCCESS;
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
