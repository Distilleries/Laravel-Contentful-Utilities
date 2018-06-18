<?php

namespace Distilleries\Contentful;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class ContentfulServiceProvider extends ServiceProvider
{
    /**
     * Package Laravel specific internal name.
     *
     * @var string
     */
    protected $package = 'contentful';

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path($this->package . '.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../views' => base_path('resources/views/vendor/' . $this->package),
        ], 'views');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\Sync\Locales::class,
                Commands\Generators\Models::class,
                Commands\Generators\Migrations::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../../views', $this->package);
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', $this->package);
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations/');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', $this->package);

        $this->app->singleton(Api\Delivery\Api::class, function ($app) {
            // @TODO... Use cached API version
            return new Api\Delivery\Live;
            //return new Api\Delivery\Cache(app(\Illuminate\Contracts\Cache\Factory::class));
        });

        // @TODO... Required?
        // $this->alias();
    }

    /**
     * Programmatically declare Laravel aliases
     *
     * @return void
     */
    private function alias()
    {

        AliasLoader::getInstance()->alias('Agent', 'Jenssegers\Agent\Facades\Agent');
        AliasLoader::getInstance()->alias('Log', 'Illuminate\Support\Facades\Log');
        AliasLoader::getInstance()->alias('DB', 'Illuminate\Support\Facades\DB');
    }
}