<?php

namespace Distilleries\Contentful\Api\Delivery;

interface Api
{
    /**
     * Return the "/entries" raw response corresponding to given parameters.
     *
     * @param  array  $parameters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function entries($parameters = []);

    /**
     * Return the "/assets" raw response corresponding to given parameters.
     *
     * @param  array  $parameters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function assets($parameters = []);

    /**
     * Call for a single entry for given entry ID.
     *
     * @param  string  $entryId
     * @param  string  $locale
     * @return array
     */
    public function entry($entryId, $locale = '');

    /**
     * Call for a single asset for given asset ID.
     *
     * @param  string  $assetId
     * @param  string  $locale
     * @return array
     */
    public function asset($assetId, $locale = '');
}
