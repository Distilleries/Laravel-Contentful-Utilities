<?php namespace Distilleries\Messenger;

class ContentfulServiceProvider extends ServiceProvider {


    protected $package = 'contentful';
    public function boot()
    {


        $this->publishes([
            __DIR__.'/../../config/config.php'    => config_path($this->package.'.php')
        ],'config');

        $this->publishes([
            __DIR__ . '/../../views' => base_path('resources/views/vendor/'.$this->package),
        ], 'views');


        $this->loadViewsFrom(__DIR__.'/../../views', $this->package);
        $this->loadTranslationsFrom(__DIR__.'/../../lang', $this->package);
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations/');



    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php',
            $this->package
        );
        $this->alias();
    }



    public function alias() {
        AliasLoader::getInstance()->alias(
            'Log',
            'Illuminate\Support\Facades\Log'
        );
    }
}