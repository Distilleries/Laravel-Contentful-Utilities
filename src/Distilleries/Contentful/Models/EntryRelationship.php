<?php

namespace Distilleries\Contentful\Models;

use Distilleries\Contentful\Models\Base\ContentfulModel;

/**
 * @property string $source_contentful_id
 * @property string $source_contentful_type
 * @property string $related_contentful_id
 * @property string $related_contentful_type
 * @property string $locale
 * @property string $country
 * @property integer $order
 * @property boolean $is_forbidden
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EntryRelationship extends ContentfulModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'entry_relationships';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = null;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var boolean
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'source_contentful_id',
        'source_contentful_type',
        'related_contentful_id',
        'related_contentful_type',
        'locale',
        'country',
        'order',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
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
        $localModel = rtrim(config('contentful.namespaces.model'), '\\') . '\\' . ucfirst($this->src_content_type);
        if (! class_exists($localModel)) {
            return null;
        }

        $model = (new $localModel);

        return $model
            ->locale()
            ->where($model->getKeyName(), '=', $this->src_contentful_id)
            ->first();
    }
}
