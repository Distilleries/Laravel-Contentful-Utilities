<?php

namespace Distilleries\Contentful\Contentful\Commands\Definitions;

class LocationDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    protected function migrationType()
    {
        return [
            "float('latitude', 10, 6)",
            "float('longitude', 10, 6)",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelProperties()
    {
        return [
            "float \$latitude",
            "float \$longitude",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelFillable()
    {
        return [
            'latitude',
            'longitude',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelCast()
    {
        return [
            ['key' => 'latitude', 'type' => 'float'],
            ['key' => 'longitude', 'type' => 'float'],
        ];
    }
}
