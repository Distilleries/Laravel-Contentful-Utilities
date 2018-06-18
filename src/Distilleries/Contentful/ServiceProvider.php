<?php

namespace Distilleries\Contentful;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Package Laravel specific internal name.
     *
     * @var string
     */
    protected $package = 'contentful';

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [
            'command.contentful.sync',
            'command.contentful.sync-data',
            'command.contentful.sync-flatten',
            'command.contentful.sync-locales',
            'command.contentful.import-clean',
            'command.contentful.import-publish',
        ];
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/config.php' => base_path('config/' . $this->package . '.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations/');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', $this->package);

        $this->app->bind(Api\DeliveryApi::class, Api\Delivery\Cached::class);
        $this->app->bind(Api\ManagementApi::class, Api\Management\Api::class);
        $this->app->bind(Api\SyncApi::class, Api\Sync\Api::class);
        $this->app->bind(Api\UploadApi::class, Api\Upload\Api::class);

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register Artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->app->singleton('command.contentful.sync', function () {
            return new Commands\Sync\Sync;
        });
        $this->app->singleton('command.contentful.sync-data', function () {
            return new Commands\Sync\SyncData(app(Api\SyncApi::class));
        });
        $this->app->singleton('command.contentful.sync-flatten', function () {
            return new Commands\Sync\SyncFlatten;
        });
        $this->app->singleton('command.contentful.sync-locales', function () {
            return new Commands\Sync\SyncLocales(app(Api\ManagementApi::class));
        });
        $this->app->singleton('command.contentful.import-clean', function () {
            return new Commands\Import\ImportClean(app(Api\ManagementApi::class));
        });
        $this->app->singleton('command.contentful.import-publish', function () {
            return new Commands\Import\ImportPublish(app(Api\ManagementApi::class));
        });

        $this->commands('command.contentful.sync');
        $this->commands('command.contentful.sync-data');
        $this->commands('command.contentful.sync-flatten');
        $this->commands('command.contentful.sync-locales');
        $this->commands('command.contentful.import-clean');
        $this->commands('command.contentful.import-publish');
    }
}
