<?php

namespace Distilleries\Contentful\Models\Base;

use Distilleries\Contentful\Helpers\NamespaceResolver;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Distilleries\Contentful\Models\Asset;
use Distilleries\Contentful\Models\Traits\Localable;
use Distilleries\Contentful\Models\EntryRelationship;

abstract class ContentfulModel extends Model
{
    use Localable;

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'contentful_id';

    /**
     * The content-type ID.
     *
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
     */
    public function __construct(array $attributes = [])
    {
        // Override fillable
        foreach ($this->defaultFillable() as $defaultFillable) {
            if (!in_array($defaultFillable, $this->fillable)) {
                $this->fillable[] = $defaultFillable;
            }
        }

        // Override casts
        foreach ($this->defaultCasts() as $field => $type) {
            if (!isset($this->casts[$field])) {
                $this->casts[$field] = $type;
            }
        }

        $this->initContentType();

        parent::__construct($attributes);
    }

    /**
     * Init model content-type if needed.
     *
     * @return void
     */
    protected function initContentType()
    {
        if (empty($this->contentType)) {
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

    protected function getAndSetPayloadContentfulAsset(string $payload, $link, $query = null): ?Asset
    {
        if (!isset($this->attributes[$payload]) && isset($this->payload[$payload])) {
            $this->attributes[$payload] = $this->contentfulAsset($link, $query);
            return $this->attributes[$payload];
        } else {
            if (isset($this->attributes[$payload])) {
                return $this->attributes[$payload];
            } else {
                return null;
            }
        }
    }


    /**
     * Return Contentful Asset for given link (sys or ID).
     *
     * @param  array|string|null $link
     * @param  callback|null $query
     * @return \Distilleries\Contentful\Models\Asset|null
     */
    protected function contentfulAsset($link, $query = null): ?Asset
    {
        $assetId = $this->contentfulLinkId($link);

        if (empty($assetId)) {
            return null;
        }

        $instance = (new Asset)->query()
            ->where('contentful_id', '=', $assetId)
            ->where('locale', '=', $this->locale)
            ->where('country', '=', $this->country);

        if (!empty($query)) {
            $instance = call_user_func($query, $instance);
        }

        $asset = $instance->first();

        return !empty($asset) ? $asset : null;
    }

    /**
     * Return payload of related Contentful entries.
     *
     * @param  string $payload
     * @param  array $links
     * @param  mixed $query
     * @return \Illuminate\Support\Collection
     */
    protected function getAndSetPayloadContentfulEntries(string $payload, array $links, $query = null): Collection
    {
        if (!isset($this->attributes[$payload]) && isset($this->payload[$payload])) {
            $this->attributes[$payload] = $this->contentfulEntries($links, $query);
            return $this->attributes[$payload];
        } else {
            if (isset($this->attributes[$payload])) {
                return $this->attributes[$payload];
            } else {
                return collect();
            }
        }
    }

    /**
     * Return payload of related Contentful entry.
     *
     * @param  string $payload
     * @param  array $links
     * @param  mixed $query
     * @return \Distilleries\Contentful\Models\Base\ContentfulModel|null
     */
    protected function getAndSetPayloadContentfulEntry(string $payload, array $links, $query = null): ?ContentfulModel
    {
        if (!isset($this->attributes[$payload]) && isset($this->payload[$payload])) {
            $this->attributes[$payload] = $this->contentfulEntry($links, $query);
            return $this->attributes[$payload];
        } else {
            if (isset($this->attributes[$payload])) {
                return $this->attributes[$payload];
            } else {
                return null;
            }
        }
    }

    /**
     * Return Contentful Entry for given link (sys or ID).
     *
     * @param  array|string|null $link
     * @param  mixed $query
     * @return \Distilleries\Contentful\Models\Base\ContentfulModel|null
     */
    protected function contentfulEntry($link, $query = null): ?ContentfulModel
    {
        $entryId = $this->contentfulLinkId($link);

        if (empty($entryId)) {
            return null;
        }

        $entries = $this->contentfulEntries([$entryId], $query);

        return $entries->isNotEmpty() ? $entries->first() : null;
    }

    /**
     * Return Contentful Entries for given ID.
     *
     * @param  array $links
     * @param  mixed $query
     * @return \Illuminate\Support\Collection
     */
    protected function contentfulEntries(array $links, $query = null): Collection
    {
        $entries = [];

        $entryIds = [];
        foreach ($links as $link) {
            $entryId = $this->contentfulLinkId($link);
            if (!empty($entryId)) {
                $entryIds[] = $entryId;
            }
        }

        if (!empty($entryIds)) {
            $relationships = EntryRelationship::select('related_contentful_id', 'related_contentful_type')
                ->distinct()
                ->locale($this->locale, $this->country)
                ->where('source_contentful_id', '=', $this->contentful_id)
                ->whereIn('related_contentful_id', $entryIds)
                ->where('relation_type', $link)
                ->orderBy('order', 'asc')
                ->get();

            foreach ($relationships as $relationship) {
                if ($relationship->related_contentful_type === 'asset') {
                    $model = new Asset;
                } else {
                    $model = NamespaceResolver::model($relationship->related_contentful_type);
                }

                if (!empty($model)) {
                    $instance = $model->query()
                        ->where('country', '=', $this->country)
                        ->where('locale', '=', $this->locale)
                        ->where('contentful_id', '=', $relationship->related_contentful_id);

                    if (!empty($query)) {
                        $instance = call_user_func($query, $instance);
                    }

                    $instance = $instance->first();

                }

                if (!empty($instance)) {
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

        $query = EntryRelationship::select('source_contentful_id', 'source_contentful_type')
            ->locale($this->locale, $this->country)
            ->where('related_contentful_id', '=', $contentfulId);

        if (!empty($contentfulType)) {
            $query = $query->where('source_contentful_type', '=', $contentfulType);
        }

        $relationships = $query->orderBy('order', 'asc')->get();
        foreach ($relationships as $relationship) {
            if ($relationship->source_contentful_type === 'asset') {
                $model = new Asset;
            } else {
                $model = NamespaceResolver::model($relationship->related_contentful_type);
            }

            $instance = !empty($model) ? $model->query()
                ->locale($this->locale, $this->country)
                ->where('contentful_id', '=', $relationship->source_contentful_id)
                ->first() : null;

            if (!empty($instance)) {
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
        if (empty($link)) {
            return null;
        }

        if (is_string($link)) {
            return $link;
        }

        if (is_array($link) && isset($link['sys']) && isset($link['sys']['id'])) {
            return $link['sys']['id'];
        }

        return null;
    }

    /**
     * Return model Contentful content-type.
     *
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Return ID attribute.
     *
     * @return mixed
     */
    public function getIdAttribute()
    {
        return $this->getKey();
    }

    /**
     * Magical extended toArray().
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        foreach ($this->getMutatedAttributes() as $key) {
            if (!array_key_exists($key, $array)) {
                $array[$key] = $this->{$key};
            }
        }

        return $array;
    }
}
