<?php

namespace Distilleries\Contentful\Services\Contentful;

use Illuminate\Cache\CacheManager;

interface ContentDelivery
{
    /**
     * ContentDelivery constructor.
     *
     * @param  \Illuminate\Cache\CacheManager  $cache
     */
    public function __construct(CacheManager $cache,$config);

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


    public function asset($assetId, $locale = '');
}
