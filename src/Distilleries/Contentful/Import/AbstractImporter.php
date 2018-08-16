<?php

namespace Distilleries\Contentful\Import;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Distilleries\Contentful\Models\Locale;
use Distilleries\Contentful\Api\ManagementApi;

abstract class AbstractImporter
{
    /**
     * Management API implementation.
     *
     * @var \Distilleries\Contentful\Api\ManagementApi
     */
    protected $api;

    /**
     * Importer constructor.
     *
     * @param  \Distilleries\Contentful\Api\ManagementApi  $api
     */
    public function __construct(ManagementApi $api)
    {
        $this->api = $api;
    }

    /**
     * Import given data to Contentful (API call).
     *
     * @param  array  $data
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    abstract public function import(array $data): array;

    /**
     * Store entry in imported entries table.
     *
     * @param  array  $entry
     * @return void
     */
    protected function storeImport(array $entry)
    {
        $importEntry = DB::table('import_entries')
            ->where('contentful_id', '=', $entry['sys']['id'])
            ->first();

        if (empty($importEntry)) {
            DB::table('import_entries')
                ->insert([
                    'contentful_id' => $entry['sys']['id'],
                    'contentful_type' => ($entry['sys']['type'] === 'Asset') ? 'asset' : $entry['sys']['contentType']['sys']['id'],
                    'version' => (int) $entry['sys']['version'],
                    'imported_at' => Carbon::now(),
                ]);
        } else {
            DB::table('import_entries')
                ->where('contentful_id', '=', $entry['sys']['id'])
                ->update([
                    'version' => (int) $entry['sys']['version'],
                    'imported_at' => Carbon::now(),
                    'published_at' => null,
                ]);
        }
    }

    /**
     * Index given data with locale to match Contentful fields structure.
     *
     * @param  array  $data
     * @param  string  $locale
     * @return array
     */
    protected function indexFieldsWithLocales(array $data, string $locale = ''): array
    {
        $indexed = [];

        $locale = ! empty($locale) ? $locale : Locale::default();
        foreach ($data as $field => $value) {
            $indexed[$field][$locale] = $value;
        }

        return $indexed;
    }

    /**
     * Return entry Link signature.
     *
     * @param  array  $entry
     * @return array
     */
    protected function mapEntryLink(array $entry): array
    {
        return [
            'sys' => [
                'id' => $entry['sys']['id'],
                'type' => 'Link',
                'linkType' => $entry['sys']['type'],
            ],
        ];
    }
}
