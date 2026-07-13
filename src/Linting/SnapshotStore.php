<?php

namespace SparrowhawkLabs\PinionUi\Linting;

/**
 * SnapshotStore — automatic before-state snapshots for the class-rewriting
 * commands (`ui:spacing-migrate --write`, `ui:eject --write`), so every bulk
 * rewrite can be undone without relying on git.
 *
 * Layout (one store shared by both commands, default
 * `storage/pinion-ui/rewrites/`):
 *
 *   <dir>/<runId>/manifest.json   — command, timestamp, per-file sha256 before/after
 *   <dir>/<runId>/before/<n>      — byte-exact pre-rewrite copies
 *
 * Restore is hash-guarded: a file is only rolled back when its CURRENT
 * content still matches the post-rewrite hash recorded in the manifest.
 * A file edited since the rewrite is reported and left alone — silently
 * clobbering later hand edits would be worse than no undo at all.
 *
 * Deliberately PURE (no Laravel) — the store root is injected.
 */
final class SnapshotStore
{
    public function __construct(private string $dir)
    {
    }

    /**
     * Record one rewrite run. $files = [path => ['before' => string, 'after' => string]].
     * Returns the runId.
     */
    public function record(string $command, array $files): string
    {
        $runId = date('Ymd-His');
        for ($i = 2; is_dir("{$this->dir}/{$runId}"); $i++) {
            $runId = date('Ymd-His') . "-{$i}";
        }
        $runDir = "{$this->dir}/{$runId}";
        mkdir("{$runDir}/before", 0755, true);

        $manifest = ['command' => $command, 'time' => date('c'), 'files' => []];
        $n = 0;
        foreach ($files as $path => $contents) {
            $beforeName = 'before/' . $n++ . '-' . basename($path);
            file_put_contents("{$runDir}/{$beforeName}", $contents['before']);
            $manifest['files'][] = [
                'path' => $path,
                'before' => $beforeName,
                'sha_before' => hash('sha256', $contents['before']),
                'sha_after' => hash('sha256', $contents['after']),
            ];
        }
        file_put_contents("{$runDir}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $runId;
    }

    /** List runs, newest first: [['run' => id, 'command' => ..., 'time' => ..., 'files' => n], ...] */
    public function runs(): array
    {
        if (! is_dir($this->dir)) {
            return [];
        }
        $out = [];
        foreach (scandir($this->dir, SCANDIR_SORT_DESCENDING) ?: [] as $entry) {
            $manifestPath = "{$this->dir}/{$entry}/manifest.json";
            if ($entry === '.' || $entry === '..' || ! is_file($manifestPath)) {
                continue;
            }
            $m = json_decode((string) file_get_contents($manifestPath), true);
            if (is_array($m)) {
                $out[] = ['run' => $entry, 'command' => $m['command'] ?? '?', 'time' => $m['time'] ?? '?', 'files' => count($m['files'] ?? [])];
            }
        }

        return $out;
    }

    /**
     * Restore a run (latest when $runId is null).
     * Returns ['run' => id|null, 'results' => [['path','status' => restored|modified|missing], ...]].
     * 'modified' = file changed since the rewrite — left untouched.
     */
    public function restore(?string $runId = null): array
    {
        if ($runId === null) {
            $runs = $this->runs();
            $runId = $runs[0]['run'] ?? null;
        }
        if ($runId === null || ! is_file("{$this->dir}/{$runId}/manifest.json")) {
            return ['run' => $runId, 'results' => []];
        }

        $m = json_decode((string) file_get_contents("{$this->dir}/{$runId}/manifest.json"), true) ?: [];
        $results = [];
        foreach ($m['files'] ?? [] as $f) {
            $current = @file_get_contents($f['path']);
            if ($current === false) {
                $results[] = ['path' => $f['path'], 'status' => 'missing'];
                continue;
            }
            if (hash('sha256', $current) !== $f['sha_after']) {
                // already reverted?
                $status = hash('sha256', $current) === $f['sha_before'] ? 'restored' : 'modified';
                $results[] = ['path' => $f['path'], 'status' => $status];
                continue;
            }
            file_put_contents($f['path'], file_get_contents("{$this->dir}/{$runId}/{$f['before']}"));
            $results[] = ['path' => $f['path'], 'status' => 'restored'];
        }

        return ['run' => $runId, 'results' => $results];
    }
}
