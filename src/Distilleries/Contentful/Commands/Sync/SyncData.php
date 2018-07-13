<?php

namespace Distilleries\Contentful\Commands\Sync;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Distilleries\Contentful\Api\SyncApi;
use GuzzleHttp\Exception\GuzzleException;

class SyncData extends Command
{
    use Traits\SyncTrait;

    /**
     * {@inheritdoc}
     */
    protected $signature = 'contentful:sync-data {--preview}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Synchronize Contentful entries and assets';

    /**
     * Contentful Sync API implementation.
     *
     * @var \Distilleries\Contentful\Api\SyncApi
     */
    protected $api;

    /**
     * SyncData command constructor.
     *
     * @param  \Distilleries\Contentful\Api\SyncApi  $api
     * @return void
     */
    public function __construct(SyncApi $api)
    {
        parent::__construct();

        $this->api = $api;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->option('preview')) {
            use_contentful_preview();
        }

        $this->line('Clean previous synced data');
        DB::table('sync_entries')->truncate();

        $this->line('Syncing assets...');
        $this->syncAssets();

        $this->line('Syncing entries...');
        $this->syncEntries();
    }

    /**
     * Synchronize assets via Sync API and store into DB for further use.
     *
     * @return void
     */
    private function syncAssets()
    {
        try {
            $assets = $this->api->syncInitial('Asset');
            while (! empty($assets)) {
                $this->saveAssets($assets);
                $assets = $this->api->syncNext();
            }
        } catch (GuzzleException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Save given Contentful assets.
     *
     * @param  array  $assets
     * @return void
     */
    private function saveAssets(array $assets)
    {
        DB::transaction(function () use ($assets) {
            foreach ($assets as $asset) {
                DB::table('sync_entries')->insert([
                    'contentful_id' => $asset['sys']['id'],
                    'contentful_type' => 'asset',
                    'payload' => json_encode($asset),
                ]);
            }
        });
    }

    /**
     * Synchronize entries via Sync API and store into DB for further use.
     *
     * @return void
     */
    private function syncEntries()
    {
        try {
            $entries = $this->api->syncInitial('Entry');
            while (! empty($entries)) {
                $this->saveEntries($entries);
                $entries = $this->api->syncNext();
            }
        } catch (GuzzleException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Save given Contentful entries.
     *
     * @param  array  $entries
     * @return void
     */
    private function saveEntries(array $entries)
    {
        DB::transaction(function () use ($entries) {
            foreach ($entries as $entry) {
                DB::table('sync_entries')->insert([
                    'contentful_id' => $entry['sys']['id'],
                    'contentful_type' => $entry['sys']['contentType']['sys']['id'],
                    'payload' => json_encode($entry),
                ]);
            }
        });
    }
}
