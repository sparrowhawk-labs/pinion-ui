<?php

namespace SparrowhawkLabs\PinionUi\Commands\Concerns;

use SparrowhawkLabs\PinionUi\Linting\SnapshotStore;

/**
 * Shared plumbing for the bulk class-rewriting commands
 * (`ui:spacing-migrate`, `ui:eject`): Blade file collection, and the
 * snapshot/undo surface backed by SnapshotStore. Every `--write` records
 * a snapshot automatically; `--runs` lists them; `--undo [--run=<id>]`
 * rolls back (hash-guarded — files hand-edited since the rewrite are
 * reported and left alone).
 */
trait RewritesBladeFiles
{
    private function snapshotStore(): SnapshotStore
    {
        return new SnapshotStore(storage_path('pinion-ui/rewrites'));
    }

    /** `--runs`: list recorded rewrite runs. */
    private function listRuns(): int
    {
        $runs = $this->snapshotStore()->runs();

        if ($this->option('json')) {
            $this->line(json_encode(['runs' => $runs], JSON_UNESCAPED_SLASHES));

            return 0;
        }

        if (empty($runs)) {
            $this->info('No recorded rewrite runs.');

            return 0;
        }

        $this->newLine();
        foreach ($runs as $r) {
            $this->line("  <fg=cyan>{$r['run']}</>  {$r['command']}  <fg=gray>{$r['time']}  {$r['files']} file(s)</>");
        }
        $this->newLine();
        $this->line('  Undo with: <fg=green>--undo</> (latest) or <fg=green>--undo --run=<id></>');
        $this->newLine();

        return 0;
    }

    /** `--undo [--run=<id>]`: restore a recorded run. */
    private function undoRun(): int
    {
        $result = $this->snapshotStore()->restore($this->option('run') ?: null);

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_UNESCAPED_SLASHES));

            return empty($result['results']) ? 1 : 0;
        }

        if (empty($result['results'])) {
            $this->warn('No matching rewrite run to undo. See --runs.');

            return 1;
        }

        $this->newLine();
        $this->line("  undo run <fg=cyan>{$result['run']}</>");
        foreach ($result['results'] as $r) {
            $tag = match ($r['status']) {
                'restored' => '<fg=green>restored</>',
                'modified' => '<fg=yellow>modified since rewrite — left untouched</>',
                default => '<fg=red>missing</>',
            };
            $this->line('    ' . $this->relative($r['path']) . "  {$tag}");
        }
        $this->newLine();

        return 0;
    }

    /** Record a snapshot for the files a --write changed. */
    private function recordSnapshot(array $snapshotFiles): ?string
    {
        if (empty($snapshotFiles)) {
            return null;
        }

        return $this->snapshotStore()->record($this->getName(), $snapshotFiles);
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
