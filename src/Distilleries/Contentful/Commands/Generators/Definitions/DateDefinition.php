<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

class DateDefinition extends BaseDefinition
{

    /**
     * {@inheritdoc}
     */
    public function modelGetter()
    {
        $stubPath = __DIR__ . '/stubs/datetime.stub';
        return self::getStub($stubPath, [
            'field_camel' => camel_case($this->id()),
            'field' => $this->id(),
        ]);
    }
}
