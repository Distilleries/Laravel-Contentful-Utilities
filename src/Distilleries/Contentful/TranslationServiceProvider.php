<?php

namespace Distilleries\Contentful;

use Distilleries\Contentful\Models\Label;
use Distilleries\Contentful\Translations\FileOrDatabaseLoader;
use Illuminate\Translation\TranslationServiceProvider as BaseServiceProvider;

class TranslationServiceProvider extends BaseServiceProvider
{
    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new FileOrDatabaseLoader(new Label, $app['files'], $app['path.lang']);
        });
    }
}
