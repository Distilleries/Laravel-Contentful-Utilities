<?php

namespace Distilleries\Contentful\Commands\Generators\Definitions;

interface DefinitionInterface
{
    /**
     * Return the getters functions
     *
     * @return string
     */
    public function modelGetter();


}