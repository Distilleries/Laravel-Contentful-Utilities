<?php

namespace Distilleries\Contentful\Commands\Sync;

use Distilleries\Contentful\Models\Release;
use stdClass;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Distilleries\Contentful\Models\Locale;
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
    protected $signature = 'contentful:sync-flatten {--preview} {--no-switch} {--no-truncate} {--multi}';

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

    protected function canSwitch(): bool
    {
        $bool = $this->option('no-switch');
        return !empty($bool) ? false : true;
    }

    protected function withoutTruncate(): bool
    {
        $bool = $this->option('no-truncate');
        return !empty($bool) ? true : false;
    }

    protected function isPreview(): bool
    {
        $bool = $this->option('preview');
        return !empty($bool) ? true : false;
    }

    protected function isMultiThread(): bool
    {
        $bool = $this->option('multi');
        return !empty($bool) ? true : false;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(Release $release)
    {
        $isPreview = $this->isPreview();

        if ($this->canSwitch()) {
            $this->call('contentful:sync-switch', $isPreview ? ['--preview'=>true] : []);
        }

        if ($isPreview) {
            use_contentful_preview();
        }

        if (!$this->withoutTruncate()) {
            $this->line('Truncate Contentful related tables');
            $this->assets->truncateRelatedTables();
            $this->entries->truncateRelatedTables();
        }

        try {
            $this->line('Map and persist synced data');
            if($this->isMultiThread()){
                $release = $this->getCurrentRelease($release);
                $this->flattenSyncedDataMultiThread($release);
            }else{
                $this->flattenSyncedData();
            }


        } catch (Exception $e) {
            echo PHP_EOL;
            $this->error($e->getMessage());
            return;
        }

        if ($this->canSwitch()) {
            $this->call('contentful:sync-switch', $isPreview ? ['--preview'=>true, '--live'=>true] : ['--live'=>true]);
        }
    }

    /**
     * Map and persist synced data.
     *
     * @return void
     * @throws \Exception
     */
    protected function flattenSyncedData()
    {
        $page = 1;
        $paginator = DB::table('sync_entries')->paginate(static::PER_BATCH, ['*'], 'page', $page);
        $locales = Locale::all();

        $bar = $this->createProgressBar($paginator->total());
        while ($paginator->isNotEmpty()) {
            foreach ($paginator->items() as $item) {
                $bar->setMessage('Map entry ID: ' . $item->contentful_id);
                $this->mapItemToContentfulModel($item, $locales);
                $bar->advance();
            }

            $page++;
            $paginator = DB::table('sync_entries')->paginate(static::PER_BATCH, ['*'], 'page', $page);
        }
        $bar->finish();

        echo PHP_EOL;
    }

    protected function flattenSyncedDataMultiThread(Release $release)
    {

        $locales = Locale::all();
        $bar = $this->createProgressBar(DB::table('sync_entries')->count());

        try {
            $this->updateFromOtherThread($bar);
            $items = collect();

            DB::transaction(function () use ($release, & $items) {
                $items = DB::table('sync_entries')
                    ->whereNull('release_id')
                    ->take(static::PER_BATCH)
                    ->lockForUpdate()
                    ->get();

                DB::table('sync_entries')
                    ->whereIn('contentful_id', $items->pluck('contentful_id')->toArray())
                    ->lockForUpdate()
                    ->update(['release_id' => $release->getKey()]);
            });
        } catch (Exception $e) {
            //
        }

        $items->each(function ($item, $key) use ($locales, $bar) {
            $bar->setMessage('Map entry ID: ' . $item->contentful_id);
            $this->mapItemToContentfulModel($item, $locales);
            $bar->advance();
        });

        $bar->finish();

    }

    /**
     * Update progress from other threads.
     *
     * @return void
     */
    protected function updateFromOtherThread($bar)
    {
        $bar->setProgress((DB::table('sync_entries'))->whereNotNull('release_id')->count());
    }

    /**
     * Get current release.
     *
     */
    protected function getCurrentRelease(Release $release)
    {
        return $release->where('current', true)->get()->first();
    }

    /**
     * Create custom progress bar.
     *
     * @param  integer $total
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    protected function createProgressBar(int $total): ProgressBar
    {
        $bar = $this->output->createProgressBar($total);

        $bar->setFormat("%message%" . PHP_EOL . " %current%/%max% [%bar%] %percent:3s%%");

        return $bar;
    }

    /**
     * Map and persist given sync_entries item.
     *
     * @param  \stdClass $item
     * @param \Illuminate\Support\Collection $locales
     * @return void
     * @throws \Exception
     */
    protected function mapItemToContentfulModel(stdClass $item, Collection $locales)
    {
        $entry = json_decode($item->payload, true);

        if ($item->contentful_type === 'asset') {
            $this->assets->toContentfulModel($entry, $locales);
        } else {
            $this->entries->toContentfulModel($entry, $locales);
        }
    }
}
