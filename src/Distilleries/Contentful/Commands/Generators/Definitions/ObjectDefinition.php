<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

use Illuminate\Support\Str;

class ObjectDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    public function modelGetter()
    {
        $stubPath = __DIR__ . '/stubs/json.stub';

        return self::getStub($stubPath, [
            'field_camel' => Str::studly($this->id()),
            'field' => $this->id(),
        ]);
    }
}
