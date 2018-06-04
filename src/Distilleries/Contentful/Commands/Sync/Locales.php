<?php

namespace Distilleries\Contentful\Commands\Sync;

use Illuminate\Console\Command;
use GuzzleHttp\Exception\GuzzleException;
use Distilleries\Contentful\Models\Locale;
use Distilleries\Contentful\Api\Management\Api;

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
     * Contentful Management API.
     *
     * @var \Distilleries\Contentful\Api\Management\Api
     */
    protected $api;

    /**
     * Locales command constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->api = new Api;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        Locale::query()->truncate();

        try {
            $data = $this->api->locales();
        } catch (GuzzleException $e) {
            $this->error($e->getMessage());
            return;
        }

        foreach ($data['items'] as $locale) {
            Locale::query()->create([
                'name' => $locale['name'],
                'code' => $locale['code'],
                'fallback_code' => $locale['fallbackCode'],
                'is_editable' => ! empty($locale['contentManagementApi']),
                'is_publishable' => ! empty($locale['contentDeliveryApi']),
            ]);
        }
    }
}
