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

    /**
     * @param  array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array $attributes
     * @return $this
     *
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

}
