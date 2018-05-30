<?php

namespace Distilleries\Contentful\Contentful\Commands\Definitions;

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
