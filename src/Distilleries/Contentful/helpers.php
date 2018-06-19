<?php

if (! function_exists('use_contentful_preview')) {
    /**
     * Use Contentful preview switch setup.
     *
     * @param  boolean  $state
     * @return void
     */
    function use_contentful_preview(bool $state = true)
    {
        if (! empty($state)) {
            config([
                'database.default' => 'mysql_preview',
                'contentful.use_preview' => 1,
            ]);
        } else {
            config([
                'database.default' => 'mysql',
                'contentful.use_preview' => 0,
            ]);
        }
    }
}
