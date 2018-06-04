<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

class SymbolDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    protected function migrationType()
    {
        return [
            "string('" . $this->id() . "')",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelProperties()
    {
        return [
            "string \$" . $this->id(),
        ];
    }
}
