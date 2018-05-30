<?php

namespace Distilleries\Contentful;

use Distilleries\Contentful\Models\LabelTransverses;
use Distilleries\Contentful\Translations\FileOrDatabaseLoader;

class TranslationServiceProvider extends \Illuminate\Translation\TranslationServiceProvider
{
    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new FileOrDatabaseLoader(new LabelTransverses(), $app['files'], $app['path.lang']);
        });
    }
}
