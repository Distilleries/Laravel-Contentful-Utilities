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
            'field_camel' => camel_case($this->id()),
            'field' => $this->id(),
        ]);
    }
}
