<?php

namespace SparrowhawkLabs\PinionUi\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use SparrowhawkLabs\PinionUi\PinionUiServiceProvider;

class UiInstall extends Command
{
    protected $signature = 'ui:install
        {--ai : Add AI-agent reference to CLAUDE.md (points at AGENTS.md)}
        {--claude : Alias of --ai (kept for compatibility)}
        {--skip-npm : Skip adding npm dependencies}
        {--skip-css : Skip CSS file modifications}
        {--skip-alpine : Skip Alpine.js setup in app.js}
        {--tune-only= : Only include specific tune presets (comma-separated)}';

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

        // Add AI-agent reference to CLAUDE.md
        $addToClaude = $this->option('ai') || $this->option('claude');
        if (!$addToClaude) {
            $addToClaude = $this->confirm('Add pinion-ui AI-agent reference to CLAUDE.md? (so Claude Code / other agents pick up AGENTS.md)', true);
        }
        if ($addToClaude) {
            $this->addToClaudeMd();
        }

        $this->newLine();
        $this->info('  ✓ Installation complete!');
        $this->newLine();
        $this->line('  Next steps:');
        $this->line('    1. Run: npm install');
        $this->line('    2. Run: npm run build');
        $this->line('    3. Set <html data-theme="light" data-tune="default"> in your layout');
        $this->line('    4. Use: <x-button color="primary">Click</x-button>');
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

        // Add daisyUI plugin (Tailwind v4 syntax). Detect regardless of quote
        // style — Laravel's default app.css uses single quotes (`@plugin 'daisyui'`)
        // and earlier versions of this command emitted double quotes, so a
        // strict double-quote check missed the existing block and produced a
        // duplicate on re-install.
        if (!preg_match('/@plugin\s+["\']daisyui["\']/', $content)) {
            $pluginLine = '@plugin "daisyui" {' . "\n" . '  themes: all;' . "\n" . '}';

            // Insert after @import "tailwindcss" (any quote style) if present
            if (preg_match('/@import\s+["\']tailwindcss["\']/', $content)) {
                $content = preg_replace(
                    '/(@import\s+["\']tailwindcss["\'][^;]*;?)/',
                    "$1\n" . $pluginLine,
                    $content
                );
            } else {
                $content = $pluginLine . "\n" . $content;
            }
            $modified = true;
            $this->line('    + Added @plugin "daisyui"');
        }

        // Import the pinion-ui preset. The preset wires:
        //   • @source for Blade views AND Compose PHP (both must be scanned
        //     — Composer files emit class strings that won't appear in any
        //     blade source)
        //   • safelist for the color × appearance × state permutations the
        //     Composers build via interpolation (Tailwind JIT can't see
        //     through "bg-{$color}/10")
        //   • @import "./tune.css" for the data-tune token system
        //   • custom CSS rules (tooltip-light, base-N tooltip variants,
        //     dark theme *-content patches)
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

    protected function addToClaudeMd(): void
    {
        $claudeMdPath = base_path('CLAUDE.md');
        $snippetPath = dirname(__DIR__, 2) . '/CLAUDE_SNIPPET.md';

        if (!File::exists($snippetPath)) {
            $this->warn('  CLAUDE_SNIPPET.md not found in package.');
            return;
        }

        $snippet = File::get($snippetPath);
        $marker = '## pinion-ui (AI agents)';

        if (File::exists($claudeMdPath)) {
            $existing = File::get($claudeMdPath);
            if (str_contains($existing, $marker)) {
                $this->line('    - CLAUDE.md already contains pinion-ui reference');
                return;
            }
            File::append($claudeMdPath, "\n" . $snippet);
        } else {
            File::put($claudeMdPath, "# Project Guidelines\n\n" . $snippet);
        }

        $this->info('  ✓ Added pinion-ui reference to CLAUDE.md');
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
