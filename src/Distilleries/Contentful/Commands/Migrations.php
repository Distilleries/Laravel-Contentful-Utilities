<?php

namespace Distilleries\Contentful\Contentful\Commands;

use Illuminate\Support\Carbon;

class Migrations extends GeneratorCommand
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'contentful:migrations';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate migrations from Contentful content-types';

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
                $file = $this->createMigration($contentType);
                $this->line('Migration "' . $file . '" created');
                sleep(1);
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
    private function createMigration($contentType)
    {
        $table = $this->tableName($contentType['sys']['id']);

        $stubPath = __DIR__ . '/stubs/migration.stub';
        $destPath = database_path('migrations/' . Carbon::now()->format('Y_m_d_His') . '_create_' . $table . '_table.php');

        return static::writeStub($stubPath, $destPath, [
            'class' => studly_case($table),
            'table' => $table,
            'fields' => $this->migrationFields($table, $contentType['fields']),
        ]);
    }

    /**
     * Generate migration fields definitions for given Contentful fields.
     *
     * @param  string  $table
     * @param  array  $fields
     * @return string
     * @throws \Exception
     */
    private function migrationFields($table, $fields)
    {
        $migrations = [
            "increments('id')",
            "string('contentful_id')->index()",
        ];

        foreach ($fields as $field) {
            if ($this->isFieldEnabled($field)) {
                $fieldDefinition = $this->fieldDefinition($table, $field);
                foreach ($fieldDefinition->migration() as $migration) {
                    $migrations[] = $migration;
                }
            }
        }

        $migrations[] = "timestamps()";

        return implode("\n", array_map(function ($migration) {
            return "\t\t\t\$table->" . $migration . ";";
        }, $migrations));
    }
}
