<?php

namespace Distilleries\Contentful\Models\Base;

use Distilleries\Contentful\Models\EntryRelationship;
use Distilleries\Contentful\Models\Traits\Localable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Distilleries\Contentful\Models\Asset;

abstract class ContentfulModel extends Model
{
    use Localable;

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'contentful_id';

    /**
     * The content Type id
     * @var string
     */
    protected $contentType = null;


    /**
     * {@inheritdoc}
     */
    protected $keyType = 'string';

    /**
     * {@inheritdoc}
     */
    public $incrementing = false;

    /**
     * ContentfulModel constructor.
     *
     * @param  array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        // Override fillable
        foreach ($this->defaultFillable() as $defaultFillable)
        {
            if (!in_array($defaultFillable, $this->fillable))
            {
                $this->fillable[] = $defaultFillable;
            }
        }

        // Override casts
        foreach ($this->defaultCasts() as $field => $type)
        {
            if (!isset($this->casts[$field]))
            {
                $this->casts[$field] = $type;
            }
        }

        $this->initContentType();

        parent::__construct($attributes);
    }

    protected function initContentType()
    {
        if (empty($this->contentType))
        {
            $this->contentType = lcfirst(class_basename(get_class($this)));
        }
    }

    /**
     * Return default fillable fields.
     *
     * @return array
     */
    public function defaultFillable(): array
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
    public function defaultCasts(): array
    {
        return [
            'payload' => 'array',
        ];
    }


    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Return Contentful Asset for given link (sys or ID).
     *
     * @param  array|string|null $link
     * @return \Distilleries\Contentful\Models\Asset|null
     */
    protected function contentfulAsset($link): ?Asset
    {
        $assetId = $this->contentfulLinkId($link);

        if (empty($assetId))
        {
            return null;
        }

        $asset = (new Asset)->query()
            ->where('contentful_id', '=', $assetId)
            ->where('locale', '=', $this->locale)
            ->where('country', '=', $this->country)
            ->first();

        return !empty($asset) ? $asset : null;
    }

    protected function getAndSetPayloadContentfulEntries(string $payload, array $links, $query = null): Collection
    {
        if (empty($this->payload[$payload]))
        {
            return collect();
        }

        if (!is_object($this->payload[$payload]))
        {
            $this->payload[$payload] = $this->contentfulEntries($links, $query);
        }

        return $this->payload[$payload];
    }

    protected function getAndSetPayloadContentfulEntry(string $payload, array $links, $query = null): ?ContentfulModel
    {
        if (empty($this->payload[$payload]))
        {
            return null;
        }

        if (!is_object($this->payload[$payload]))
        {
            $this->payload[$payload] = $this->contentfulEntry($links, $query);
        }

        return $this->payload[$payload];
    }

    /**
     * Return Contentful Entry for given link (sys or ID).
     *
     * @param  array|string|null $link
     * @param  callback|null $query
     * @return \Distilleries\Contentful\Models\Base\ContentfulModel|null
     */
    protected function contentfulEntry($link, $query = null): ?ContentfulModel
    {
        $entryId = $this->contentfulLinkId($link);

        if (empty($entryId))
        {
            return null;
        }

        $entries = $this->contentfulEntries([$entryId], $query);

        return $entries->isNotEmpty() ? $entries->first() : null;
    }

    /**
     * Return Contentful Entries for given ID.
     *
     * @param  array $links
     * @param  callback|null $query
     * @return \Illuminate\Support\Collection
     */
    protected function contentfulEntries(array $links, $query = null): Collection
    {
        $entries = [];

        $entryIds = [];
        foreach ($links as $link)
        {
            $entryId = $this->contentfulLinkId($link);
            if (!empty($entryId))
            {
                $entryIds[] = $entryId;
            }
        }

        if (!empty($entryIds))
        {
            $relationships = EntryRelationship::query()
                ->select('related_contentful_id', 'related_contentful_type', 'order')
                ->distinct()
                ->locale($this->locale, $this->country)
                ->where('source_contentful_id', '=', $this->contentful_id)
                ->whereIn('related_contentful_id', $entryIds)
                ->orderBy('order', 'asc')
                ->get();

            foreach ($relationships as $relationship)
            {
                if ($relationship->related_contentful_type === 'asset')
                {
                    $model = new Asset;
                } else
                {
                    $modelClass = config('contentful.namespace.model') . '\\' . studly_case($relationship->related_contentful_type);
                    $model = new $modelClass;
                }

                $instance = $model->query()
                    ->where('country', '=', $this->country)
                    ->where('locale', '=', $this->locale)
                    ->where('contentful_id', '=', $relationship->related_contentful_id);


                if (!empty($query))
                {
                    $instance = call_user_func($query, $instance);
                }

                $instance = $instance->first();

                if (!empty($instance))
                {
                    $entries[] = $instance;
                }
            }
        }

        return collect($entries);
    }


    /**
     * Return a collection of related models for base Contentful ID.
     *
     * @param  string $contentfulId
     * @param  string $contentfulType
     * @return \Illuminate\Support\Collection
     */
    protected function contentfulRelatedEntries(string $contentfulId, string $contentfulType = ''): Collection
    {
        $entries = [];

        $query = EntryRelationship::query()
            ->select('source_contentful_id', 'source_contentful_type', '')
            ->locale($this->locale, $this->country)
            ->where('related_contentful_id', '=', $contentfulId);

        if (!empty($contentfulType))
        {
            $query = $query->where('source_contentful_type', '=', $contentfulType);
        }

        $relationships = $query->orderBy('order', 'asc')->get();
        foreach ($relationships as $relationship)
        {
            if ($relationship->source_contentful_type === 'asset')
            {
                $model = new Asset;
            } else
            {
                $modelClass = rtrim(config('contentful.namespace.model'), '\\') . '\\' . studly_case($relationship->source_contentful_type);
                $model = new $modelClass;
            }

            $instance = $model->query()
                ->locale($this->locale, $this->country)
                ->where('contentful_id', '=', $relationship->source_contentful_id)
                ->first();

            if (!empty($instance))
            {
                $entries[] = $instance;
            }
        }

        return collect($entries);
    }

    /**
     * Return Contentful link ID.
     *
     * @param  mixed $link
     * @return string|null
     */
    protected function contentfulLinkId($link): ?string
    {
        if (empty($link))
        {
            return null;
        }

        if (is_string($link))
        {
            return $link;
        }

        if (is_array($link) and isset($link['sys']) and isset($link['sys']['id']))
        {
            return $link['sys']['id'];
        }

        return null;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getIdAttribute()
    {
        return $this->getKey();
    }

    public function toArray()
    {
        $array = parent::toArray();
        foreach ($this->getMutatedAttributes() as $key)
        {
            if (!array_key_exists($key, $array))
            {
                $array[$key] = $this->{$key};
            }
        }
        return $array;
    }

}
