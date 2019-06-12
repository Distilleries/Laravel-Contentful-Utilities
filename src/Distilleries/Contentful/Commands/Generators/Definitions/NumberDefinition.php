<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

use Illuminate\Support\Str;

class NumberDefinition extends BaseDefinition
{

    /**
     * {@inheritdoc}
     */
    public function modelGetter()
    {
        $stubPath = __DIR__ . '/stubs/float.stub';

        return self::getStub($stubPath, [
            'field_camel' => Str::studly($this->id()),
            'field' => $this->id(),
        ]);
    }
}
