<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

class TextDefinition extends SymbolDefinition
{
    /**
     * {@inheritdoc}
     */
    protected function migrationType()
    {
        return [
            "text('" . $this->id() . "')",
        ];
    }
}
