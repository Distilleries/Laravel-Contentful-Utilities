<?php

namespace Distilleries\Contentful\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
     * @param \Illuminate\Support\Collection  $locales
     * @return void
     */
    public function toContentfulModel(array $asset, Collection $locales)
    {
        $this->upsertEntryType($asset, 'asset');

        foreach ($locales as $locale) {
            $this->upsertAsset($asset, $locale->code);
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
    private function assetLocales(array $asset): array
    {
        $locales = [];

        if (isset($asset['fields']) && ! empty($asset['fields'])) {
            $firstField = Arr::first($asset['fields']);
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
    private function upsertAsset(array $asset, string $locale): ?Asset
    {
        $country = Locale::getCountry($locale);
        $iso = Locale::getLocale($locale);

        if (! Locale::canBeSave($country, $iso)) {
            return null;
        }

        $data = $this->mapAsset($asset, $locale);
        $instance = Asset::query()
            ->where('contentful_id', '=', $asset['sys']['id'])
            ->where('locale', '=', $iso)
            ->where('country', '=', $country)
            ->first();

        if (empty($instance)) {
            $instance = Asset::query()->create($data);
        } else {
            Asset::query()
                ->where('contentful_id', '=', $asset['sys']['id'])
                ->where('locale', '=', $iso)
                ->where('country', '=', $country)
                ->update($data);

            $instance = Asset::query()
                ->where('contentful_id', '=', $asset['sys']['id'])
                ->where('locale', '=', $iso)
                ->where('country', '=', $country)
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
    private function mapAsset(array $asset, string $locale): array
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
    private function fieldsWithFallback(array $fields, string $locale): array
    {
        $fallbackLocale = Locale::fallback($locale);
        $secondFallback = Locale::fallback($fallbackLocale);
        $file = $this->getFieldValue($fields, 'file', $locale, $fallbackLocale, [], $secondFallback);
        $details = isset($file['details']) ? $file['details'] : [];

        return [
            'title' => $this->getFieldValue($fields, 'title', $locale, $fallbackLocale, '', $secondFallback),
            'description' => $this->getFieldValue($fields, 'description', $locale, $fallbackLocale, '', $secondFallback),
            'url' => isset($file['url']) ? $file['url'] : '',
            'file_name' => isset($file['fileName']) ? $file['fileName'] : '',
            'content_type' => isset($file['contentType']) ? $file['contentType'] : '',
            'size' => (isset($details['size'])) ? intval($details['size']) : 0,
            'width' => (isset($details['image']) && isset($details['image']['width'])) ? intval($details['image']['width']) : 0,
            'height' => (isset($details['image']) && isset($details['image']['height'])) ? intval($details['image']['height']) : 0,
        ];
    }

    /**
     * Get given field value.
     *
     * @param  array  $fields
     * @param  string  $field
     * @param  string  $locale
     * @param  string  $fallbackLocale
     * @param  mixed  $default
     * @param  string|null  $secondFallback
     * @return mixed
     */
    protected function getFieldValue(array $fields, string $field, string $locale, string $fallbackLocale, $default, string $secondFallback = null)
    {
        return ! empty($fields[$field][$locale]) ?
            $fields[$field][$locale] :
            (
                ! empty($fields[$field][$fallbackLocale]) ? $fields[$field][$fallbackLocale] :
                (! empty($secondFallback) && ! empty($fields[$field][$secondFallback]) ? $fields[$field][$secondFallback] : $default)
            );
    }
}
