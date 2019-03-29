<?php

namespace Distilleries\Contentful\Models\Base;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Distilleries\Contentful\Models\Locale;
use Distilleries\Contentful\Api\DeliveryApi;
use Distilleries\Contentful\Repositories\Traits\EntryType;

abstract class ContentfulMapper
{
    use EntryType;

    /**
     * Map entry specific payload.
     *
     * @param  array $entry
     * @param  string $locale
     * @return array
     * @throws \Exception
     */
    abstract protected function map(array $entry, string $locale): array;

    /**
     * Map entry with common data + specific payload for each locales.
     *
     * @param  array $entry
     * @param  \Illuminate\Support\Collection $locales
     * @return array
     * @throws \Exception
     */
    public function toLocaleEntries(array $entry, Collection $locales): array
    {
        $entries = [];

        $common = [
            'contentful_id' => $entry['sys']['id'],
            'created_at' => new Carbon($entry['sys']['createdAt']),
            'updated_at' => new Carbon($entry['sys']['updatedAt']),
        ];

        foreach ($locales as $locale) {
            // Add specific fields
            $data = array_merge($common, $this->map($entry, $locale->code));

            $data['country'] = Locale::getCountry($locale->code);
            $data['locale'] = Locale::getLocale($locale->code);

            if (!isset($data['payload'])) {
                $data['payload'] = $this->mapPayload($entry, $locale->code);
            }

            if (!isset($data['relationships'])) {
                $data['relationships'] = $this->mapRelationships($data['payload']);
            }

            if (isset($data['slug']) && Str::contains($data['slug'], 'untitled-')) {
                $data['slug'] = null;
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
     * @param  array $entry
     * @param  string $locale
     * @return array
     */
    protected function mapPayload(array $entry, string $locale): array
    {
        $payload = [];
        $dontFallback = config('contentful.payload_fields_not_fallback', []);

        $fallbackLocale = Locale::fallback($locale);
        $fallbackSecondLevel = !empty($fallbackLocale) ? Locale::fallback($fallbackLocale) : null;

        foreach ($entry['fields'] as $field => $localesData) {
            if (isset($localesData[$locale])) {
                $payload[$field] = $localesData[$locale];
            } else {
                if (!in_array($field, $dontFallback)
                    && isset($localesData[$fallbackLocale])
                    && ($this->levelFallBack($field) === 'all')) {
                    $payload[$field] = $localesData[$fallbackLocale];
                } else {
                    if (!empty($fallbackSecondLevel) && !in_array($field, $dontFallback)
                        && isset($localesData[$fallbackSecondLevel])
                        && ($this->levelFallBack($field) === 'all')) {
                        $payload[$field] = $localesData[$fallbackSecondLevel];
                    } else {
                        $payload[$field] = null;
                    }
                }
            }
        }

        return $payload;
    }

    /**
     * Level fallback.
     *
     * @param  string $field
     * @return string
     */
    protected function levelFallBack($field): string
    {
        $levelMaster = ['slug'];

        return in_array($field, $levelMaster) ? 'master' : 'all';
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Map relationships in given payload.
     *
     * @param  array $payload
     * @return array
     * @throws \Exception
     */
    protected function mapRelationships($payload): array
    {
        $relationships = [];

        foreach ($payload as $field => $value) {
            if (is_array($value)) {
                if ($this->isLink($value)) {
                    try {
                        $relationships[] = $this->relationshipSignature($value,$field);
                    } catch (Exception $e) {
                        //
                    }
                } else {
                    foreach ($value as $entry) {
                        if ($this->isLink($entry)) {
                            try {
                                $relationships[] = $this->relationshipSignature($entry,$field);
                            } catch (Exception $e) {
                                //
                            }
                        }
                    }
                }
            }
        }

        return $relationships;
    }

    /**
     * Return relationship signature for given "localized" field.
     *
     * @param  array $localeField
     * @return array|null
     * @throws \Exception
     */
    private function relationshipSignature(array $localeField,string $field=''): ?array
    {
        if ($localeField['sys']['linkType'] === 'Asset') {
            return [
                'id' => $localeField['sys']['id'],
                'type' => 'asset',
                'field' => $field,
            ];
        } else {
            if ($localeField['sys']['linkType'] === 'Entry') {
                return [
                    'id' => $localeField['sys']['id'],
                    'type' => $this->contentTypeFromEntryTypes($localeField['sys']['id']),
                    'field' => $field,
                ];
            }
        }

        throw new Exception('Invalid field signature... ' . PHP_EOL . print_r($localeField, true));
    }

    /**
     * Return if field is a Link one.
     *
     * @param  mixed $localeField
     * @return boolean
     */
    private function isLink($localeField): bool
    {
        return isset($localeField['sys']) && isset($localeField['sys']['type']) && ($localeField['sys']['type'] === 'Link');
    }

    /**
     * Return contentful-type for given Contentful ID from `sync_entries` table.
     *
     * @param  string $contentfulId
     * @return string
     * @throws \Exception
     */
    public function contentTypeFromEntryTypes(string $contentfulId): string
    {
        $pivot = DB::table('sync_entries')
            ->select('contentful_type')
            ->where('contentful_id', '=', $contentfulId)
            ->first();

        if (empty($pivot)) {
            try {
                $entry = app(DeliveryApi::class)->entries([
                    'id' => $contentfulId,
                    'locale' => '*',
                    'content_type' => 'single_entry',
                ]);

                if (!empty($entry) && !empty($entry['sys']['contentType']) && !empty($entry['sys']['contentType']['sys'])) {
                    $this->upsertEntryType($entry, $entry['sys']['contentType']['sys']['id']);

                    return $entry['sys']['contentType']['sys']['id'];
                }
            } catch (Exception $e) {
                throw new Exception('Unknown content-type from synced entry: ' . $contentfulId);
            }
        }

        return $pivot->contentful_type;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Return all locales in entry payload.
     *
     * @param  array $entry
     * @return array
     */
    private function entryLocales(array $entry): array
    {
        $locales = [];

        if (isset($entry['fields']) && !empty($entry['fields'])) {
            $firstField = array_first($entry['fields']);
            $locales = array_keys($firstField);
        }

        return $locales;
    }
}
