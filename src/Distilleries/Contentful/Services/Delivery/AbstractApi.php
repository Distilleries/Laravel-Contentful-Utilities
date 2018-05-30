<?php

namespace Distilleries\Contentful\Services;

use GuzzleHttp\Client;
use Illuminate\Cache\CacheManager;

abstract class AbstractApi
{
    /**
     * API base URL.
     *
     * @var string
     */
    const CDN_URL = 'https://cdn.contentful.com';

    /**
     * API base preview URL.
     *
     * @var string
     */
    const PREVIEW_URL = 'https://preview.contentful.com';

    /**
     * HTTP client implementation.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Contentful API configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Cache manager.
     *
     * @var \Illuminate\Cache\CacheManager
     */
    protected $cache;

    /**
     * Decode HTTP response.
     *
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @return array
     */
    protected function decodeResponse($response)
    {
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Parse and return result (from cache if status !== 20x).
     *
     * @param  string $action
     * @param  string $cacheKey
     * @param  mixed $result
     * @return mixed
     */
    protected function parseAndReturnResult($action, $cacheKey, $result)
    {
        $value = null;

        if (($result->getStatusCode() >= 300) or ($result->getStatusCode() <= 100) or !empty($this->config['force_cache'])) {
            $value = $this->getFromCache($action, $cacheKey);
        } else {
            $value = $this->decodeResponse($result);
            $this->setForeverCache($action, $cacheKey, $value);
        }

        return $value;
    }

    /**
     * Get data from cache system.
     *
     * @param  string $action
     * @param  string $cacheKey
     * @return \Illuminate\Contracts\Cache\Repository
     */
    protected function getFromCache($action, $cacheKey)
    {
        if ($this->cache->getStore() instanceof \Illuminate\Cache\TaggableStore) {
            $cache = $this->cache->tags(['models', $action])->get($cacheKey);
        } else {
            $cache = $this->cache->get($cacheKey);
        }

        return $cache;
    }

    /**
     * Set a "forever" cache with given cache key.
     *
     * @param  string $action
     * @param  string $cacheKey
     * @param  mixed $value
     * @return void
     */
    protected function setForeverCache($action, $cacheKey, $value)
    {
        if (config('cache.fallback_enabled', false)) {
            if ($this->cache->getStore() instanceof \Illuminate\Cache\TaggableStore) {
                $this->cache->tags(['models', $action])->forever($cacheKey, $value);
            } else {
                $this->cache->forever($cacheKey, $value);
            }
        }

    }

    /**
     * Return cache key for given parameters.
     *
     * @param  array $parameters
     * @param  string $prefix
     * @return string
     */
    protected function getCacheKey($parameters, $prefix = '')
    {
        $options = array_merge($parameters, [
            'access_token' => config('contentful.use_preview') ? $this->config['delivery.preview'] : $this->config['delivery.token'],
        ]);

        return md5($prefix . $this->getUri() . serialize($options));
    }

    /**
     * Get URI used for ContentDelivery API.
     *
     * @return string
     */
    protected function getUri()
    {
        return config('contentful.use_preview') ? static::PREVIEW_URL : static::CDN_URL;
    }
}
