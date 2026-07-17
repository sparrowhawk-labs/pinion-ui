<?php

namespace SparrowhawkLabs\PinionUi\Commands;

use Illuminate\Console\Command;
use SparrowhawkLabs\PinionUi\Linting\EjectTransformer;

/**
 * `php artisan ui:eject` — freeze the current theme × tune × strength
 * rendering into vanilla Tailwind classes (`p-md` -> `p-4` under
 * default/md, `tune-btn-md` -> `h-9 px-3.5 text-[14px]`, `bg-primary` ->
 * `bg-[#131110]`). The reverse of `ui:spacing-migrate`: migrate to adopt
 * pinion-ui, eject to leave — no lock-in.
 *
 * Values come from the browser-measured table in
 * src/resources/eject-table.json (see EjectTransformer for the rules and
 * the deliberate skips — fonts, unknown var() tokens, component tags).
 * Dry-run by default; `--write` applies and snapshots (undo with `--undo`).
 */
class UiEject extends Command
{
    use Concerns\RewritesBladeFiles;

    protected $signature = 'ui:eject
        {path?* : Files or directories to eject (default: resources/views)}
        {--tune=default : Tune preset whose rendering to freeze}
        {--strength=md : Tune strength (xs|sm|md|lg|xl)}
        {--theme=pinion : Theme whose colors to freeze (any shipped theme, e.g. pinion|pinion-dark|mood-monokai|reactive)}
        {--write : Apply the replacements (default is a dry-run preview)}
        {--undo : Restore files from a previous --write run (latest, or --run=<id>)}
        {--run= : Run id for --undo (see --runs)}
        {--runs : List recorded --write runs}
        {--json : Output machine-readable JSON}';

    protected $description = 'Freeze a theme × tune rendering into vanilla Tailwind classes (dry-run by default)';

    public function handle(): int
    {
        if ($this->option('runs')) {
            return $this->listRuns();
        }
        if ($this->option('undo')) {
            return $this->undoRun();
        }

        $table = json_decode((string) file_get_contents(__DIR__ . '/../resources/eject-table.json'), true);
        if (! is_array($table)) {
            $this->error('eject-table.json is missing or unreadable — reinstall the package.');

            return Command::FAILURE;
        }

        $combo = $this->option('tune') . '|' . $this->option('strength');
        if (! isset($table['tokens'][$combo])) {
            $this->error("Unknown tune/strength '{$combo}'. Valid: " . implode(', ', array_keys($table['tokens'])));

            return Command::FAILURE;
        }
        if (! isset($table['colors'][$this->option('theme')])) {
            $this->error("Unknown theme '{$this->option('theme')}'. Valid: " . implode(', ', array_keys($table['colors'])) . '. Other daisyUI themes are not in the table — eject their colors manually.');

            return Command::FAILURE;
        }

        $paths = $this->argument('path') ?: [base_path('resources/views')];
        $files = $this->collectBladeFiles($paths);
        $write = (bool) $this->option('write');

        $transformer = new EjectTransformer($table['tokens'][$combo], $table['colors'][$this->option('theme')]);
        $changes = [];
        $skipped = [];
        $written = [];
        $snapshot = [];

        foreach ($files as $file) {
            $src = @file_get_contents($file);
            if ($src === false) {
                continue;
            }
            $result = $transformer->eject($src);
            foreach ($result['changes'] as $c) {
                $changes[] = ['file' => $file] + $c;
            }
            foreach ($result['skipped'] as $s) {
                $skipped[] = ['file' => $file] + $s;
            }
            if ($write && $result['source'] !== $src) {
                $snapshot[$file] = ['before' => $src, 'after' => $result['source']];
                file_put_contents($file, $result['source']);
                $written[] = $file;
            }
        }

        $runId = $write ? $this->recordSnapshot($snapshot) : null;

        if ($this->option('json')) {
            $this->line(json_encode([
                'files' => count($files),
                'tune' => $this->option('tune'), 'strength' => $this->option('strength'), 'theme' => $this->option('theme'),
                'changes' => $changes,
                'skipped' => $skipped,
                'written' => $write ? array_map(fn ($f) => $this->relative($f), $written) : [],
                'dry_run' => ! $write,
                'run' => $runId,
            ], JSON_UNESCAPED_SLASHES));

            return Command::SUCCESS;
        }

        $this->newLine();
        $mode = $write ? 'applied' : 'dry-run — use --write to apply';
        $this->line("  eject — freeze theme×tune to vanilla Tailwind <fg=gray>({$this->option('theme')} × {$combo} · {$mode})</>");
        $this->line('  ─────────────────────────────────────');

        if (empty($changes) && empty($skipped)) {
            $this->info('  ✓ nothing to eject (' . count($files) . ' files scanned).');
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
                $this->line("    <fg=gray>L{$c['line']}</> {$c['from']} → <fg=green>{$c['to']}</>");
            }
        }

        if (! empty($skipped)) {
            $this->newLine();
            $this->line('  needs manual handling:');
            foreach ($skipped as $s) {
                $this->line('    <fg=cyan>' . $this->relative($s['file']) . "</> <fg=gray>L{$s['line']}</> <fg=yellow>{$s['token']}</> <fg=gray>({$s['reason']})</>");
            }
        }

        $this->newLine();
        $summary = count($changes) . ' replacement(s) across ' . count($byFile) . ' file(s).';
        if ($write) {
            $this->info("  ✓ {$summary} " . count($written) . ' file(s) written.');
            if ($runId !== null) {
                $this->line("  <fg=gray>snapshot: run {$runId} — revert anytime with</> ui:eject --undo");
            }
            $this->line('  <fg=gray>Note: <x-…> component tags and data-theme/data-tune attributes are untouched — remove them once the page no longer depends on pinion-ui.</>');
        } else {
            $this->line("  {$summary}");
        }
        $this->newLine();

        return Command::SUCCESS;
    }
}
