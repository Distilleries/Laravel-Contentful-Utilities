<?php

namespace Distilleries\Contentful\Api;

interface DeliveryApi
{
    /**
     * Return the "/entries" raw response matching given parameters.
     *
     * @param  array  $parameters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function entries(array $parameters = []) : array;

    /**
     * Return the "/assets" raw response matching given parameters.
     *
     * @param  array  $parameters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function assets(array $parameters = []) : array;
}
