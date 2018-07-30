<?php

namespace Distilleries\Contentful\Commands\Generators;

use Illuminate\Support\Carbon;

class Migrations extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'contentful:generate:migrations';

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
    private function createMigration($contentType): string
    {
        $table = $this->tableName($contentType['sys']['id']);

        $stubPath = __DIR__ . '/stubs/migration.stub';
        $destPath = database_path('migrations/' . Carbon::now()->format('Y_m_d_His') . '_create_' . $table . '_table.php');

        return static::writeStub($stubPath, $destPath, [
            'class' => studly_case($table),
            'table' => $table
        ]);
    }
}
