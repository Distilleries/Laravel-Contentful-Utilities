<?php

namespace Distilleries\Contentful\Translations;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Translation\Loader as LoaderInterface;

class FileOrDatabaseLoader implements LoaderInterface
{
    /**
     * All of the namespace hints.
     *
     * @var array
     */
    protected $hints = [];

    /**
     * The eloquent model to load.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Filesystem implementations.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * File path.
     *
     * @var string
     */
    protected $path = '';

    /**
     * Create a new database loader instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $path
     * @return void
     */
    public function __construct(Model $model, Filesystem $files, $path)
    {
        $this->model = $model;

        $this->files = $files;

        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function load($locale, $group, $namespace = null)
    {
        if (($group == '*') and ($namespace == '*')) {
            return $this->loadJsonPath($this->path, $locale);
        }

        if (is_null($namespace) or $namespace == '*') {
            return $this->loadPath($this->path, $locale, $group);
        }

        return $this->loadNamespaced($locale, $group, $namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function addNamespace($namespace, $hint)
    {
        $this->hints[$namespace] = $hint;
    }

    /**
     * {@inheritdoc}
     */
    public function namespaces()
    {
        return $this->hints;
    }

    /**
     * {@inheritdoc}
     */
    public function addJsonPath($path)
    {
        // TODO: Implement addJsonPath() method.
    }

    /**
     * Load a namespaced translation group.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function loadNamespaced($locale, $group, $namespace)
    {
        if (isset($this->hints[$namespace])) {
            $lines = $this->loadPath($this->hints[$namespace], $locale, $group);

            return $this->loadNamespaceOverrides($lines, $locale, $group, $namespace);
        }

        return [];
    }

    /**
     * Load a local namespaced translation group for overrides.
     *
     * @param  array  $lines
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function loadNamespaceOverrides(array $lines, $locale, $group, $namespace)
    {
        $file = "{$this->path}/vendor/{$namespace}/{$locale}/{$group}.php";

        if ($this->files->exists($file)) {
            return array_replace_recursive($lines, $this->files->getRequire($file));
        }

        return $lines;
    }

    /**
     * Load a locale from a given path.
     *
     * @param  string  $path
     * @param  string  $locale
     * @param  string  $group
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function loadPath($path, $locale, $group)
    {
        if ($this->files->exists($full = "{$path}/{$locale}/{$group}.php")) {
            return $this->files->getRequire($full);
        } else if ($group == 'db') {
            $model = $this->model
                ->locale($locale, config('app.country'))
                ->get()
                ->last();

            if (! empty($model)) {
                 return !empty($model->json['labels'])?$model->json['labels']:[];
            }
        }

        return [];
    }

    /**
     * Load a locale from the given JSON file path.
     *
     * @param  string  $path
     * @param  string  $locale
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function loadJsonPath($path, $locale)
    {
        if ($this->files->exists($full = "{$path}/{$locale}.json")) {
            return json_decode($this->files->get($full), true);
        }

        return [];
    }
}
