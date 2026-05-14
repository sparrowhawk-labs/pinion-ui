<?php

namespace SparrowhawkLabs\PinionUi\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use SparrowhawkLabs\PinionUi\PinionUiServiceProvider;

class UiInstall extends Command
{
    protected $signature = 'ui:install
        {--claude : Add reference to CLAUDE.md}
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

        // Add to CLAUDE.md
        $addToClaude = $this->option('claude');
        if (!$addToClaude) {
            $addToClaude = $this->confirm('Add component reference to CLAUDE.md?', true);
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
        $this->line('    4. Use: <x-pn::button variant="primary">Click</x-pn::button>');
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

        // Add daisyUI plugin (Tailwind v4 syntax)
        if (!str_contains($content, '@plugin "daisyui"')) {
            $pluginLine = '@plugin "daisyui" {' . "\n" . '  themes: all;' . "\n" . '}';

            // Insert after @import "tailwindcss" if present
            if (str_contains($content, '@import "tailwindcss"')) {
                $content = preg_replace(
                    '/(@import\s+"tailwindcss"[^;]*;?)/',
                    "$1\n" . $pluginLine,
                    $content
                );
            } else {
                $content = $pluginLine . "\n" . $content;
            }
            $modified = true;
            $this->line('    + Added @plugin "daisyui"');
        }

        // Add @source for vendor component views
        $sourceLine = '@source "../../vendor/sparrowhawk-labs/pinion-ui/src/resources/views/**/*.blade.php";';
        if (!str_contains($content, 'sparrowhawk-labs/pinion-ui')) {
            $content .= "\n" . $sourceLine . "\n";
            $modified = true;
            $this->line('    + Added @source for pinion-ui components');
        }

        if ($modified) {
            File::put($appCssPath, $content);
            $this->info('  ✓ Updated resources/css/app.css');
        }

        // Generate tune CSS file
        $this->generateTuneCss();
    }

    protected function generateTuneCss(): void
    {
        $tuneCssPath = resource_path('css/pinion-tune.css');

        if (File::exists($tuneCssPath)) {
            $this->line('    - pinion-tune.css already exists');
            return;
        }

        $sourceTuneCss = dirname(__DIR__) . '/resources/css/tune.css';
        if (!File::exists($sourceTuneCss)) {
            $this->warn('  tune.css source not found in package.');
            return;
        }

        File::copy($sourceTuneCss, $tuneCssPath);

        // Add import to app.css
        $appCssPath = resource_path('css/app.css');
        if (File::exists($appCssPath)) {
            $content = File::get($appCssPath);
            if (!str_contains($content, 'pinion-tune.css')) {
                $content .= "\n@import \"./pinion-tune.css\";\n";
                File::put($appCssPath, $content);
            }
        }

        $this->info('  ✓ Created resources/css/pinion-tune.css');
    }

    protected function setupAlpineJs(): void
    {
        $appJsPath = resource_path('js/app.js');

        if (!File::exists($appJsPath)) {
            $this->warn('  resources/js/app.js not found. Skipping Alpine.js setup.');
            return;
        }

        $content = File::get($appJsPath);

        if (str_contains($content, 'alpinejs') || str_contains($content, 'Alpine')) {
            $this->line('    - Alpine.js already configured');
            return;
        }

        $alpineSetup = <<<'JS'
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

JS;

        $content = $alpineSetup . $content;
        File::put($appJsPath, $content);
        $this->info('  ✓ Added Alpine.js to resources/js/app.js');
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
        $marker = '## pinion-ui Components';

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
