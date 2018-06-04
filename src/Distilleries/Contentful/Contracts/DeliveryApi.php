<?php

namespace Distilleries\Contentful\Contracts;

use Illuminate\Cache\CacheManager;

interface DeliveryApi
{
    /**
     * DeliveryApi constructor.
     *
     * @param  \Illuminate\Cache\CacheManager  $cache
     * @param  array  $config
     * @return void
     */
    public function __construct(CacheManager $cache, $config);

    /**
     * Return entries for given parameters.
     *
     * @param  array  $parameters
     * @return array
     */
    public function entries($parameters = []);

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
