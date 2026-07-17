<?php

namespace SparrowhawkLabs\PinionUi\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use SparrowhawkLabs\PinionUi\PinionUiServiceProvider;

class UiInstall extends Command
{
    protected $signature = 'ui:install
        {--ai : Add AI-agent reference to AGENTS.md (CLAUDE.md imports it via @AGENTS.md)}
        {--claude : Alias of --ai (kept for compatibility)}
        {--skip-npm : Skip adding npm dependencies}
        {--skip-css : Skip CSS file modifications}
        {--skip-alpine : Skip Alpine.js setup in app.js}
        {--skip-layout : Skip patching the consumer layout <html> data-theme}
        {--editor : Wire the opt-in <x-editor> JS module (Tiptap deps + app.js registration)}
        {--data-grid : Wire the opt-in <x-data-grid> JS module (Tabulator dep + base CSS + app.js registration)}
        {--sheet : Wire the opt-in <x-sheet> JS module (Locality-of-Behavior spreadsheet — pure Alpine, NO npm dep)}
        {--calendar : Wire the opt-in <x-calendar> JS module (minimal date picker — pure Alpine, NO npm dep)}
        {--skip-hooks : Skip installing the lint-after-edit Claude Code hook}
        {--git-hook : Install a general (agent-agnostic) git pre-commit ui:lint hook}';

    protected $description = 'Install pinion-ui v2 components with required dependencies';

    public function handle()
    {
        $this->info('');
        $this->info('  pinion-ui v2 Installation');
        $this->info('  ─────────────────────────');
        $this->newLine();

        // Check for component name conflicts
        if (!$this->checkComponentConflicts()) {
            return Command::FAILURE;
        }

        // Setup npm dependencies
        if (!$this->option('skip-npm')) {
            $this->setupNpmDependencies();
        }

        // Setup CSS (Tailwind v4 + daisyUI v5 + tune)
        if (!$this->option('skip-css')) {
            $this->setupCss();
        }

        // Setup Alpine.js in app.js
        if (!$this->option('skip-alpine')) {
            $this->setupAlpineJs();
        }

        // Patch the consumer's root layout <html ...> tag so the pinion theme
        // is explicit. The theme is also `default: true` in pinion-ui.css, so
        // missing the patch only loses discoverability, not functionality.
        if (!$this->option('skip-layout')) {
            $this->setupLayout();
        }

        // OPT-IN editor module. <x-editor> is pinion-ui's only JS-behavior
        // component (Tiptap). Its npm deps + app.js registration are wired ONLY
        // when --editor is passed, so non-editor apps pay zero JS bundle cost.
        if ($this->option('editor')) {
            $this->setupEditor();
        }

        // OPT-IN data-grid module. <x-data-grid> wraps Tabulator (spreadsheet
        // grid). Its npm dep + base CSS + app.js registration are wired ONLY when
        // --data-grid is passed, so non-grid apps pay zero JS/CSS engine cost.
        if ($this->option('data-grid')) {
            $this->setupDataGrid();
        }

        // OPT-IN sheet module. <x-sheet> is the Locality-of-Behavior spreadsheet
        // (hand-written Alpine, NO engine) — only an app.js registration, NO npm dep
        // and NO extra CSS (the .pn-sheet theme is already in the bundled preset).
        if ($this->option('sheet')) {
            $this->setupSheet();
        }

        // OPT-IN calendar module. <x-calendar> is the minimal date picker (pure Alpine,
        // no engine) — app.js registration only, no npm dep, no extra CSS.
        if ($this->option('calendar')) {
            $this->setupCalendar();
        }

        // Add AI-agent reference: core snippet into the host's AGENTS.md,
        // plus a CLAUDE.md `@AGENTS.md` import so Claude Code (which reads
        // CLAUDE.md, not AGENTS.md) picks up the same content without
        // duplicating it.
        $addToClaude = $this->option('ai') || $this->option('claude');
        if (!$addToClaude) {
            $addToClaude = $this->confirm('Add pinion-ui AI-agent reference to AGENTS.md? (Claude Code picks it up via a CLAUDE.md @AGENTS.md import)', true);
        }
        if ($addToClaude) {
            $this->ensureAgentsMdCore();
            $this->ensureClaudeMdImportsAgents();
        }

        // Install the lint-after-edit hook so agents get violations injected back
        // into their context (not just shown to the human). See installLintHook().
        if (!$this->option('skip-hooks')) {
            $installHook = $this->option('ai') || $this->option('claude');
            if (!$installHook) {
                $installHook = $this->confirm('Install the lint-after-edit Claude Code hook? (runs ui:lint on edited Blade and feeds violations back to the agent)', true);
            }
            if ($installHook) {
                $this->installLintHook();
            }
        }

        // General, agent-agnostic automation: a git pre-commit hook (opt-in).
        if ($this->option('git-hook')) {
            $this->installGitHook();
        }

        $this->newLine();
        $this->info('  ✓ Installation complete!');
        $this->newLine();
        $this->line('  Next steps:');
        $this->line('    1. Run: npm install');
        $this->line('    2. Run: npm run build');
        $this->line('    3. Use: <x-button color="primary">Click</x-button>');
        $this->newLine();
        $this->line('  Theme: pinion (warm cream + amber accent) is shipped as the default.');
        $this->line('  For dark mode or other looks, set <html data-theme="dark"> (or dim/night/…).');
        $this->newLine();
        $this->line('  Documentation:');
        $this->line('    - README:     vendor/sparrowhawk-labs/pinion-ui/README.md');
        $this->line('    - Components: vendor/sparrowhawk-labs/pinion-ui/reference/components/index.md');

        return Command::SUCCESS;
    }

