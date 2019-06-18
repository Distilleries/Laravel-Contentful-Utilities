<?php

namespace Distilleries\Contentful\Helpers;

use Illuminate\Support\Str;
use Distilleries\Contentful\Models\Base\ContentfulModel;
use Distilleries\Contentful\Models\Base\ContentfulMapper;

class NamespaceResolver
{
    /**
     * Resolve model classname.
     *
     * @param  string  $model
     * @return string|null
     */
    public static function modelClass(string $model): ?string
    {
        return static::load('contentful.namespace.model', $model);
    }

    /**
     * Resolve model.
     *
     * @param  string  $model
     * @return \Distilleries\Contentful\Models\Base\ContentfulModel|null
     */
    public static function model(string $model): ?ContentfulModel
    {
        $class = static::modelClass($model);

        if (! empty($class)) {
            $model = new $class;
            return $model instanceof ContentfulModel ? $model : null;
        }

        return null;
    }

    /**
     * Resolve mapper classname.
     *
     * @param  string  $mapper
     * @return string|null
     */
    public static function mapperClass(string $mapper): ?string
    {
        return static::load('contentful.namespace.mapper', $mapper);
    }

    /**
     * Resolve mapper.
     *
     * @param  string  $mapper
     * @return \Distilleries\Contentful\Models\Base\ContentfulMapper|null
     */
    public static function mapper(string $mapper): ?ContentfulMapper
    {
        $class = static::mapperClass($mapper);

        if (! empty($class)) {
            return new $class;
        }

        return null;
    }

    /**
     * Load given namespace + class.
     *
     * @param  string  $key
     * @param  string  $element
     * @return string|null
     */
    public static function load(string $key, string $element): ?string
    {
        foreach (config($key, []) as $namespace) {
            $modelClass = rtrim($namespace, '\\') . '\\' . ltrim(Str::studly($element), '\\');
            if (class_exists($modelClass)) {
                return $modelClass;
            }
        }

        return null;
    }
}
