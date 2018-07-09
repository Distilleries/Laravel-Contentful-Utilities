<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

class BooleanDefinition extends BaseDefinition
{

    /**
     * {@inheritdoc}
     */
    public function modelGetter()
    {
        $stubPath = __DIR__ . '/stubs/boolean.stub';
        return self::getStub($stubPath, [
            'field_camel' => camel_case($this->id()),
            'field' => $this->id(),
        ]);
    }
}
