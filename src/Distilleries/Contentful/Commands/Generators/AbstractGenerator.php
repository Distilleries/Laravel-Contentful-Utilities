<?php

namespace Distilleries\Contentful\Commands\Generators;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Distilleries\Contentful\Api\ManagementApi as Api;
use Distilleries\Contentful\Commands\Generators\Definitions\DefinitionInterface;

abstract class AbstractGenerator extends Command
{
    /**
     * Contentful Management API implementation.
     *
     * @var \Distilleries\Contentful\Api\ManagementApi
     */
    protected $api;

    /**
     * Create a new command instance.
     *
     * @param  \Distilleries\Contentful\Api\ManagementApi  $api
     * @return void
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
        return DB::getTablePrefix() . Str::plural(Str::snake($id));
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
    public static function writeStub($stubPath, $destPath, $replacements = []): string
    {
        $content = file_get_contents($stubPath);
        foreach ($replacements as $key => $value) {
            $content = str_replace('{{' . Str::upper($key) . '}}', $value, $content);
        }

        file_put_contents($destPath, $content);

        return str_replace(base_path(), '', $destPath);
    }

    /**
     * Asset content-type minimal definition.
     *
     * @return array
     */
    protected function assetContentType(): array
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
     */
    protected function fieldDefinition($table, $field): ?DefinitionInterface
    {
        $className = '\Distilleries\Contentful\Commands\Generators\Definitions\\' . $field['type'] . 'Definition';

        if (class_exists($className)) {
            return new $className($table, $field);
        }

        return null;
    }

    /**
     * Return if field is actually enabled.
     *
     * @param  array  $field
     * @return boolean
     */
    protected function isFieldEnabled($field): bool
    {
        return ! $field['disabled'] && ! $field['omitted'];
    }
}
