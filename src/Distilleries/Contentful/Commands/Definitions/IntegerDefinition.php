<?php

namespace Distilleries\Contentful\Contentful\Commands\Definitions;

class IntegerDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    protected function migrationType()
    {
        return [
            "integer('" . $this->id() . "')->unsigned()",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelProperties()
    {
        return [
            "integer \$" . $this->id(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelCast()
    {
        return [
            ['key' => $this->id(), 'type' => 'integer'],
        ];
    }
}
