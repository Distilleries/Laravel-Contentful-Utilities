<?php

namespace Distilleries\Contentful\Models;


/**
 * @property string $lon
 * @property string $lat
 */
class Location
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'lon',
        'lat'
    ];

}
