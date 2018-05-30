<?php

namespace Distilleries\Contentful\Contentful\Commands\Definitions;

class DateDefinition extends BaseDefinition
{
    /**
     * {@inheritdoc}
     */
    protected function migrationType()
    {
        return [
            "date('" . $this->id() . "')",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelProperties()
    {
        return [
            "\\Illuminate\\Support\\Carbon \$" . $this->id(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modelCast()
    {
        return [
            ['key' => $this->id(), 'type' => 'datetime'],
        ];
    }
}
