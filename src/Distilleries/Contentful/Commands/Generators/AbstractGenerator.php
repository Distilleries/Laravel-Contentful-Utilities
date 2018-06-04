<?php

namespace Distilleries\Contentful\Commands\Generators;

use Exception;
use Illuminate\Console\Command;
use Distilleries\Contentful\Eloquent;
use Distilleries\Contentful\Api\Management\Api;

abstract class AbstractGenerator extends Command
{
    /**
     * Contentful Management API implementation.
     *
     * @var \Distilleries\Contentful\Api\Management\Api
     */
    protected $api;

    /**
     * Create a new command instance.
     *
     * @param  \Distilleries\Contentful\Api\Management\Api  $api
     */
    public function __construct(Api $api)
    {
        parent::__construct();

        $this->api = $api;
    }

    /**
     * Return content-type corresponding table string ID.
     *
     * @param  string  $id
     * @return string
     */
    protected function tableName($id)
    {
        return Eloquent::TABLE_PREFIX . str_plural($id);
    }

    /**
     * Write stub to destination path with given string replacements.
     *
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
     * @return \Distilleries\Contentful\Commands\Generators\Definitions\DefinitionInterface
     * @throws \Exception
     */
    protected function fieldDefinition($table, $field)
    {
        $className = '\Distilleries\Contentful\Commands\Generators\Definitions\\' . $field['type'] . 'Definition';
        
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
