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
        {--skip-layout : Skip patching the consumer layout <html> data-theme}
        {--editor : Wire the opt-in <x-editor> JS module (Tiptap deps + app.js registration)}
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
        //   • the daisyUI plugin itself (themes: all + component exclude
        //     list — see pinion-ui.css for the rationale)
        //   • @source for Blade views AND Compose PHP (both must be scanned
        //     — Composer files emit class strings that won't appear in any
        //     blade source)
        //   • safelist for the color × appearance × state permutations the
        //     Composers build via interpolation (Tailwind JIT can't see
        //     through "bg-{$color}/10")
        //   • @import "./tune.css" for the data-tune token system
        //   • the `pinion` default theme definition
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

        if (!str_contains($content, 'alpinejs') && !str_contains($content, 'Alpine')) {
            $this->warn('    Alpine.js not set up in app.js — run without --skip-alpine first.');
            return;
        }

        $importLine = "import { pinionEditor } from '../../vendor/sparrowhawk-labs/pinion-ui/src/resources/js/editor.js';";
        $registerLine = "Alpine.data('pinionEditor', pinionEditor);";

        // Insert the import after the Alpine import, and the registration right
        // before Alpine.start() (so the component is defined before any mount).
        $content = preg_replace(
            '/(import Alpine from \'alpinejs\';)/',
            "$1\n{$importLine}",
            $content,
            1
        );

        if (str_contains($content, 'Alpine.start();')) {
            $content = preg_replace(
                '/(Alpine\.start\(\);)/',
                "{$registerLine}\n$1",
                $content,
                1
            );
        } else {
            // No explicit start() (e.g. Livewire boots Alpine): register after
            // the window.Alpine assignment, else just append.
            if (str_contains($content, 'window.Alpine = Alpine;')) {
                $content = preg_replace(
                    '/(window\.Alpine = Alpine;)/',
                    "$1\n{$registerLine}",
                    $content,
                    1
                );
            } else {
                $content .= "\n{$registerLine}\n";
            }
        }

        File::put($appJsPath, $content);
        $this->info('    ✓ Registered pinionEditor in resources/js/app.js');
        $this->line('      Run `npm install && npm run build`, then use <x-editor wire:model="body" />');
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
        //   1. data-theme="pinion"        → idempotent no-op
        //   2. data-theme="something-else" → migrate with confirmation
        //      (v0.4.0 ships pinion as the brand default; the previous
        //       ui:install recommendation was data-theme="light", so most
        //       existing consumers will hit this path)
        //   3. no data-theme              → append data-theme="pinion"
        //      (+ data-tune="default" if missing too)
        if (preg_match('/data-theme\s*=\s*["\']pinion["\']/', $attrs)) {
            $this->line("    - {$relative}: <html data-theme=\"pinion\"> already present");
            return;
        }

        if (preg_match('/data-theme\s*=\s*["\']([^"\']+)["\']/', $attrs, $themeMatch)) {
            $currentTheme = $themeMatch[1];
            // Default to migrating only from "light" (the pre-v0.4.0 ui:install
            // recommendation). Any other value — a daisyUI stock theme or the
            // host's own custom theme — is a deliberate choice, so the default
            // is "keep". confirm() returns the default when the command runs
            // non-interactively, which is what used to silently stomp custom
            // themes on every re-run (v0.4.2 regression, found via NADI).
            $migrate = $this->confirm(
                "  {$relative} has data-theme=\"{$currentTheme}\". Switch to \"pinion\" (v0.4.0 brand default)?",
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
