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
            'field' => $this->id(),
            'field_studly' => $this->studlyId(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function modelProperty()
    {
        return ' * @property bool $' . $this->attribute();
    }
}
