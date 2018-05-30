<?php

namespace Distilleries\Contentful\Contentful\Commands;

use Exception;

abstract class GeneratorCommand extends BaseCommand
{
    /**
     * Write stub to destination path with given string replacements.
     * Return relative base path of destination path.
     *
     * @param  string  $stubPath
     * @param  string  $destPath
     * @param  array  $replacements
     * @return string
     */
    public static function writeStub($stubPath, $destPath, $replacements = [])
    {
        $content = file_get_contents($stubPath);
        foreach ($replacements as $key => $value) {
            $content = str_replace('{{' . mb_strtoupper($key) . '}}', $value, $content);
        }

        file_put_contents($destPath, $content);

        return str_replace(base_path(), '', $destPath);
    }

    /**
     * Asset content-type minimal definition.
     *
     * @return array
     */
    protected function assetContentType()
    {
        $assetContentType = [
            'sys' => [
                'id' => 'asset',
            ],
            'name' => 'asset',
            'fields' => [[
                'id' => 'file_name',
                'type' => 'Symbol',
            ], [
                'id' => 'mime_type',
                'type' => 'Symbol',
            ], [
                'id' => 'size',
                'type' => 'Integer',
            ], [
                'id' => 'url',
                'type' => 'Symbol',
            ], [
                'id' => 'title',
                'type' => 'Symbol',
                'required' => false,
            ], [
                'id' => 'description',
                'type' => 'Symbol',
                'required' => false,
            ]],
        ];

        $assetContentType['fields'] = array_map(function ($field) {
            if (! isset($field['required'])) {
                $field['required'] = true;
            }
            $field['disabled'] = false;
            $field['omitted'] = false;

            return $field;
        }, $assetContentType['fields']);

        return $assetContentType;
    }

    /**
     * Return definition interface class instance.
     *
     * @param  string  $table
     * @param  array  $field
     * @return \App\Services\Contentful\Commands\Definitions\DefinitionInterface
     * @throws \Exception
     */
    protected function fieldDefinition($table, $field)
    {
        $className = '\App\Services\Contentful\Commands\Definitions\\' . $field['type'] . 'Definition';
        
        if (! class_exists($className)) {
            throw new Exception('Unknown field type "' . $field['type'] . '"');
        }

        return new $className($table, $field);
    }

    /**
     * Return if field is actually enabled.
     *
     * @param  array  $field
     * @return boolean
     */
    protected function isFieldEnabled($field)
    {
        return ! $field['disabled'] and ! $field['omitted'];
    }
}
