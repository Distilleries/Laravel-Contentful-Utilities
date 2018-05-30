<?php

namespace Distilleries\Contentful\Contentful\Commands\Definitions;

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
