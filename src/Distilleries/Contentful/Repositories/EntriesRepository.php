<?php

namespace Distilleries\Contentful\Repositories;

use Distilleries\Contentful\Helpers\NamespaceResolver;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Distilleries\Contentful\Models\Locale;
use Distilleries\Contentful\Models\Base\ContentfulModel;
use Distilleries\Contentful\Models\Base\ContentfulMapper;

class EntriesRepository
{
    use Traits\EntryType;

    /**
     * Truncate all tables extending a ContentfulModel.
     *
     * @return void
     */
    public function truncateRelatedTables()
    {
        $modelPath = config('contentful.generator.model');

        foreach (glob($modelPath . '/*.php') as $file) {
            $modelInstance = NamespaceResolver::model(str_replace(
                [$modelPath, '.php', '/'],
                ['', '', '\\'],
                $file
            ));

            if (!empty($modelInstance) && $modelInstance instanceof ContentfulModel) {
                $modelInstance->query()->truncate();
            }
        }

        DB::table('entry_types')->truncate();
        DB::table('entry_relationships')->truncate();
    }

    /**
     * Map Contentful entry payload to an Eloquent one.
     *
     * @param  array $entry
     * @param \Illuminate\Support\Collection $locales
     * @return void
     * @throws \Exception
     */
    public function toContentfulModel(array $entry, Collection $locales)
    {
        $this->upsertEntryType($entry, $this->entryContentType($entry));
        $this->deleteRelationships($entry);

        $localeEntries = $this->entryMapper($entry)->toLocaleEntries($entry, $locales);
        foreach ($localeEntries as $localeEntry) {
            $model = $this->upsertLocale($entry, $localeEntry);

            if (!empty($model)) {
                if (isset($localeEntry['relationships'])) {
                    $this->handleRelationships($localeEntry['locale'], $localeEntry['contentful_id'],
                        $this->entryContentType($entry), $localeEntry['relationships']);
                    unset($localeEntry['relationships']);
                }
            } else {
                if (isset($localeEntry['relationships'])) {
                    unset($localeEntry['relationships']);
                }
            }
        }
    }

    /**
     * Delete entry and relationships.
     *
     * @param  array $entry
     * @return void
     * @throws \Exception
     */
    public function delete(array $entry)
    {
        $this->deleteEntryType($entry['sys']['id']);
        $this->deleteRelationships($entry);

        $this->entryModel($entry)->query()->where('contentful_id', '=', $entry['sys']['id'])->delete();
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Return entry content-type.
     *
     * @param  array $entry
     * @return string
     */
    private function entryContentType(array $entry): string
    {
        return $entry['sys']['contentType']['sys']['id'];
    }

    /**
     * Return entry content-type mapper class instance.
     *
     * @param  array $entry
     * @return \Distilleries\Contentful\Models\Base\ContentfulMapper
     * @throws \Exception
     */
    private function entryMapper(array $entry): ContentfulMapper
    {
        $class = studly_case($this->entryContentType($entry)) . 'Mapper';
        $mapperClass = NamespaceResolver::mapper($class);

        if (empty($mapperClass) && !($mapperClass instanceof ContentfulMapper)) {
            throw new Exception('Unknown mapper: ' . $class);
        }

        return new $mapperClass;
    }

    /**
     * Return entry content-type model class instance.
     *
     * @param  array $entry
     * @return \Distilleries\Contentful\Models\Base\ContentfulModel
     * @throws \Exception
     */
    private function entryModel(array $entry): ContentfulModel
    {
        $model = studly_case($this->entryContentType($entry));
        $modelClass = NamespaceResolver::modelClass($model);

        if (empty($modelClass)) {
            throw new Exception('Unknown model: ' . $model);
        }

        return new $modelClass;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Handle mapped relationships to fill `entry_relationships` pivot table.
     *
     * @param  string $locale
     * @param  string $sourceId
     * @param  string $sourceType
     * @param  array $relationships
     * @return void
     * @throws \Exception
     */
    private function handleRelationships(
        string $locale,
        string $sourceId,
        string $sourceType,
        array $relationships = []
    ) {
        $country = Locale::getCountry($locale);
        $iso = Locale::getLocale($locale);

        DB::table('entry_relationships')
            ->where('locale', '=', $iso)
            ->where('country', '=', $country)
            ->where('source_contentful_id', '=', $sourceId)
            ->delete();

        $order = 1;
        foreach ($relationships as $relationship) {
            if (!isset($relationship['id']) || !isset($relationship['type'])) {
                throw new Exception('Relationships malformed! (' . print_r($relationship, true) . ')');
            }

            DB::table('entry_relationships')->insert([
                'locale' => $iso,
                'country' => $country,
                'source_contentful_id' => $sourceId,
                'source_contentful_type' => $sourceType,
                'related_contentful_id' => $relationship['id'],
                'related_contentful_type' => $relationship['type'],
                'order' => $order,
                'relation' => isset($relationship['field']) ? $relationship['field'] : null,
            ]);

            $order++;
        }
    }

    /**
     * Delete entry relationships for given Contentful entry.
     *
     * @param  array $entry
     * @return void
     */
    private function deleteRelationships(array $entry)
    {
        DB::table('entry_relationships')
            ->where('source_contentful_id', '=', $entry['sys']['id'])
            ->where('source_contentful_type', '=', $this->entryContentType($entry))
            ->delete();
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Return inserted / updated model instance for given parameters.
     *
     * @param  array $entry
     * @param  array $data
     * @return \Distilleries\Contentful\Models\Base\ContentfulModel|null
     * @throws \Exception
     */
    private function upsertLocale(array $entry, array $data): ?ContentfulModel
    {
        $model = $this->entryModel($entry);
        if ((method_exists($model, 'bootNotNullSlug') && empty($data['slug'])) || !Locale::canBeSave($data['country'],
                $data['locale'])) {

            //Remove instance if slug is empty
            $instance = $this->instanceQueryBuilder($model, $data)->first();
            if (!empty($instance)) {
                $instance->delete();
            }

            return null;
        }

        if (!isset($data['payload'])) {
            throw new Exception('Mapper for model ' . class_basename($model) . ' must set a "payload" key');
        }

        $instance = $this->instanceQueryBuilder($model, $data)->first();

        if (empty($instance)) {
            $model->fill($data)->save();
        } else {
            $this->overridePayloadAndExtraFillables($model, $data);
        }

        return $this->instanceQueryBuilder($model, $data)->first();
    }

    /**
     * Override Eloquent entry with all fillable data.
     *
     * @param  \Distilleries\Contentful\Models\Base\ContentfulModel $model
     * @param  array $data
     * @return void
     */
    private function overridePayloadAndExtraFillables(ContentfulModel $model, array $data)
    {
        $fillables = $model->getFillable();

        // In this way we can delegate extra field update to
        // the Model itself (eg. adding slug or publishing date).
        $update = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $fillables)) {
                $update[$key] = $value;
            }
        }
        $update['payload'] = json_encode($data['payload']);

        $this->instanceQueryBuilder($model, $data)->update($update);
    }

    /**
     * Return Eloquent QueryBuilder to target given entry.
     *
     * @param  \Distilleries\Contentful\Models\Base\ContentfulModel $model
     * @param  array $data
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function instanceQueryBuilder(ContentfulModel $model, array $data): Builder
    {
        return $model
            ->withoutGlobalScopes()
            ->where('contentful_id', '=', $data['contentful_id'])
            ->where('locale', '=', $data['locale']);
    }
}
