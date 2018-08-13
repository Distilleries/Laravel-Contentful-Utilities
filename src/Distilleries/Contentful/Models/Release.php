<?php

namespace Distilleries\Contentful\Models;

use Illuminate\Database\Eloquent\Model;

class Release extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'current',
    ];

}
