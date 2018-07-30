<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

interface DefinitionInterface
{
    /**
     * Return the getters functions
     *
     * @return string
     * @throws \Exception
     */
    public function modelGetter();
}
