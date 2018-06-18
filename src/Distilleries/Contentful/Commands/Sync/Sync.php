<?php

namespace Distilleries\Contentful\Commands\Sync;

use Illuminate\Console\Command;

class Sync extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'contentful:sync {--preview}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Synchronize Contentful process (locales, raw fetch, map and persist)';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $arguments = [];
        if ($this->option('preview')) {
            $arguments['--preview'] = true;
        }

        $this->line('Syncing locales...');
        $this->call('contentful:sync-locales', $arguments);

        $this->call('contentful:sync-data', $arguments);

        $this->call('contentful:sync-flatten', $arguments);
    }
}
