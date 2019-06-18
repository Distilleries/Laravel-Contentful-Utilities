<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

class NumberDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    public function modelGetter()
    {
        $stubPath = __DIR__ . '/stubs/float.stub';

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
        return ' * @property float $' . $this->snakeId();
    }
}
