<?php

namespace Distilleries\Contentful\Models;

use Distilleries\Contentful\Models\Traits\Localable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property string $contentful_id
 * @property string $locale
 * @property string $title
 * @property string $description
 * @property string $url
 * @property string $file_name
 * @property string $content_type
 * @property integer $size
 * @property integer $width
 * @property integer $height
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Asset extends Model
{
    use Localable;
    /**
     * {@inheritdoc}
     */
    protected $table = 'assets';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'contentful_id',
        'country',
        'locale',
        'title',
        'description',
        'url',
        'file_name',
        'content_type',
        'size',
        'width',
        'height',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];


    /**
     * Return asset URL.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

}
