<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

use Exception;
use Illuminate\Support\Str;

class ArrayDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    public function modelGetter()
    {
        switch ($this->field['items']['type']) {
            case 'Link':
                $stubPath = __DIR__ . '/stubs/entries.stub';
                break;
            case 'Symbol':
                $stubPath = __DIR__ . '/stubs/string.stub';
                break;
            default:
                throw new Exception('Unknown Array items type "' . $this->field['items']['type'] . '"');
        }

        return self::getStub($stubPath, [
            'field_camel' => Str::studly($this->id()),
            'field' => $this->id(),
        ]);
    }
}
