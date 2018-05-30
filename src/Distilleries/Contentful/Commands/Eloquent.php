<?php

namespace Distilleries\Contentful\Contentful\Commands;

class Eloquent extends GeneratorCommand
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'contentful:eloquent';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate Eloquent models from Contentful content-types';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function handle()
    {
        $contentTypes = $this->api->contentTypes();

        if (! empty($contentTypes['items'])) {
            array_unshift($contentTypes['items'], $this->assetContentType());
            foreach ($contentTypes['items'] as $contentType) {
                $this->info('Content-Type: ' . mb_strtoupper($contentType['name']));
                $file = $this->createModel($contentType);
                $this->line('Model "' . $file . '" created');
            }
        }
    }

    /**
     * Create migration file for given content-type.
     *
     * @param  array  $contentType
     * @return string
     * @throws \Exception
     */
    private function createModel($contentType)
    {
        $table = $this->tableName($contentType['sys']['id']);
        $model = studly_case(str_singular($table));

        $stubPath = __DIR__ . '/stubs/model.stub';
        $destPath = app_path('models/' . $model . '.php');

        return static::writeStub($stubPath, $destPath, [
            'properties' => $this->modelProperties($table, $contentType['fields']),
            'model' => $model,
            'table' => $table,
            'fillables' => $this->modelFillables($table, $contentType['fields']),
            'casts' => $this->modelCasts($table, $contentType['fields']),
            'relationships' => $this->modelRelationships($table, $contentType['fields']),
        ]);
    }

    /**
     * Generate model properties for given Contentful fields.
     *
     * @param  string  $table
     * @param  array  $fields
     * @return string
     * @throws \Exception
     */
    private function modelProperties($table, $fields)
    {
        $properties = [
            "integer \$id",
            "string \$contentful_id",
        ];

        foreach ($fields as $field) {
            if ($this->isFieldEnabled($field)) {
                $fieldDefinition = $this->fieldDefinition($table, $field);
                foreach ($fieldDefinition->modelProperties() as $fieldProperty) {
                    $properties[] = $fieldProperty;
                }
            }
        }

        $properties[] = "\\Illuminate\\Support\\Carbon \$created_at";
        $properties[] = "\\Illuminate\\Support\\Carbon \$updated_at";

        return implode("\n", array_map(function ($property) {
            return " * @property " . $property;
        }, $properties));
    }

    /**
     * Generate model casts for given Contentful fields.
     *
     * @param  string  $table
     * @param  array  $fields
     * @return string
     * @throws \Exception
     */
    private function modelFillables($table, $fields)
    {
        $fillables = [
            "contentful_id",
        ];

        foreach ($fields as $field) {
            if ($this->isFieldEnabled($field)) {
                $fieldDefinition = $this->fieldDefinition($table, $field);
                foreach ($fieldDefinition->modelFillable() as $fillable) {
                    $fillables[] = $fillable;
                }
            }
        }

        if (empty($fillables)) {
            return "\t\t//";
        }

        return implode("\n", array_map(function ($fillable) {
            return "\t\t'" . $fillable . "',";
        }, $fillables));
    }

    /**
     * Generate model casts for given Contentful fields.
     *
     * @param  string  $table
     * @param  array  $fields
     * @return string
     * @throws \Exception
     */
    private function modelCasts($table, $fields)
    {
        $casts = [];

        foreach ($fields as $field) {
            if ($this->isFieldEnabled($field)) {
                $fieldDefinition = $this->fieldDefinition($table, $field);
                foreach ($fieldDefinition->modelCast() as $cast) {
                    $casts[] = $cast;
                }
            }
        }

        if (empty($casts)) {
            return "\t\t//";
        }

        return implode("\n", array_map(function ($cast) {
            return "\t\t'" . $cast['key'] . "' => '" . $cast['type'] . "',";
        }, $casts));
    }

    /**
     * Generate model relationships for given Contentful fields.
     *
     * @param  string  $table
     * @param  array  $fields
     * @return string
     * @throws \Exception
     */
    private function modelRelationships($table, $fields)
    {
        $relationships = [];

        foreach ($fields as $field) {
            if ($this->isFieldEnabled($field)) {
                $fieldDefinition = $this->fieldDefinition($table, $field);
                $relationship = $fieldDefinition->modelRelationship();

                if (! empty($relationship)) {
                    $relationships[] = $relationship;
                }
            }
        }

        return implode("", array_map(function ($relationship) {
            return "\n\n" . $relationship;
        }, $relationships));
    }
}
