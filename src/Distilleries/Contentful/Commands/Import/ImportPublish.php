<?php

namespace Distilleries\Contentful\Commands\Import;

use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\GuzzleException;
use Distilleries\Contentful\Api\ManagementApi;

class ImportPublish extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'contentful:import-publish';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Publish imported entries in Contentful';

    /**
     * Management API implementation.
     *
     * @var \Distilleries\Contentful\Api\ManagementApi
     */
    protected $api;

    /**
     * ContentfulPublish command constructor.
     *
     * @param  \Distilleries\Contentful\Api\ManagementApi  $managementApi
     */
    public function __construct(ManagementApi $managementApi)
    {
        parent::__construct();

        $this->api = $managementApi;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->warn('Publish imported Contentful entries...');

        $importedEntries = DB::table('import_entries')->get();

        $this->publish($importedEntries);
    }

    /**
     * Publish given imported entries collection.
     *
     * @param  \Illuminate\Support\Collection  $importedEntries
     * @return void
     */
    private function publish(Collection $importedEntries)
    {
        $bar = $this->output->createProgressBar(count($importedEntries));

        foreach ($importedEntries as $importedEntry) {
            if (empty($importedEntry->published_at)) {
                try {
                    if ($importedEntry->contentful_type === 'asset') {
                        $this->api->publishAsset($importedEntry->contentful_id, $importedEntry->version + 1);
                    } else {
                        $this->api->publishEntry($importedEntry->contentful_id, $importedEntry->version);
                    }

                    DB::table('import_entries')->where('contentful_id', '=', $importedEntry->contentful_id)->update(['published_at' => Carbon::now()]);
                } catch (GuzzleException $e) {
                    DB::table('import_entries')->where('contentful_id', '=', $importedEntry->contentful_id)->update(['version' => $importedEntry->version + 1]);

                    $this->error($e->getMessage());
                }
            }

            $bar->advance();
        }

        $bar->finish();
    }
}
