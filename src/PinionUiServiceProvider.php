<?php

namespace SparrowhawkLabs\PinionUi;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use SparrowhawkLabs\PinionUi\Commands\UiInstall;
use SparrowhawkLabs\PinionUi\Commands\UiLint;

class PinionUiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/pinion-ui.php', 'pinion-ui');
    }

    public function boot()
    {
        // Register anonymous components without namespace prefix
        // Usage: <x-button>, <x-input>, <x-card>, etc.
        Blade::anonymousComponentPath(__DIR__ . '/resources/views/components');

        // Keep namespaced version as fallback: <x-pn::button>
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'pn');

        // Commands + config publishing
        if ($this->app->runningInConsole()) {
            $this->commands([
                UiInstall::class,
                UiLint::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/pinion-ui.php' => config_path('pinion-ui.php'),
            ], 'pinion-ui-config');
        }
    }

    /**
     * Get list of component names provided by this package.
     */
    public static function getComponentNames(): array
    {
        $componentsPath = __DIR__ . '/resources/views/components';
        $components = [];

        foreach (glob($componentsPath . '/*.blade.php') as $file) {
            $components[] = basename($file, '.blade.php');
        }

        // Include nested components (e.g., section/hero, pagination/full)
        foreach (glob($componentsPath . '/*', GLOB_ONLYDIR) as $dir) {
            $dirName = basename($dir);
            foreach (glob($dir . '/*.blade.php') as $file) {
                $components[] = $dirName . '.' . basename($file, '.blade.php');
            }
        }

        return $components;
    }
}
