<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

class DateDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    public function modelGetter()
    {
        $stubPath = __DIR__ . '/stubs/datetime.stub';

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
        return ' * @property \Illuminate\Support\Carbon $' . $this->snakeId();
    }
}
