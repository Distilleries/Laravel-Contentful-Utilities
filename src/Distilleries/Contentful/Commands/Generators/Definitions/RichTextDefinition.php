<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

use Illuminate\Support\Str;

class RichTextDefinition extends SymbolDefinition
{
    /**
     * {@inheritdoc}
     */
    public function modelGetter()
    {
        $stubPath = __DIR__ . '/stubs/string.stub';

        return self::getStub($stubPath, [
            'field_camel' => Str::studly($this->id()),
            'field' => $this->id(),
        ]);
    }
}
