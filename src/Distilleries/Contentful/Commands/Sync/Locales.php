<?php

namespace Distilleries\Contentful\Commands\Sync;

use Illuminate\Console\Command;

class Locales extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'contentful:sync:locales';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Refresh Contentful space locales data';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->line('Sync CF locales');
    }
}
