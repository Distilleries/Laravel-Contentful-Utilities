<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

use Exception;
use Illuminate\Support\Carbon;
use App\Eloquent;
use Distilleries\Contentful\Commands\Generators\Models as EloquentCommand;

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
            'field_camel' => studly_case($this->id()),
            'field' => $this->id(),
        ]);
    }

}
