<?php

namespace Distilleries\Contentful\Models;

use Illuminate\Database\Eloquent\Model;

class Release extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'releases';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'current',
    ];
}
