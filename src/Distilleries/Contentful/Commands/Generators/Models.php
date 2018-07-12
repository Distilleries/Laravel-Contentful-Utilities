<?php

namespace Distilleries\Contentful\Commands\Generators;

class Models extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'contentful:generate:models';

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

        if (!empty($contentTypes['items'])) {
            array_unshift($contentTypes['items'], $this->assetContentType());
            foreach ($contentTypes['items'] as $contentType) {
                $this->info('Content-Type: ' . mb_strtoupper($contentType['name']));
                $file = $this->createMapper($contentType);
                $this->line('Mapper "' . $file . '" created');
                $file = $this->createModel($contentType);
                $this->line('Model "' . $file . '" created');

            }
        }
    }

    /**
     * Create migration file for given content-type.
     *
     * @param  array $contentType
     * @return string
     * @throws \Exception
     */
    protected function createModel(array $contentType): string
    {
        $table = $this->tableName($contentType['sys']['id']);
        $model = studly_case(str_singular($table));

        $stubPath = __DIR__ . '/stubs/model.stub';
        $destPath = rtrim(config('contentful.generator.model'), '/') . '/' . $model . '.php';

        return static::writeStub($stubPath, $destPath, [
            'model' => $model,
            'table' => $table,
            'getters' => $this->modelGetters($table, $contentType['fields']),
        ]);
    }

    protected function createMapper(array $contentType): string
    {
        $table = $this->tableName($contentType['sys']['id']);
        $model = studly_case(str_singular($table));

        $stubPath = __DIR__ . '/stubs/mapper.stub';
        $destPath = rtrim(config('contentful.generator.mapper'), '/') . '/' . $model . 'Mapper.php';

        return static::writeStub($stubPath, $destPath, [
            'model' => $model
        ]);

    }

    protected function modelGetters($table, $fields): string
    {
        $getters = [];
        foreach ($fields as $field) {
            if ($this->isFieldEnabled($field)) {
                $fieldDefinition = $this->fieldDefinition($table, $field);
                $getters[] = $fieldDefinition->modelGetter();
            }
        }

        if (empty($getters)) {
            return "\t\t//";
        }

        return implode("\n\n", array_map(function ($getter) {
            return $getter;
        }, $getters));
    }
}
