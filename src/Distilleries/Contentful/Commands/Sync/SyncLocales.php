<?php

namespace Distilleries\Contentful\Commands\Sync;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\GuzzleException;
use Distilleries\Contentful\Models\Locale;
use Distilleries\Contentful\Api\ManagementApi;

class SyncLocales extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'contentful:sync-locales {--preview}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Synchronize Contentful locales';

    /**
     * Contentful Management API implementation.
     *
     * @var \Distilleries\Contentful\Api\ManagementApi
     */
    protected $api;

    /**
     * SyncLocales constructor.
     *
     * @param  \Distilleries\Contentful\Api\ManagementApi $api
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
        if ($this->option('preview')) {
            use_contentful_preview();
        }

        try {
            $data = $this->api->locales();
            $this->resetLocales($data['items']);
        } catch (GuzzleException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Reset Contentful locales in application DB.
     *
     * @param  array  $locales
     * @return void
     */
    private function resetLocales(array $locales)
    {
        if (! empty($locales)) {
            Cache::forget('locale_default');
            Locale::query()->truncate();

            foreach ($locales as $locale) {
                $this->createLocale($locale);
            }
        }
    }

    /**
     * Create locale in DB.
     *
     * @param  array  $locale
     * @return void
     */
    private function createLocale(array $locale)
    {
        Locale::query()->create([
            'label' => $locale['name'],
            'code' => $locale['code'],
            'country' => Locale::getCountry($locale['code']),
            'locale' => Locale::getLocale($locale['code']),
            'fallback_code' => $locale['fallbackCode'],
            'is_default' => ! empty($locale['default']),
            'is_editable' => ! empty($locale['contentManagementApi']),
            'is_publishable' => ! empty($locale['contentDeliveryApi']),
        ]);
    }
}
