<?php

namespace SparrowhawkLabs\PinionUi;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use SparrowhawkLabs\PinionUi\Commands\UiInstall;

class PinionUiServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Register anonymous components without namespace prefix
        // Usage: <x-button>, <x-input>, <x-card>, etc.
        Blade::anonymousComponentPath(__DIR__ . '/resources/views/components');

        // Keep namespaced version as fallback: <x-pinion-ui::button>
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'pinion-ui');

        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                UiInstall::class,
            ]);
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