    protected function setupNpmDependencies(): void
    {
        $packageJsonPath = base_path('package.json');

        if (!File::exists($packageJsonPath)) {
            $this->warn('  package.json not found. Skipping npm dependencies.');
            return;
        }

        $packageJson = json_decode(File::get($packageJsonPath), true);
        $modified = false;

        $dependencies = [
            '@alpinejs/collapse' => '^3.14.0',
            '@alpinejs/focus' => '^3.14.0',
            'alpinejs' => '^3.14.0',
            'daisyui' => '^5.0.0',
        ];

        foreach ($dependencies as $package => $version) {
            if (!isset($packageJson['dependencies'][$package])) {
                $packageJson['dependencies'][$package] = $version;
                $modified = true;
                $this->line("    + Added {$package}@{$version}");
            } else {
                $this->line("    - {$package} already present");
            }
        }

        if ($modified) {
            ksort($packageJson['dependencies']);
            File::put($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
            $this->info('  ✓ Updated package.json');
        }
    }

    protected function setupCss(): void
    {
        $appCssPath = resource_path('css/app.css');

        if (!File::exists($appCssPath)) {
            $this->warn('  resources/css/app.css not found. Skipping CSS setup.');
            return;
        }

        $content = File::get($appCssPath);
        $modified = false;

        // The pinion-ui preset (imported below) loads daisyUI itself, with an
        // exclude list that limits the host to color/theme tokens plus the
        // component CSS pinion-ui's own output references. A standalone full
        // `@plugin "daisyui"` in the host app.css (written by ui:install
        // ≤ v0.4.1, a Laravel starter, or by hand — any quote style) would
        // regenerate every daisyUI component class (`.btn`, `.card`, …) and
        // defeat that boundary, so remove it. `@plugin "daisyui/theme"`
        // blocks are left alone — consumer theme definitions are legitimate.
        $standalonePlugin = '/^[ \t]*@plugin\s+["\']daisyui["\']\s*(?:\{[^}]*\}|;)[ \t]*\n?/m';
        if (preg_match($standalonePlugin, $content)) {
            $content = preg_replace($standalonePlugin, '', $content);
            $modified = true;
            $this->line('    - Removed standalone @plugin "daisyui" (the pinion-ui preset loads daisyUI itself: colors/themes + only the component CSS pinion-ui uses)');
        }

        // Import the pinion-ui preset. The preset wires:
        //   • the daisyUI plugin itself (themes: false + component exclude
        //     list — see pinion-ui.css for the rationale; the shipped
        //     lineup in theme.css is the only theme source)
        //   • @source for Blade views AND Compose PHP (both must be scanned
        //     — Composer files emit class strings that won't appear in any
        //     blade source)
        //   • safelist for the color × appearance × state permutations the
        //     Composers build via interpolation (Tailwind JIT can't see
        //     through "bg-{$color}/10")
        //   • @import "./tune.css" for the data-tune token system
        //   • the theme lineup (36 originals × light/dark, `pinion` default)
        // Skipping the preset = ~half the design system silently missing
        // from the bundle, which is what the v0.3.17 bug report uncovered.
        $presetImport = '@import "../../vendor/sparrowhawk-labs/pinion-ui/src/resources/css/pinion-ui.css";';
        if (!str_contains($content, 'sparrowhawk-labs/pinion-ui/src/resources/css/pinion-ui.css')) {
            // Drop any v0.3.16-and-earlier piecemeal lines so we don't end
            // up with both the old @source and the new preset import.
            $content = preg_replace(
                '/^@source\s+"[^"]*sparrowhawk-labs\/pinion-ui[^"]*";\s*\n?/m',
                '',
                $content
            );
            $content = preg_replace(
                '/^@import\s+"\.\/pinion-tune\.css";\s*\n?/m',
                '',
                $content
            );
            $content .= "\n" . $presetImport . "\n";
            $modified = true;
            $this->line('    + Added @import for pinion-ui preset');
        }

        if ($modified) {
            File::put($appCssPath, $content);
            $this->info('  ✓ Updated resources/css/app.css');
        }

        // Clean up the stale pinion-tune.css copy from earlier install
        // versions — the preset imports tune.css internally now.
        $staleTune = resource_path('css/pinion-tune.css');
        if (File::exists($staleTune)) {
            File::delete($staleTune);
            $this->line('    - Removed legacy resources/css/pinion-tune.css (preset bundles tune.css)');
        }
    }

    protected function setupAlpineJs(): void
    {
        $appJsPath = resource_path('js/app.js');

        if (!File::exists($appJsPath)) {
            $this->warn('  resources/js/app.js not found. Skipping Alpine.js setup.');
            return;
        }

        $content    = File::get($appJsPath);
        $hasAlpine  = str_contains($content, 'alpinejs') || str_contains($content, 'Alpine');
        $hasFocus   = str_contains($content, '@alpinejs/focus');
        $hasCollapse = str_contains($content, '@alpinejs/collapse');

        if ($hasAlpine && $hasFocus && $hasCollapse) {
            $this->line('    - Alpine.js + focus + collapse plugins already configured');
            return;
        }

        if ($hasAlpine) {
            // Alpine already wired — surgically insert any missing plugin
            // lines. Handles upgrades from earlier ui:install versions:
            //   v0.3.16 and earlier: no plugins
            //   v0.3.17: focus wired, collapse missing
            //   v0.3.19+: both wired
            // pinion-ui blade components use:
            //   <x-sidebar>  → @alpinejs/focus (x-trap.inert.noscroll)
            //   <x-accordion> → @alpinejs/collapse (x-collapse for height anim)
            $added = [];

            if (!$hasFocus) {
                $content = preg_replace(
                    '/(import Alpine from \'alpinejs\';)/',
                    "$1\nimport focus from '@alpinejs/focus';",
                    $content,
                    1
                );
                $content = preg_replace(
                    '/(window\.Alpine = Alpine;)/',
                    "Alpine.plugin(focus);\n$1",
                    $content,
                    1
                );
                $added[] = '@alpinejs/focus';
            }

            if (!$hasCollapse) {
                $content = preg_replace(
                    '/(import Alpine from \'alpinejs\';)/',
                    "$1\nimport collapse from '@alpinejs/collapse';",
                    $content,
                    1
                );
                $content = preg_replace(
                    '/(window\.Alpine = Alpine;)/',
                    "Alpine.plugin(collapse);\n$1",
                    $content,
                    1
                );
                $added[] = '@alpinejs/collapse';
            }

            File::put($appJsPath, $content);
            $this->info('  ✓ Wired ' . implode(' + ', $added) . ' into resources/js/app.js');
            return;
        }

        $alpineSetup = <<<'JS'
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';

Alpine.plugin(collapse);
Alpine.plugin(focus);
window.Alpine = Alpine;
Alpine.start();

JS;

        $content = $alpineSetup . $content;
        File::put($appJsPath, $content);
        $this->info('  ✓ Added Alpine.js + plugins to resources/js/app.js');
    }

    /**
     * Wire the opt-in <x-editor> JS-behavior module (Tiptap).
     *
     * Two steps, mirroring setupAlpineJs():
     *   1. Add the Tiptap npm deps to package.json (task-list/task-item/link are
     *      NOT in StarterKit, hence listed explicitly).
     *   2. Import the pinionEditor factory from the vendored module and register
     *      it via Alpine.data('pinionEditor', …), inserted right before
     *      Alpine.start() so it's available when components mount.
     *
     * Opt-in (only on `ui:install --editor`) so non-editor apps never pull the
     * Tiptap bundle. Idempotent.
     */
    protected function setupEditor(): void
    {
        $this->newLine();
        $this->info('  Editor (<x-editor>) — opt-in JS module');

        // ── 1. npm deps ───────────────────────────────────────────────────
        $packageJsonPath = base_path('package.json');
        if (!File::exists($packageJsonPath)) {
            $this->warn('    package.json not found. Skipping editor npm deps.');
        } else {
            $packageJson = json_decode(File::get($packageJsonPath), true);
            $modified = false;

            // task-list / task-item are NOT bundled in StarterKit. StarterKit v3
            // bundles its own Link, but editor.js disables it (link: false) and
            // adds the standalone Link with custom config — so it's a real dep.
            $editorDeps = [
                '@tiptap/core'                  => '^3.0.0',
                '@tiptap/starter-kit'           => '^3.0.0',
                '@tiptap/extension-placeholder' => '^3.0.0',
                '@tiptap/extension-task-list'   => '^3.0.0',
                '@tiptap/extension-task-item'   => '^3.0.0',
                '@tiptap/extension-link'        => '^3.0.0',
            ];

            foreach ($editorDeps as $package => $version) {
                if (!isset($packageJson['dependencies'][$package])) {
                    $packageJson['dependencies'][$package] = $version;
                    $modified = true;
                    $this->line("    + Added {$package}@{$version}");
                } else {
                    $this->line("    - {$package} already present");
                }
            }

            if ($modified) {
                ksort($packageJson['dependencies']);
                File::put($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
                $this->info('    ✓ Updated package.json with Tiptap deps');
            }
        }

        // ── 2. app.js registration ────────────────────────────────────────
        $appJsPath = resource_path('js/app.js');
        if (!File::exists($appJsPath)) {
            $this->warn('    resources/js/app.js not found. Skipping editor registration.');
            return;
        }

        $content = File::get($appJsPath);

        if (str_contains($content, "Alpine.data('pinionEditor'") || str_contains($content, 'Alpine.data("pinionEditor"')) {
            $this->line('    - pinionEditor already registered in resources/js/app.js');
            return;
        }

        // Require a REAL Alpine reference (not a bare-substring 'Alpine' in a comment).
        if (!preg_match('/(from\s+[\'"]alpinejs[\'"]|from\s+[\'"][^\'"]*livewire\.esm[\'"]|\bAlpine\.(data|plugin|start)\b)/', $content)) {
            $this->warn('    Alpine.js not set up in app.js — run without --skip-alpine first.');
            return;
        }

        $importLine = "import { pinionEditor } from '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/editor.js';";
        $registerLine = "Alpine.data('pinionEditor', pinionEditor);";

        // Insert the import after whichever line imports Alpine (vanilla
        // `import Alpine from 'alpinejs'` OR `import { …, Alpine } from '…livewire.esm'`).
        if (preg_match('/^import .*Alpine.* from .*;$/m', $content)) {
            $content = preg_replace('/^(import .*Alpine.* from .*;)$/m', "$1\n{$importLine}", $content, 1);
        } else {
            $content = $importLine . "\n" . $content;
        }

        // Register BEFORE the boot call (Livewire-ESM → Livewire.start(); vanilla →
        // Alpine.start()), matching the call with an OPTIONAL semicolon so apps
        // formatted with semi:false don't fall through and register after boot.
        if (preg_match('/^[ \t]*Livewire\.start\(\)\s*;?/m', $content)) {
            $content = preg_replace('/(^[ \t]*Livewire\.start\(\)\s*;?)/m', "{$registerLine}\n$1", $content, 1);
        } elseif (preg_match('/^[ \t]*Alpine\.start\(\)\s*;?/m', $content)) {
            $content = preg_replace('/(^[ \t]*Alpine\.start\(\)\s*;?)/m', "{$registerLine}\n$1", $content, 1);
        } elseif (preg_match('/window\.Alpine\s*=\s*Alpine\s*;?/', $content)) {
            $content = preg_replace('/(window\.Alpine\s*=\s*Alpine\s*;?)/', "$1\n{$registerLine}", $content, 1);
        } else {
            $content .= "\n{$registerLine}\n";
        }

        File::put($appJsPath, $content);
        $this->info('    ✓ Registered pinionEditor in resources/js/app.js');
        $this->line('      Run `npm install && npm run build`, then use <x-editor wire:model="body" />');
    }

    /**
     * Wire the opt-in <x-data-grid> JS-behavior module (Tabulator).
     *
     * Mirrors setupEditor(), with two differences:
     *   1. One npm dep (tabulator-tables) instead of six.
     *   2. The grid needs Tabulator's base structural CSS, so we inject a CSS
     *      import alongside the JS import.
     *
     * Robust to both app.js shapes: a vanilla `import Alpine from 'alpinejs'` +
     * `Alpine.start()`, AND the Livewire-ESM single-Alpine bundle
     * (`import { Livewire, Alpine } from '…/livewire.esm'` + `Livewire.start()`),
     * where the registration MUST precede Livewire.start(). Idempotent.
     */
    protected function setupDataGrid(): void
    {
        $this->newLine();
        $this->info('  Data grid (<x-data-grid>) — opt-in JS module');

        // ── 1. npm dep ────────────────────────────────────────────────────
        $packageJsonPath = base_path('package.json');
        if (!File::exists($packageJsonPath)) {
            $this->warn('    package.json not found. Skipping data-grid npm dep.');
        } else {
            $packageJson = json_decode(File::get($packageJsonPath), true);
            $package = 'tabulator-tables';
            $version = '^6.3.0';

            if (!isset($packageJson['dependencies'][$package])) {
                $packageJson['dependencies'][$package] = $version;
                ksort($packageJson['dependencies']);
                File::put($packageJsonPath, json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
                $this->line("    + Added {$package}@{$version}");
                $this->info('    ✓ Updated package.json with Tabulator dep');
            } else {
                $this->line("    - {$package} already present");
            }
        }

        // ── 2. app.js registration (import CSS + factory, register on Alpine) ─
        $appJsPath = resource_path('js/app.js');
        if (!File::exists($appJsPath)) {
            $this->warn('    resources/js/app.js not found. Skipping data-grid registration.');
            return;
        }

        $content = File::get($appJsPath);

        if (str_contains($content, "Alpine.data('pinionDataGrid'") || str_contains($content, 'Alpine.data("pinionDataGrid"')) {
            $this->line('    - pinionDataGrid already registered in resources/js/app.js');
            return;
        }

        // Require a REAL Alpine reference (import or Alpine.{data,plugin,start}) — a
        // bare substring 'Alpine' also matches a comment, which would let us register
        // onto an undefined Alpine.
        if (!preg_match('/(from\s+[\'"]alpinejs[\'"]|from\s+[\'"][^\'"]*livewire\.esm[\'"]|\bAlpine\.(data|plugin|start)\b)/', $content)) {
            $this->warn('    Alpine.js not set up in app.js — run without --skip-alpine first.');
            return;
        }

        $cssImport = "import 'tabulator-tables/dist/css/tabulator.min.css';";
        $jsImport = "import { pinionDataGrid } from '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/data-grid.js';";
        $registerLine = "Alpine.data('pinionDataGrid', pinionDataGrid);";
        $imports = "{$cssImport}\n{$jsImport}";

        // Insert the imports right after whichever line imports Alpine (vanilla
        // `import Alpine from 'alpinejs'` OR `import { …, Alpine } from '…livewire.esm'`).
        if (preg_match('/^import .*Alpine.* from .*;$/m', $content)) {
            $content = preg_replace('/^(import .*Alpine.* from .*;)$/m', "$1\n{$imports}", $content, 1);
        } else {
            $content = $imports . "\n" . $content;
        }

        // Register BEFORE the boot call so the component is defined before mount.
        // Livewire-ESM boots Alpine via Livewire.start(); vanilla via Alpine.start().
        // Match the call with an OPTIONAL semicolon (apps formatted with semi:false
        // write `Livewire.start()` / `Alpine.start()` — a `;`-anchored check there
        // falls through and appends the registration AFTER boot = registered too late).
        if (preg_match('/^[ \t]*Livewire\.start\(\)\s*;?/m', $content)) {
            $content = preg_replace('/(^[ \t]*Livewire\.start\(\)\s*;?)/m', "{$registerLine}\n$1", $content, 1);
        } elseif (preg_match('/^[ \t]*Alpine\.start\(\)\s*;?/m', $content)) {
            $content = preg_replace('/(^[ \t]*Alpine\.start\(\)\s*;?)/m', "{$registerLine}\n$1", $content, 1);
        } elseif (preg_match('/window\.Alpine\s*=\s*Alpine\s*;?/', $content)) {
            $content = preg_replace('/(window\.Alpine\s*=\s*Alpine\s*;?)/', "$1\n{$registerLine}", $content, 1);
        } else {
            $content .= "\n{$registerLine}\n";
        }

        File::put($appJsPath, $content);
        $this->info('    ✓ Registered pinionDataGrid in resources/js/app.js');
        $this->line('      Run `npm install && npm run build`, then use <x-data-grid :columns="…" wire:model="rows" />');
    }

    /**
     * Wire the opt-in <x-sheet> JS-behavior module (Locality-of-Behavior spreadsheet).
     *
     * Simpler than setupDataGrid(): NO npm dependency (pure Alpine, no engine) and NO
     * extra CSS import (the `.pn-sheet` theme ships in the bundled preset). Just the
     * factory registration in app.js, placed BEFORE the boot call. Robust to both the
     * vanilla-Alpine and Livewire-ESM single-Alpine app.js shapes; semicolon-independent;
     * idempotent.
     */
    protected function setupSheet(): void
    {
        $this->newLine();
        $this->info('  Sheet (<x-sheet>) — opt-in JS module (pure Alpine, no npm dep)');

        $appJsPath = resource_path('js/app.js');
        if (!File::exists($appJsPath)) {
            $this->warn('    resources/js/app.js not found. Skipping sheet registration.');
            return;
        }

        $content = File::get($appJsPath);

        if (str_contains($content, "Alpine.data('pinionSheet'") || str_contains($content, 'Alpine.data("pinionSheet"')) {
            $this->line('    - pinionSheet already registered in resources/js/app.js');
            return;
        }

        // Require a REAL Alpine reference (not a bare substring that could be a comment).
        if (!preg_match('/(from\s+[\'"]alpinejs[\'"]|from\s+[\'"][^\'"]*livewire\.esm[\'"]|\bAlpine\.(data|plugin|start)\b)/', $content)) {
            $this->warn('    Alpine.js not set up in app.js — run without --skip-alpine first.');
            return;
        }

        $jsImport = "import { pinionSheet } from '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/sheet.js';";
        $registerLine = "Alpine.data('pinionSheet', pinionSheet);";

        // Insert the import right after whichever line imports Alpine.
        if (preg_match('/^import .*Alpine.* from .*;$/m', $content)) {
            $content = preg_replace('/^(import .*Alpine.* from .*;)$/m', "$1\n{$jsImport}", $content, 1);
        } else {
            $content = $jsImport . "\n" . $content;
        }

        // Register BEFORE the boot call (Livewire.start() / Alpine.start()), semicolon-optional.
        if (preg_match('/^[ \t]*Livewire\.start\(\)\s*;?/m', $content)) {
            $content = preg_replace('/(^[ \t]*Livewire\.start\(\)\s*;?)/m', "{$registerLine}\n$1", $content, 1);
        } elseif (preg_match('/^[ \t]*Alpine\.start\(\)\s*;?/m', $content)) {
            $content = preg_replace('/(^[ \t]*Alpine\.start\(\)\s*;?)/m', "{$registerLine}\n$1", $content, 1);
        } elseif (preg_match('/window\.Alpine\s*=\s*Alpine\s*;?/', $content)) {
            $content = preg_replace('/(window\.Alpine\s*=\s*Alpine\s*;?)/', "$1\n{$registerLine}", $content, 1);
        } else {
            $content .= "\n{$registerLine}\n";
        }

        File::put($appJsPath, $content);
        $this->info('    ✓ Registered pinionSheet in resources/js/app.js');
        $this->line('      Run `npm run build`, then use <x-sheet :columns="…" :rows="…" wire:model="rows" />');
    }

    /**
     * Wire the opt-in <x-calendar> JS module (minimal date picker). Same shape as
     * setupSheet(): NO npm dep, NO extra CSS — just the factory registration in app.js.
     */
    protected function setupCalendar(): void
    {
        $this->newLine();
        $this->info('  Calendar (<x-calendar>) — opt-in JS module (pure Alpine, no npm dep)');

        $appJsPath = resource_path('js/app.js');
        if (!File::exists($appJsPath)) {
            $this->warn('    resources/js/app.js not found. Skipping calendar registration.');
            return;
        }

        $content = File::get($appJsPath);

        if (str_contains($content, "Alpine.data('pinionCalendar'") || str_contains($content, 'Alpine.data("pinionCalendar"')) {
            $this->line('    - pinionCalendar already registered in resources/js/app.js');
            return;
        }

        if (!preg_match('/(from\s+[\'"]alpinejs[\'"]|from\s+[\'"][^\'"]*livewire\.esm[\'"]|\bAlpine\.(data|plugin|start)\b)/', $content)) {
            $this->warn('    Alpine.js not set up in app.js — run without --skip-alpine first.');
            return;
        }

        $jsImport = "import { pinionCalendar } from '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/calendar.js';";
        $registerLine = "Alpine.data('pinionCalendar', pinionCalendar);";

        if (preg_match('/^import .*Alpine.* from .*;$/m', $content)) {
            $content = preg_replace('/^(import .*Alpine.* from .*;)$/m', "$1\n{$jsImport}", $content, 1);
        } else {
            $content = $jsImport . "\n" . $content;
        }

        if (preg_match('/^[ \t]*Livewire\.start\(\)\s*;?/m', $content)) {
            $content = preg_replace('/(^[ \t]*Livewire\.start\(\)\s*;?)/m', "{$registerLine}\n$1", $content, 1);
        } elseif (preg_match('/^[ \t]*Alpine\.start\(\)\s*;?/m', $content)) {
            $content = preg_replace('/(^[ \t]*Alpine\.start\(\)\s*;?)/m', "{$registerLine}\n$1", $content, 1);
        } elseif (preg_match('/window\.Alpine\s*=\s*Alpine\s*;?/', $content)) {
            $content = preg_replace('/(window\.Alpine\s*=\s*Alpine\s*;?)/', "$1\n{$registerLine}", $content, 1);
        } else {
            $content .= "\n{$registerLine}\n";
        }

        File::put($appJsPath, $content);
        $this->info('    ✓ Registered pinionCalendar in resources/js/app.js');
        $this->line('      Run `npm run build`, then use <x-calendar wire:model="date" />');
    }

    protected function setupLayout(): void
    {
        // Search standard layout locations in order. Most Laravel apps put
        // the root <html> in one of these; the first hit wins. If the
        // consumer's app uses a non-standard layout, they get a warning
        // and a manual instruction.
        $candidates = [
            resource_path('views/components/layouts/app.blade.php'),
            resource_path('views/layouts/app.blade.php'),
            resource_path('views/app.blade.php'),
        ];

        $layoutPath = null;
        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                $layoutPath = $candidate;
                break;
            }
        }

        if ($layoutPath === null) {
            $this->warn('  No standard layout found (looked for components/layouts/app, layouts/app, app).');
            $this->line('    Manually add data-theme="pinion" data-tune="default" to your <html> tag.');
            return;
        }

        $relative = $this->relativeLayoutPath($layoutPath);
        $content = File::get($layoutPath);

        // Blade-aware <html ...> match. The naive `[^>]*` breaks on layouts
        // like `<html lang="{{ ... app()->getLocale() ... }}">` because the
        // `>` inside `app()->` ends the match prematurely. So we explicitly
        // skip over `{{ ... }}` (and `{!! ... !!}`) blocks before consuming
        // any other non-`>` char. The `s` modifier lets the html tag span
        // multiple lines, which a few Laravel skeletons do.
        $htmlPattern = '/<html\b((?:\{\{(?:(?!\}\}).)*?\}\}|\{!!(?:(?!!!\}).)*?!!\}|[^>])*?)>/si';
        if (!preg_match($htmlPattern, $content, $matches)) {
            $this->warn("  No <html> tag found in {$relative}. Skipping.");
            return;
        }

        $attrs = $matches[1];

        // Three cases:
        //   1. data-theme="pinion"  → idempotent no-op
        //      (the name survives v0.6.0 — only the palette behind it changed)
        //   2. data-theme="something-else" → migrate with confirmation
        //      (daisyUI's built-in themes no longer exist in the build, so
        //       `light` — the past ui:install recommendation — defaults to
        //       migrate; any other value is a deliberate choice and
        //       defaults to keep)
        //   3. no data-theme              → append data-theme="pinion"
        //      (+ data-tune="default" if missing too)
        if (preg_match('/data-theme\s*=\s*["\']pinion["\']/', $attrs)) {
            $this->line("    - {$relative}: <html data-theme=\"pinion\"> already present");
            return;
        }

        if (preg_match('/data-theme\s*=\s*["\']([^"\']+)["\']/', $attrs, $themeMatch)) {
            $currentTheme = $themeMatch[1];
            // Default to migrating only from the past ui:install
            // recommendation "light" (pre-v0.4.0; a daisyUI stock name that
            // no longer exists in the build — keeping it would render
            // unthemed). Any other value — the host's own custom theme — is
            // a deliberate choice, so the default is "keep". confirm()
            // returns the default when the command runs non-interactively,
            // which is what used to silently stomp custom themes on every
            // re-run (v0.4.2 regression, found via NADI).
            $migrate = $this->confirm(
                "  {$relative} has data-theme=\"{$currentTheme}\". Switch to \"pinion\" (v0.6.0 brand default)?",
                $currentTheme === 'light'
            );
            if (!$migrate) {
                $this->line("    - {$relative}: kept data-theme=\"{$currentTheme}\"");
                return;
            }
            $newAttrs = preg_replace(
                '/data-theme\s*=\s*["\'][^"\']*["\']/',
                'data-theme="pinion"',
                $attrs,
                1
            );
            $newTag = '<html' . $newAttrs . '>';
            $content = preg_replace($htmlPattern, $newTag, $content, 1);
            File::put($layoutPath, $content);
            $this->info("  ✓ {$relative}: data-theme=\"{$currentTheme}\" → \"pinion\"");
            return;
        }

        // No data-theme — append. Also append data-tune="default" if it's
        // missing, since the tune system is part of the bundle and a blank
        // <html> with no data-tune renders the implicit `default` tune anyway.
        $additions = ' data-theme="pinion"';
        if (!preg_match('/data-tune\s*=/', $attrs)) {
            $additions .= ' data-tune="default"';
        }
        $newTag = '<html' . rtrim($attrs) . $additions . '>';
        $content = preg_replace($htmlPattern, $newTag, $content, 1);
        File::put($layoutPath, $content);
        $this->info("  ✓ {$relative}: added data-theme=\"pinion\"");
    }

    protected function relativeLayoutPath(string $absolute): string
    {
        return ltrim(str_replace(base_path(), '', $absolute), '/');
    }

    /**
     * Append the pinion-ui core snippet (calling conventions, class
     * vocabulary rule, pointer to the package's own AGENTS.md for the full
     * reference) into the host app's AGENTS.md — the cross-tool convention
     * file most coding agents other than Claude Code read natively.
     * Idempotent via the CLAUDE_SNIPPET.md marker heading.
     */
    protected function ensureAgentsMdCore(): void
    {
        $agentsMdPath = base_path('AGENTS.md');
        $snippetPath = dirname(__DIR__, 2) . '/CLAUDE_SNIPPET.md';

        if (!File::exists($snippetPath)) {
            $this->warn('  CLAUDE_SNIPPET.md not found in package.');
            return;
        }

        $snippet = File::get($snippetPath);
        $marker = '## pinion-ui (AI agents)';

        if (File::exists($agentsMdPath)) {
            $existing = File::get($agentsMdPath);
            if (str_contains($existing, $marker)) {
                $this->line('    - AGENTS.md already contains pinion-ui reference');
                return;
            }
            File::append($agentsMdPath, "\n" . $snippet);
        } else {
            File::put($agentsMdPath, $snippet);
        }

        $this->info('  ✓ Added pinion-ui reference to AGENTS.md');
    }

    /**
     * Claude Code reads CLAUDE.md, not AGENTS.md, unless CLAUDE.md imports it
     * (`@AGENTS.md`) — see https://code.claude.com/docs/en/memory (AGENTS.md
     * section). Ensure that import so Claude Code sessions pick up the same
     * AGENTS.md core section without duplicating its content into CLAUDE.md.
     * Creates a minimal CLAUDE.md if the host app has none. Idempotent.
     */
    protected function ensureClaudeMdImportsAgents(): void
    {
        $claudeMdPath = base_path('CLAUDE.md');
        $existing = File::exists($claudeMdPath) ? File::get($claudeMdPath) : '';

        if (str_contains($existing, '@AGENTS.md')) {
            $this->line('    - CLAUDE.md already imports AGENTS.md');
            return;
        }

        $content = $existing === ''
            ? "@AGENTS.md\n"
            : "@AGENTS.md\n\n" . ltrim($existing);

        File::put($claudeMdPath, $content);
        $this->info('  ✓ Added @AGENTS.md import to CLAUDE.md');
    }

    /**
     * Install a general (agent-agnostic) git pre-commit hook that runs `ui:lint`
     * on staged Blade files and blocks the commit on violations. Unlike the
     * Claude Code PostToolUse hook, this is git-level — it works for any workflow
     * (human, CI, or any CLI agent). The universal interface is `ui:lint`; this
     * just wires it to commit. Never clobbers an existing pre-commit hook.
     */
    protected function installGitHook(): void
    {
        $stub = dirname(__DIR__, 2) . '/stubs/hooks/pre-commit-lint';
        $gitDir = base_path('.git');
        if (! is_dir($gitDir)) {
            $this->warn('  No .git directory — skipping git pre-commit hook.');
            return;
        }
        if (! File::exists($stub)) {
            $this->warn('  pre-commit hook stub not found in package; skipping.');
            return;
        }

        $hookPath = $gitDir . '/hooks/pre-commit';
        $marker = 'pinion-ui pre-commit lint';

        if (File::exists($hookPath)) {
            if (str_contains(File::get($hookPath), $marker)) {
                $this->line('    - git pre-commit ui:lint hook already installed');
                return;
            }
            // Don't clobber a hook the user/another tool owns — show the one-liner.
            $this->warn('  A .git/hooks/pre-commit already exists — add this line manually:');
            $this->line('      php artisan ui:lint $(git diff --cached --name-only --diff-filter=ACM -- "*.blade.php")');
            return;
        }

        File::ensureDirectoryExists($gitDir . '/hooks');
        File::copy($stub, $hookPath);
        @chmod($hookPath, 0o755);
        $this->info('  ✓ Installed git pre-commit ui:lint hook (.git/hooks/pre-commit)');
    }

    /**
     * Install the PostToolUse "lint-after-edit" hook: copy the hook script into
     * .claude/hooks/ and register it in .claude/settings.json (idempotent).
     *
     * The hook runs `ui:lint` on each edited Blade file and, on violations, feeds
     * them back into the agent's context via `additionalContext` (the hook exits 0
     * and prints JSON — a non-zero exit would be dropped from the model's context).
     */
    protected function installLintHook(): void
    {
        $stub = dirname(__DIR__, 2) . '/stubs/hooks/lint-blade.php';
        if (!File::exists($stub)) {
            $this->warn('  lint hook stub not found in package; skipping hook install.');
            return;
        }

        // 1. (Re)copy the hook script — keeps it current with the installed package.
        //    But if .claude/hooks (or the script itself) is a symlink, a scaffold owns
        //    it (e.g. blueprint-flow symlinks .claude/hooks to a shared stack dir).
        //    Writing through the symlink would clobber that shared, multi-app file —
        //    so leave it as-is; the scaffold already provides the script.
        $hookDir = base_path('.claude/hooks');
        $hookFile = $hookDir . '/lint-blade.php';
        if (is_link($hookDir) || is_link($hookFile)) {
            $this->line('    - .claude/hooks is scaffold-managed (symlink); leaving hook script as-is');
        } else {
            File::ensureDirectoryExists($hookDir);
            File::copy($stub, $hookFile);
        }

        // 2. Register the PostToolUse hook in .claude/settings.json (idempotent).
        $settingsPath = base_path('.claude/settings.json');
        $settings = [];
        if (File::exists($settingsPath)) {
            $settings = json_decode(File::get($settingsPath), true) ?: [];
        }

        // Shell-guarded so a missing script is a silent no-op. This matters when
        // .claude/settings.json is a symlink to a shared scaffold (e.g. blueprint-flow):
        // the registration is shared across apps, but each app's hook script is copied
        // per-install — apps without the script must not error on every edit.
        $command = 'test -f "$CLAUDE_PROJECT_DIR/.claude/hooks/lint-blade.php" '
            . '&& php "$CLAUDE_PROJECT_DIR/.claude/hooks/lint-blade.php" || true';

        $already = false;
        foreach ($settings['hooks']['PostToolUse'] ?? [] as $entry) {
            foreach ($entry['hooks'] ?? [] as $h) {
                if (str_contains($h['command'] ?? '', 'lint-blade.php')) {
                    $already = true;
                }
            }
        }

        if ($already) {
            $this->line('    - lint-after-edit hook already registered (script refreshed)');
            return;
        }

        $settings['hooks']['PostToolUse'][] = [
            'matcher' => 'Edit|Write',
            'hooks' => [[
                'type' => 'command',
                'command' => $command,
            ]],
        ];

        File::put(
            $settingsPath,
            json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
        );

        $this->info('  ✓ Installed lint-after-edit hook (.claude/hooks/lint-blade.php + settings.json)');
    }

    protected function checkComponentConflicts(): bool
    {
        $projectComponentsPath = resource_path('views/components');

        if (!File::isDirectory($projectComponentsPath)) {
            $this->info('  ✓ No component name conflicts detected.');
            $this->newLine();
            return true;
        }

        $packageComponents = PinionUiServiceProvider::getComponentNames();
        $conflicts = [];

        foreach ($packageComponents as $component) {
            $componentPath = str_replace('.', '/', $component);
            $filePath = $projectComponentsPath . '/' . $componentPath . '.blade.php';

            if (File::exists($filePath)) {
                $conflicts[] = $component;
            }
        }

        if (empty($conflicts)) {
            $this->info('  ✓ No component name conflicts detected.');
            $this->newLine();
            return true;
        }

        $this->error('  Component name conflicts detected!');
        $this->newLine();
        $this->warn('  The following components conflict with pinion-ui:');
        foreach ($conflicts as $conflict) {
            $this->line("    - resources/views/components/{$conflict}.blade.php");
        }
        $this->newLine();
        $this->info('  To resolve, use namespaced syntax: <x-pn::component> instead of <x-component>');
        $this->newLine();

        return $this->confirm('Continue installation anyway?', false);
    }
}
