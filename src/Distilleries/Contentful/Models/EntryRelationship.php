<?php

namespace Distilleries\Contentful\Models;

use Distilleries\Contentful\Models\Base\ContentfulModel;

/**
 * @property string $locale
 * @property string $country
 * @property string $source_contentful_id
 * @property string $source_contentful_type
 * @property string $related_contentful_id
 * @property string $related_contentful_type
 * @property integer $order
 * @property string $relation
 */
class EntryRelationship extends ContentfulModel
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'entry_relationships';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = null;

    /**
     * {@inheritdoc}
     */
    public $incrementing = false;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'locale',
        'country',
        'source_contentful_id',
        'source_contentful_type',
        'related_contentful_id',
        'related_contentful_type',
        'order',
        'relation',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'order' => 'integer'
    ];

    /**
     * Get Contentful related entry.
     *
     * @return mixed
     */
    public function getRelatedEntry()
    {
        $localModel = rtrim(config('contentful.namespaces.model'), '\\') . '\\' . ucfirst($this->source_contentful_type);
        if (! class_exists($localModel)) {
            return null;
        }

        $model = (new $localModel);

        return $model->locale()->where($model->getKeyName(), '=', $this->source_contentful_type)->first();
    }
}
