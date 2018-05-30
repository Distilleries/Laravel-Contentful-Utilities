<?php

namespace Distilleries\Contentful\Contentful\Commands;

use Exception;
use Distilleries\Contentful\Eloquent;

class Sync extends BaseCommand
{
    /**
     * Maximum query limit parameter.
     *
     * @var integer
     */
    const LIMIT = 200;

    /**
     * {@inheritdoc}
     */
    protected $signature = 'contentful:sync';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Synchronize Contentful data to Eloquent DB';

    /**
     * An ['id' => 'table name'] associative array.
     *
     * @var array
     */
    protected $indexedTables;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->indexedTables = $this->indexTables();
        $this->truncateDb();

        $this->line('Synchronize assets...');
        $data = $this->assets();
        if ($data['sys']['type'] === 'Array') {
            $this->syncAssets($data);
        }

        $this->line(PHP_EOL . 'Synchronize entries...');
        $data = $this->entries();
        if ($data['sys']['type'] === 'Array') {
            $this->syncEntries($data);
        }
    }

    /**
     * Return indexed table names.
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function indexTables()
    {
        $tables = [];

        $data = $this->api->contentTypes();
        foreach ($data['items'] as $contentType) {
            $tables[$contentType['sys']['id']] = $this->tableName($contentType['sys']['id']);
        }

        $tables['assets'] = $this->tableName('assets');

        return $tables;
    }

    /**
     * Synchronize assets.
     *
     * @param  array  $data
     * @return void
     */
    private function syncAssets($data)
    {
        $bar = $this->output->createProgressBar($data['total']);
        $skip = 0;

        while ($skip < $data['total']) {
            foreach ($data['items'] as $asset) {
                if (isset($asset['sys']['publishedAt'])) {
                    $this->createAsset($asset);
                }
                
                $skip++;
                $bar->advance();
            }

            if ($skip < $data['total']) {
                $data = $this->assets($skip);
            }
        }

        $bar->finish();
    }

    /**
     * Synchronize entries.
     *
     * @param  array  $data
     * @return void
     */
    private function syncEntries($data)
    {
        $bar = $this->output->createProgressBar($data['total']);
        $skip = 0;

        while ($skip < $data['total']) {
            foreach ($data['items'] as $entry) {
                if (isset($entry['sys']['publishedAt'])) {
                    $this->createEntry($entry);
                }
                
                $skip++;
                $bar->advance();
            }

            if ($skip < $data['total']) {
                $data = $this->entries($skip);
            }
        }

        $bar->finish();
    }

    /**
     * Return assets with X skipped ones.
     *
     * @param  integer  $skip
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function assets($skip = 0)
    {
        return $this->api->assets([
            'skip' => $skip,
            'limit' => static::LIMIT,
            'locale' => app()->getLocale(),
        ]);
    }

    /**
     * Return entries with X skipped ones.
     *
     * @param  integer  $skip
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function entries($skip = 0)
    {
        return $this->api->entries([
            'skip' => $skip,
            'limit' => static::LIMIT,
            'locale' => app()->getLocale(),
        ]);
    }

    /**
     * Truncate Contentful related tables.
     *
     * @return void
     */
    private function truncateDb()
    {
        foreach ($this->indexedTables as $table) {
            Eloquent::table($table)->truncate();
        }
    }

    /**
     * Create an asset.
     *
     * @param  array  $asset
     * @return void
     * @throws \Exception
     */
    private function createAsset($asset)
    {
        $table = 'assets';

        $mapper = $this->modelMapper($table);
        $map = $mapper->map($asset);

        Eloquent::table($table)->insert($map['fields']);
    }

    /**
     * Create an entry.
     *
     * @param  array  $entry
     * @return void
     * @throws \Exception
     */
    private function createEntry($entry)
    {
        $table = $this->indexedTables[$entry['sys']['contentType']['sys']['id']];

        $mapper = $this->modelMapper($table);
        $map = $mapper->map($entry);

        Eloquent::table($table)->insert($map['fields']);

        if (isset($map['relations']) and is_array($map['relations'])) {
            Eloquent::handleRelations($table, $map['fields']['contentful_id'], $map['relations']);
        }
    }

    /**
     * Return model mapper for given table.
     *
     * @param  string  $table
     * @return \App\Models\Contentful\Mappers\MapperInterface
     * @throws \Exception
     */
    private function modelMapper($table)
    {
        $className = '\App\Models\Contentful\Mappers\\' . studly_case(str_singular($table) . 'Mapper');

        if (! class_exists($className)) {
            throw new Exception('Non mapped model "' . $className . '"');
        }

        return new $className;
    }
}
