<?php

namespace Distilleries\Contentful\Commands\Import;

use stdClass;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\GuzzleException;
use Distilleries\Contentful\Api\ManagementApi;

class ImportClean extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'contentful:import-clean';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Clean imported entries in Contentful space';

    /**
     * Management API implementation.
     *
     * @var \Distilleries\Contentful\Api\ManagementApi
     */
    protected $api;

    /**
     * ImportClean command constructor.
     *
     * @param  \Distilleries\Contentful\Api\ManagementApi  $api
     * @return void
     */
    public function __construct(ManagementApi $api)
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
        $this->warn('Clean imported Entry and Asset...');
        $importEntries = DB::table('import_entries')->get();

        $bar = $this->output->createProgressBar($importEntries->count());
        foreach ($importEntries as $importEntry) {
            try {
                $this->cleanEntry($importEntry);
            } catch (GuzzleException $e) {
                $this->error(PHP_EOL . $e->getMessage());
            }
            $bar->advance();
        }
        $bar->finish();

        $this->warn('Truncate `import_entries` table...');
        DB::table('import_entries')->truncate();
    }

    /**
     * Clean given entry (unpublish, delete).
     *
     * @param  \stdClass  $entry
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function cleanEntry(stdClass $entry)
    {
        if ($entry->contentful_type === 'asset') {
            if (! empty($entry->published_at)) {
                $this->api->unpublishAsset($entry->contentful_id);
            }
            $this->api->deleteAsset($entry->contentful_id);
        } else {
            if (! empty($entry->published_at)) {
                $this->api->unpublishEntry($entry->contentful_id);
            }
            $this->api->deleteEntry($entry->contentful_id);
        }
    }
}
