<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 05/09/2018
 * Time: 11:31
 */

namespace Distilleries\Contentful\Helpers;


use Distilleries\Contentful\Models\Base\ContentfulMapper;
use Distilleries\Contentful\Models\Base\ContentfulModel;

class NamespaceResolver
{

    public static function modelClass(string $model): ?string
    {
        return self::load('contentful.namespace.model', $model);
    }

    public static function model(string $model): ?ContentfulModel
    {
        $class = self::modelClass($model);

        if (!empty($class)) {
            $model = new $class;
            return $model instanceof ContentfulModel?$model:null;
        }

        return null;
    }


    public static function mapperClass(string $mapper): ?string
    {
        return self::load('contentful.namespace.mapper', $mapper);
    }

    public static function mapper(string $mapper): ?ContentfulMapper
    {
        $class = self::mapperClass($mapper);

        if (!empty($class)) {
            return new $class;
        }

        return null;
    }


    public static function load(string $key, string $element): ?string
    {
        foreach (config($key, []) as $namespace) {
            $modelClass = rtrim($namespace,
                    '\\') . '\\' . ltrim(studly_case($element), '\\');

            if (class_exists($modelClass)) {
                return $modelClass;
            }
        }

        return null;
    }
}