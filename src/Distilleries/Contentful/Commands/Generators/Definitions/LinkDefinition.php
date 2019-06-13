<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

use Exception;

class LinkDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    public function modelGetter()
    {
        switch ($this->field['linkType']) {
            case 'Entry':
                $stubPath = __DIR__ . '/stubs/entry.stub';
                break;
            case 'Asset':
                $stubPath = __DIR__ . '/stubs/asset.stub';
                break;
            default:
                throw new Exception('Unknown Link items type "' . $this->field['linkType'] . '"');
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

        switch ($this->field['linkType']) {
            case 'Entry':
                $property .= '\Distilleries\Contentful\Models\Base\ContentfulModel';
                break;
            case 'Asset':
                $property .= '\Distilleries\Contentful\Models\Asset';
                break;
            default:
                throw new Exception('Unknown Link items type "' . $this->field['items']['type'] . '"');
        }

        return $property . ' $' . $this->attribute();
    }
}
