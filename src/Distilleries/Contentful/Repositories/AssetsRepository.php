<?php

namespace Distilleries\Contentful\Repositories;

use Distilleries\Contentful\Models\Asset;
use Distilleries\Contentful\Models\Locale;

class AssetsRepository
{
    use Traits\EntryType;

    /**
     * Truncate all Assets related tables.
     *
     * @return void
     */
    public function truncateRelatedTables()
    {
        Asset::query()->truncate();
    }

    /**
     * Map Contentful asset payload to an Eloquent one.
     *
     * @param  array  $asset
     * @return void
     */
    public function toContentfulModel(array $asset)
    {
        $this->upsertEntryType($asset, 'asset');

        $locales = $this->assetLocales($asset);

        foreach ($locales as $locale) {
            $this->upsertAsset($asset, $locale);
        }
    }

    /**
     * Delete asset with given Contentful ID.
     *
     * @param  string  $assetId
     * @return void
     */
    public function delete(string $assetId)
    {
        $this->deleteEntryType($assetId);

        Asset::query()->where('contentful_id', '=', $assetId)->delete();
    }

    /**
     * Return all locales in asset payload.
     *
     * @param  array  $asset
     * @return array
     */
    private function assetLocales(array $asset) : array
    {
        $locales = [];

        if (isset($asset['fields']) and ! empty($asset['fields'])) {
            $firstField = array_first($asset['fields']);
            $locales = array_keys($firstField);
        }

        return $locales;
    }

    /**
     * Insert OR update given asset.
     *
     * @param  array  $asset
     * @param  string  $locale
     * @return \Distilleries\Contentful\Models\Asset
     */
    private function upsertAsset(array $asset, string $locale) : Asset
    {
        $data = $this->mapAsset($asset, $locale);

        $instance = Asset::query()
            ->where('contentful_id', '=', $asset['sys']['id'])
            ->where('locale', '=', Locale::getLocale($locale))
            ->where('country', '=', Locale::getCountry($locale))
            ->first();

        if (empty($instance)) {
            $instance = Asset::query()->create($data);
        } else {
            Asset::query()
                ->where('contentful_id', '=', $asset['sys']['id'])
                ->where('locale', '=', Locale::getLocale($locale))
                ->where('country', '=', Locale::getCountry($locale))
                ->update($data);

            $instance = Asset::query()
                ->where('contentful_id', '=', $asset['sys']['id'])
                ->where('locale', '=', Locale::getLocale($locale))
                ->where('country', '=', Locale::getCountry($locale))
                ->first();
        }

        return $instance;
    }

    /**
     * Map a Contentful asset to it's Eloquent model signature.
     *
     * @param  array  $asset
     * @param  string  $locale
     * @return array
     */
    private function mapAsset(array $asset, string $locale) : array
    {
        return [
            'contentful_id' => $asset['sys']['id'],
            'locale' => Locale::getLocale($locale),
            'country' => Locale::getCountry($locale),
        ] + $this->fieldsWithFallback($asset['fields'], $locale);
    }

    /**
     * Return asset fields with locale OR locale fallback data.
     *
     * @param  array  $fields
     * @param  string  $locale
     * @return array
     */
    private function fieldsWithFallback(array $fields, string $locale) : array
    {
        $fallbackLocale = Locale::fallback($locale);
        $file = ! empty($fields['file'][$locale]) ? $fields['file'][$locale] : (! empty($fields['file'][$fallbackLocale]) ? $fields['file'][$fallbackLocale] : []);

        return [
            'title' => ! empty($fields['title'][$locale]) ? $fields['title'][$locale] : (! empty($fields['title'][$fallbackLocale]) ? $fields['title'][$fallbackLocale] : ''),
            'description' => ! empty($fields['description'][$locale]) ? $fields['description'][$locale] : (! empty($fields['description'][$fallbackLocale]) ? $fields['description'][$fallbackLocale] : ''),
            'url' => isset($file['url']) ? $file['url'] : '',
            'file_name' => isset($file['fileName']) ? $file['fileName'] : '',
            'content_type' => isset($file['contentType']) ? $file['contentType'] : '',
            'size' => (isset($file['details']) and isset($file['details']['size'])) ? intval($file['details']['size']) : 0,
            'width' => (isset($file['details']) and isset($file['details']['image']) and isset($file['details']['image']['width'])) ? intval($file['details']['image']['width']) : 0,
            'height' => (isset($file['details']) and isset($file['details']['image']) and isset($file['details']['image']['height'])) ? intval($file['details']['image']['height']) : 0,
        ];
    }
}
