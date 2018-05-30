<?php

namespace Distilleries\Contentful\Contentful\Commands\Definitions;

class ObjectDefinition extends BaseDefinition
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

    /**
     * {@inheritdoc}
     */
    public function modelProperties()
    {
        return [
            "array \$" . $this->id(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelCast()
    {
        return [
            ['key' => $this->id(), 'type' => 'array'],
        ];
    }
}
