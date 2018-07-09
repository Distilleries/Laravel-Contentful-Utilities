<?php

namespace Distilleries\Contentful\Commands\Sync;

use stdClass;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;
use Distilleries\Contentful\Repositories\AssetsRepository;
use Distilleries\Contentful\Repositories\EntriesRepository;

class SyncFlatten extends Command
{
    use Traits\SyncTrait;

    /**
     * Number of entries to fetch per pagination.
     *
     * @var integer
     */
    const PER_BATCH = 50;

    /**
     * {@inheritdoc}
     */
    protected $signature = 'contentful:sync-flatten {--preview}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Map and persist previously synced Contentful data';

    /**
     * Assets repository instance.
     *
     * @var \Distilleries\Contentful\Repositories\AssetsRepository
     */
    protected $assets;

    /**
     * Entries repository instance.
     *
     * @var \Distilleries\Contentful\Repositories\EntriesRepository
     */
    protected $entries;

    /**
     * MapEntries command constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->assets = new AssetsRepository;

        $this->entries = new EntriesRepository;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $isPreview = $this->option('preview');
        if ($isPreview) {
            use_contentful_preview();
        }

        $this->switchToSyncDb();

        $this->line('Truncate Contentful related tables');
        $this->assets->truncateRelatedTables();
        $this->entries->truncateRelatedTables();

        $this->line('Map and persist synced data');
        try {
            $this->flattenSyncedData();
        } catch (Exception $e) {
            echo PHP_EOL;
            $this->error($e->getMessage());
            return;
        }

        $dumpPath = $this->dumpSync($isPreview);
        $this->putSync($dumpPath, $isPreview);
    }

    /**
     * Map and persist synced data.
     *
     * @return void
     * @throws \Exception
     */
    private function flattenSyncedData()
    {
        $page = 1;
        $paginator = DB::table('sync_entries')->paginate(static::PER_BATCH, ['*'], 'page', $page);

        $bar = $this->createProgressBar($paginator->total());
        while ($paginator->isNotEmpty()) {
            foreach ($paginator->items() as $item) {
                $bar->setMessage('Map entry ID: ' . $item->contentful_id);
                $this->mapItemToContentfulModel($item);
                $bar->advance();
            }

            $page++;
            $paginator = DB::table('sync_entries')->paginate(static::PER_BATCH, ['*'], 'page', $page);
        }
        $bar->finish();

        echo PHP_EOL;
    }

    /**
     * Create custom progress bar.
     *
     * @param  integer  $total
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    private function createProgressBar(int $total) : ProgressBar
    {
        $bar = $this->output->createProgressBar($total);

        $bar->setFormat("%message%" . PHP_EOL . " %current%/%max% [%bar%] %percent:3s%%");

        return $bar;
    }

    /**
     * Map and persist given sync_entries item.
     *
     * @param  \stdClass  $item
     * @return void
     * @throws \Exception
     */
    private function mapItemToContentfulModel(stdClass $item)
    {
        $entry = json_decode($item->payload, true);

        if ($item->contentful_type === 'asset') {
            $this->assets->toContentfulModel($entry);
        } else {
            $this->entries->toContentfulModel($entry);
        }
    }
}
