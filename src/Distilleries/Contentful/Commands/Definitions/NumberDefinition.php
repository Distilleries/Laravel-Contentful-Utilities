<?php

namespace Distilleries\Contentful\Contentful\Commands\Definitions;

class NumberDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    protected function migrationType()
    {
        return [
            "float('" . $this->id() . "', 10 ,6)",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelProperties()
    {
        return [
            "float \$" . $this->id(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelCast()
    {
        return [
            ['key' => $this->id(), 'type' => 'float'],
        ];
    }
}
