<?php

namespace Distilleries\Contentful\Models\Base;

use Exception;
use Parsedown;
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
            $data = array_merge($common, $this->map($entry, $locale));
            $data['locale'] = $locale;

            $entries[] = $data;
        }

        return $entries;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Return relationship opinionated array to handle entry single relationship.
     *
     * @param  array  $entry
     * @param  string  $locale
     * @param  string  $field
     * @return array
     * @throws \Exception
     */
    protected function fieldRelationship(array $entry, string $locale, string $field) : array
    {
        $localeField = $this->localeField($entry, $locale, $field);

        $relationship = $this->relationshipSignature($localeField);

        return ! empty($relationship) ? [$relationship] : [];
    }

    /**
     * Return relationship opinionated array to handle entry multiple relationships.
     *
     * @param  array  $entry
     * @param  string  $locale
     * @param  string  $field
     * @return array
     * @throws \Exception
     */
    protected function fieldRelationships(array $entry, string $locale, string $field) : array
    {
        $relationships = [];

        $localeFields = $this->localeField($entry, $locale, $field);
        if (! empty($localeFields)) {
            foreach ($localeFields as $localeField) {
                $relationship = $this->relationshipSignature($localeField);
                if (! empty($relationship)) {
                    $relationships[] = $relationship;
                }
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
        if (isset($localeField['sys']) and isset($localeField['sys']['type']) and ($localeField['sys']['type'] === 'Link')) {
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
        }

        return null;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Return a localised entry field casted as an integer.
     *
     * @param  array  $entry
     * @param  string  $locale
     * @param  string  $field
     * @return int
     */
    protected function fieldInteger(array $entry, string $locale, string $field) : int
    {
        $localeField = $this->localeField($entry, $locale, $field);

        return ! empty($localeField) ? (integer) $localeField : 0;
    }

    /**
     * Return a localised entry field casted as string.
     *
     * @param  array  $entry
     * @param  string  $locale
     * @param  string  $field
     * @return string
     */
    protected function fieldString(array $entry, string $locale, string $field) : string
    {
        $localeField = $this->localeField($entry, $locale, $field);

        return ! empty($localeField) ? (string) $localeField : '';
    }

    /**
     * Return a localised entry field string JSON encoded.
     *
     * @param  array  $entry
     * @param  string  $locale
     * @param  string  $field
     * @return string
     */
    protected function fieldJson(array $entry, string $locale, string $field) : string
    {
        $localeField = $this->localeField($entry, $locale, $field);

        return ! empty($localeField) ? json_encode($localeField) : '';
    }

    /**
     * Return a localised entry field transformed from Markdown HTML string.
     *
     * @param  array  $entry
     * @param  string  $locale
     * @param  string  $field
     * @return string
     */
    protected function fieldMarkdown(array $entry, string $locale, string $field) : string
    {
        $localeField = $this->localeField($entry, $locale, $field);

        if (empty($localeField)) {
            return '';
        }

        $html = (new Parsedown)->setBreaksEnabled(true)->text($localeField);

        return $html;
    }

    /**
     * Return a localised entry or asset ID field.
     *
     * @param  array  $entry
     * @param  string  $locale
     * @param  string  $field
     * @return string|null
     */
    protected function fieldLink(array $entry, string $locale, string $field) : ?string
    {
        $localeField = $this->localeField($entry, $locale, $field);

        if (! isset($localeField['sys']) or ! isset($localeField['sys']['type']) or ($localeField['sys']['type'] !== 'Link')) {
            return null;
        }

        return $localeField['sys']['id'];
    }

    /**
     * Return a localised entry or asset IDs array.
     *
     * @param  array  $entry
     * @param  string  $locale
     * @param  string  $field
     * @return array
     */
    protected function fieldArray(array $entry, string $locale, string $field) : array
    {
        $linkIds = [];

        $localeField = $this->localeField($entry, $locale, $field);
        if (! empty($localeField)) {
            foreach ($localeField as $link) {
                if (isset($link['sys']) and isset($link['sys']['type']) and ($link['sys']['type'] === 'Link')) {
                    $linkIds[] = $link['sys']['id'];
                }
            }
        }

        return $linkIds;
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

    /**
     * Return raw locale field content.
     *
     * @param  array  $entry
     * @param  string  $locale
     * @param  string  $field
     * @return mixed|null
     */
    private function localeField(array $entry, string $locale, string $field)
    {
        if (! isset($entry['fields']) or ! isset($entry['fields'][$field])) {
            return null;
        }

        if (isset($entry['fields'][$field][$locale]) and ! empty($entry['fields'][$field][$locale])) {
            return $entry['fields'][$field][$locale];
        }

        $fallbackLocale = Locale::fallback($locale);
        if (! empty($fallbackLocale) and isset($entry['fields'][$field][$fallbackLocale]) and ! empty($entry['fields'][$field][$fallbackLocale])) {
            return $entry['fields'][$field][$fallbackLocale];
        }

        return null;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

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
}
