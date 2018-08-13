<?php

namespace Distilleries\Contentful\Commands\Sync;

use Illuminate\Console\Command;

class SyncSwitch extends Command
{
    use Traits\SyncTrait;

    /**
     * {@inheritdoc}
     */
    protected $signature = 'contentful:sync-switch {--preview} {--live}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Dump and switch the database';

    protected function isFromSyncToLive(): bool
    {
        $bool = $this->option('live');
        return !empty($bool) ? true : false;
    }

    protected function isPreview(): bool
    {
        $bool = $this->option('preview');
        return !empty($bool) ? true : false;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {

        $isPreview = $this->isPreview();

        if ($this->isPreview()) {
            use_contentful_preview();
        }

        if ($this->isFromSyncToLive()) {
            $dumpPath = $this->dumpSync($isPreview, 'mysql_sync');
            $this->putSync($dumpPath, $isPreview);
        } else {
            $this->putSync($this->dumpSync($isPreview), $isPreview, 'mysql_sync');
            $this->switchToSyncDb();
        }

    }
}
