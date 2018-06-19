<?php

namespace Distilleries\Contentful\Models\Base;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Distilleries\Contentful\Models\Locale;

abstract class ContentfulMapper
{
    /**
     * Map entry specific payload.
     *
     * @param  array  $entry
     * @param  string  $locale
     * @return array
     * @throws \Exception
     */
    abstract protected function map(array $entry, string $locale) : array;

    /**
     * Map entry with common data + specific payload for each locales.
     *
     * @param  array  $entry
     * @return array
     * @throws \Exception
     */
    public function toLocaleEntries(array $entry) : array
    {
        $entries = [];

        $common = [
            'contentful_id' => $entry['sys']['id'],
            'created_at' => new Carbon($entry['sys']['createdAt']),
            'updated_at' => new Carbon($entry['sys']['updatedAt']),
        ];

        $locales = $this->entryLocales($entry);
        foreach ($locales as $locale) {
            // Add specific fields
            $data = array_merge($common, $this->map($entry, $locale));

            $data['locale'] = $locale;

            if (! isset($data['payload'])) {
                $data['payload'] = $this->mapPayload($entry, $locale);
            }

            if (! isset($data['relationships'])) {
                $data['relationships'] = $this->mapRelationships($data['payload']);
            }

            $entries[] = $data;
        }

        return $entries;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Return raw entry fields payload for given locale.
     *
     * @param  array  $entry
     * @param  string  $locale
     * @return array
     */
    protected function mapPayload(array $entry, string $locale) : array
    {
        $payload = [];

        $fallbackLocale = Locale::fallback($locale);
        foreach ($entry['fields'] as $field => $localesData) {
            if (isset($localesData[$locale])) {
                $payload[$field] = $localesData[$locale];
            } else {
                // Fallback field...
                if (isset($localesData[$fallbackLocale])) {
                    $payload[$field] = $localesData[$fallbackLocale];
                } else {
                    $payload[$field] = null;
                }
            }
        }

        return $payload;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Map relationships in given payload.
     *
     * @param  array  $payload
     * @return array
     * @throws \Exception
     */
    protected function mapRelationships($payload) : array
    {
        $relationships = [];

        foreach ($payload as $field => $value) {
            if (is_array($value)) {
                if ($this->isLink($value)) {
                    $relationships[] = $this->relationshipSignature($value);
                } else {
                    foreach ($value as $entry) {
                        if ($this->isLink($entry)) {
                            $relationships[] = $this->relationshipSignature($entry);
                        }
                    }
                }
            } else {
                // No relationship
            }
        }

        return $relationships;
    }

    /**
     * Return relationship signature for given "localized" field.
     *
     * @param  array  $localeField
     * @return array|null
     * @throws \Exception
     */
    private function relationshipSignature($localeField) : ?array
    {
        if ($localeField['sys']['linkType'] === 'Asset') {
            return ['id' => $localeField['sys']['id'], 'type' => 'asset'];
        } elseif ($localeField['sys']['linkType'] === 'Entry') {
            if (app()->runningInConsole()) {
                // From SYNC
                return ['id' => $localeField['sys']['id'], 'type' => $this->contentTypeFromSyncEntries($localeField['sys']['id'])];
            } else {
                // From Webhook
                return ['id' => $localeField['sys']['id'], 'type' => $this->contentTypeFromEntryTypes($localeField['sys']['id'])];
            }
        }

        throw new Exception('Invalid field signature... ' . PHP_EOL . print_r($localeField, true));
    }

    /**
     * Return if field is a Link one.
     *
     * @param  mixed  $localeField
     * @return bool
     */
    private function isLink($localeField) : bool
    {
        return isset($localeField['sys']) and isset($localeField['sys']['type']) and ($localeField['sys']['type'] === 'Link');
    }

    /**
     * Return contentful-type for given Contentful ID from `sync_entries` table.
     *
     * @param  string  $contentfulId
     * @return string
     * @throws \Exception
     */
    private function contentTypeFromSyncEntries(string $contentfulId) : string
    {
        $pivot = DB::table('sync_entries')
            ->select('contentful_type')
            ->where('contentful_id', '=', $contentfulId)
            ->first();

        if (empty($pivot)) {
            throw new Exception('Unknown content-type from synced entry: ' . $contentfulId);
        }

        return $pivot->contentful_type;
    }

    /**
     * Return content-type for given Contentful ID from `entry_types` table.
     *
     * @param  string  $contentfulId
     * @return string
     * @throws \Exception
     */
    private function contentTypeFromEntryTypes(string $contentfulId) : string
    {
        $pivot = DB::table('entry_types')
            ->select('contentful_type')
            ->where('contentful_id', '=', $contentfulId)
            ->first();

        if (empty($pivot)) {
            throw new Exception('Unknown content-type from webhook entry: ' . $contentfulId);
        }

        return $pivot->contentful_type;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Return all locales in entry payload.
     *
     * @param  array  $entry
     * @return array
     */
    private function entryLocales(array $entry) : array
    {
        $locales = [];

        if (isset($entry['fields']) and ! empty($entry['fields'])) {
            $firstField = array_first($entry['fields']);
            $locales = array_keys($firstField);
        }

        return $locales;
    }
}
