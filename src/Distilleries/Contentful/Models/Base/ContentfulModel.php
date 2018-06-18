<?php

namespace Distilleries\Contentful\Models\Base;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Distilleries\Contentful\Models\Asset;
use Distilleries\Contentful\Models\Locale;

abstract class ContentfulModel extends Model
{
    /**
     * {@inheritdoc}
     */
    public $primaryKey = null;

    /**
     * {@inheritdoc}
     */
    public $incrementing = false;

    /**
     * ContentfulModel constructor.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        // Override fillable
        foreach ($this->defaultFillable() as $defaultFillable) {
            if (! in_array($defaultFillable, $this->fillable)) {
                $this->fillable[] = $defaultFillable;
            }
        }

        // Override casts
        foreach ($this->defaultCasts() as $field => $type) {
            if (! isset($this->casts[$field])) {
                $this->casts[$field] = $type;
            }
        }

        parent::__construct($attributes);
    }

    /**
     * Return default fillable fields.
     *
     * @return array
     */
    public function defaultFillable() : array
    {
        return [
            'contentful_id',
            'locale',
            'payload',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Return default casted fields.
     *
     * @return array
     */
    public function defaultCasts() : array
    {
        return [
            'payload' => 'array',
        ];
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Scope a query to a given locale.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $locale
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLocale($query, string $locale = '') : Builder
    {
        $locale = ! empty($locale) ? $locale : Locale::default();

        return $query->where($this->getTable() . '.locale', '=', $locale);
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Return Contentful Asset for given ID.
     *
     * @param  string|null  $assetId
     * @return \Distilleries\Contentful\Models\Asset|null
     */
    protected function contentfulAsset($assetId) : ?Asset
    {
        if (empty($assetId)) {
            return null;
        }

        $asset = (new Asset)->query()
            ->where('contentful_id', '=', $assetId)
            ->where('locale', '=', $this->locale)
            ->first();

        return ! empty($asset) ? $asset : null;
    }

    /**
     * Return Contentful Entry for given ID.
     *
     * @param  string|null  $entryId
     * @return \Distilleries\Contentful\Models\Base\ContentfulModel|null
     */
    protected function contentfulEntry($entryId) : ?ContentfulModel
    {
        if (empty($entryId)) {
            return null;
        }

        $entries = $this->contentfulEntries([$entryId]);

        return $entries->isNotEmpty() ? $entries->first() : null;
    }

    /**
     * Return Contentful Entries for given ID.
     *
     * @param  array  $entryIds
     * @return \Illuminate\Support\Collection
     */
    protected function contentfulEntries(array $entryIds) : Collection
    {
        $entries = [];

        $relationships = DB::table('entry_relationships')
            ->select('related_contentful_id', 'related_contentful_type')
            ->where('locale', '=', $this->locale)
            ->where('source_contentful_id', '=', $this->contentful_id)
            ->whereIn('related_contentful_id', $entryIds)
            ->orderBy('order', 'asc')
            ->get();

        foreach ($relationships as $relationship) {
            if ($relationship->related_contentful_type === 'asset') {
                $model = new Asset;
            } else {
                $modelClass = '\App\Models\\' . studly_case($relationship->related_contentful_type);
                $model = new $modelClass;
            }

            $instance = $model->query()
                ->where('locale', '=', $this->locale)
                ->where('contentful_id', '=', $relationship->related_contentful_id)
                ->first();

            if (! empty($instance)) {
                $entries[] = $instance;
            }
        }

        return collect($entries);
    }

    /**
     * Return a collection of related models for base Contentful ID.
     *
     * @param  string  $contentfulId
     * @param  string  $contentfulType
     * @return \Illuminate\Support\Collection
     */
    protected function contentfulRelatedEntries(string $contentfulId, string $contentfulType = '') : Collection
    {
        $entries = [];

        $query = DB::table('entry_relationships')
            ->select('source_contentful_id', 'source_contentful_type')
            ->where('locale', '=', $this->locale)
            ->where('related_contentful_id', '=', $contentfulId);

        if (! empty($contentfulType)) {
            $query = $query->where('source_contentful_type', '=', $contentfulType);
        }

        $relationships = $query->orderBy('order', 'asc')->get();
        foreach ($relationships as $relationship) {
            if ($relationship->source_contentful_type === 'asset') {
                $model = new Asset;
            } else {
                $modelClass = '\App\Models\\' . studly_case($relationship->source_contentful_type);
                $model = new $modelClass;
            }

            $instance = $model->query()
                ->where('locale', '=', $this->locale)
                ->where('contentful_id', '=', $relationship->source_contentful_id)
                ->first();

            if (! empty($instance)) {
                $entries[] = $instance;
            }
        }

        return collect($entries);
    }

    /**
     * Decode Contentful JSON data.
     *
     * @param  string  $str
     * @return array
     */
    protected function contentfulJson(string $str) : array
    {
        return json_decode($str, true);
    }
}
