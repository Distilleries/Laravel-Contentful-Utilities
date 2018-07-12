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
    protected $primaryKey = 'contentful_id';

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
            'country',
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
     * @param  string  $country
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLocale($query, string $locale = '',string $country='') : Builder
    {
        $locale = ! empty($locale) ? $locale : Locale::getAppOrDefaultLocale();
        $country = ! empty($country) ? $country : Locale::getAppOrDefaultCountry();

        return $query
            ->whereRaw('LOWER(country) LIKE LOWER("' . $country . '")')
            ->whereRaw('LOWER(locale) LIKE LOWER("' . $locale . '")');
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Return Contentful Asset for given link (sys or ID).
     *
     * @param  array|string|null  $link
     * @return \Distilleries\Contentful\Models\Asset|null
     */
    protected function contentfulAsset($link) : ?Asset
    {
        $assetId = $this->contentfulLinkId($link);

        if (empty($assetId)) {
            return null;
        }

        $asset = (new Asset)->query()
            ->where('contentful_id', '=', $assetId)
            ->where('locale', '=', $this->locale)
            ->where('country', '=', $this->country)
            ->first();

        return ! empty($asset) ? $asset : null;
    }

    /**
     * Return Contentful Entry for given link (sys or ID).
     *
     * @param  array|string|null  $link
     * @return \Distilleries\Contentful\Models\Base\ContentfulModel|null
     */
    protected function contentfulEntry($link) : ?ContentfulModel
    {
        $entryId = $this->contentfulLinkId($link);

        if (empty($entryId)) {
            return null;
        }

        $entries = $this->contentfulEntries([$entryId]);

        return $entries->isNotEmpty() ? $entries->first() : null;
    }

    /**
     * Return Contentful Entries for given ID.
     *
     * @param  array  $links
     * @return \Illuminate\Support\Collection
     */
    protected function contentfulEntries(array $links) : Collection
    {
        $entries = [];

        $entryIds = [];
        foreach ($links as $link) {
            $entryId = $this->contentfulLinkId($link);
            if (! empty($entryId)) {
                $entryIds[] = $entryId;
            }
        }

        if (! empty($entryIds)) {
            $relationships = DB::table('entry_relationships')
                ->select('related_contentful_id', 'related_contentful_type')
                ->distinct()
                ->where('country', '=', $this->country)
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
                    ->where('country', '=', $this->country)
                    ->where('locale', '=', $this->locale)
                    ->where('contentful_id', '=', $relationship->related_contentful_id)
                    ->first();

                if (! empty($instance)) {
                    $entries[] = $instance;
                }
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
            ->where('country', '=', $this->country)
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
                ->where('country', '=', $this->country)
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
     * Return Contentful link ID.
     *
     * @param  mixed  $link
     * @return string|null
     */
    protected function contentfulLinkId($link) : ?string
    {
        if (empty($link)) {
            return null;
        }

        if (is_string($link)) {
            return $link;
        }

        if (is_array($link) and isset($link['sys']) and isset($link['sys']['id'])) {
            return $link['sys']['id'];
        }

        return null;
    }
}
