<?php

namespace Distilleries\Contentful\Contracts;

interface ModelMapper
{
    /**
     * Map data to model structure.
     *
     * @param  mixed  $data
     * @return array
     */
    public function map($data);
}
