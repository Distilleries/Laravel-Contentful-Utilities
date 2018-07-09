<?php

namespace Distilleries\Contentful\Models;

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
    /**
     * {@inheritdoc}
     */
    protected $table = 'assets';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'contentful_id',
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
     * Scope a query to a given locale.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $locale
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLocale($query, string $locale = '', string $country = ''): Builder
    {
        $locale = !empty($locale) ? $locale : Locale::default();

        return $query
            ->where($this->getTable() . '.locale', '=', $locale)
            ->where($this->getTable() . '.country', '=', $country);
    }
}
