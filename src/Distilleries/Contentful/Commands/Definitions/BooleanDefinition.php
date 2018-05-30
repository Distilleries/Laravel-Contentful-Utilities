<?php

namespace Distilleries\Contentful\Contentful\Commands\Definitions;

class BooleanDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    protected function migrationType()
    {
        return [
            "boolean('" . $this->id() . "')",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelProperties()
    {
        return [
            "boolean \$" . $this->id(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelCast()
    {
        return [
            ['key' => $this->id(), 'type' => 'boolean'],
        ];
    }
}
