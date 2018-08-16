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
        foreach ($this->fillable as $key => $value) {
            if (isset($attributes[$value])) {
                $this->{$value} = $attributes[$value];
            } else {
                $this->{$value} = null;
            }
        }

        return $this;
    }

}
