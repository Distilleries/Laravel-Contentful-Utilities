<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

class LocationDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    public function modelGetter()
    {
        $stubPath = __DIR__ . '/stubs/location.stub';

        return self::getStub($stubPath, [
            'field' => $this->id(),
            'field_studly' => $this->studlyId(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function modelProperty()
    {
        return ' * @property \Distilleries\Contentful\Models\Location $' . $this->snakeId();
    }
}
