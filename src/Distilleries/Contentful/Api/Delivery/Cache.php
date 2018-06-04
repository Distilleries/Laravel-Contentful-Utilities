<?php

namespace Distilleries\Contentful\Api\Delivery;

use Distilleries\Contentful\Api\BaseApi;
use Illuminate\Contracts\Cache\Repository as CacheManager;

class Cache extends BaseApi implements Api
{
    /**
     * Cache manager implementation.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * DeliveryApi with cache constructor.
     *
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     * @return void
     */
    public function __construct(CacheManager $cache)
    {
        parent::__construct();

        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function entries($parameters = [])
    {
        // @TODO...
    }

    /**
     * {@inheritdoc}
     */
    public function assets($parameters = [])
    {
        // @TODO...
    }

    /**
     * {@inheritdoc}
     */
    public function entry($entryId, $locale = '')
    {
        // @TODO...
    }

    /**
     * {@inheritdoc}
     */
    public function asset($assetId, $locale = '')
    {
        // @TODO...
    }
}
