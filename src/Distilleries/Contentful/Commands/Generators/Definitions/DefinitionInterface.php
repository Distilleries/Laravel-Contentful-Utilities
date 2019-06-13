<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

interface DefinitionInterface
{
    /**
     * Return a getter function.
     *
     * @return string
     * @throws \Exception
     */
    public function modelGetter();

    /**
     * Return a doc-block @property string.
     *
     * @return string
     * @throws \Exception
     */
    public function modelProperty();
}
