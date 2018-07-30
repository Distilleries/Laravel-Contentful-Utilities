<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

class TextDefinition extends SymbolDefinition
{
    /**
     * {@inheritdoc}
     */
    public function modelGetter()
    {
        $stubPath = __DIR__ . '/stubs/string.stub';

        return self::getStub($stubPath, [
            'field_camel' => studly_case($this->id()),
            'field' => $this->id(),
        ]);
    }
}
