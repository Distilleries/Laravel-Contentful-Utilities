<?php

namespace Distilleries\Contentful\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $fallback_code
 * @property boolean $is_editable
 * @property boolean $is_publishable
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Locale extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'locales';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
        'code',
        'fallback_code',
        'is_editable',
        'is_publishable',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'is_editable' => 'boolean',
        'is_publishable' => 'boolean',
    ];
}