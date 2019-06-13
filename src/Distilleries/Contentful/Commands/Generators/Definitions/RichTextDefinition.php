<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

class RichTextDefinition extends SymbolDefinition
{
    /**
     * {@inheritdoc}
     */
    public function modelGetter()
    {
        $stubPath = __DIR__ . '/stubs/string.stub';

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
        return ' * @property string $' . $this->attribute();
    }
}
