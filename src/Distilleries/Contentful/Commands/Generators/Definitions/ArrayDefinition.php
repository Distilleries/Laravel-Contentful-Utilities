<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

use Exception;

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
            'field' => $this->id(),
            'field_studly' => $this->studlyId(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function modelProperty()
    {
        $property = ' * @property ';

        switch ($this->field['items']['type']) {
            case 'Link':
                $property .= '\Illuminate\Support\Collection';
                break;
            case 'Symbol':
                $property .= 'string';
                break;
            default:
                throw new Exception('Unknown Array items type "' . $this->field['items']['type'] . '"');
        }

        return $property . ' $' . $this->attribute();
    }
}
