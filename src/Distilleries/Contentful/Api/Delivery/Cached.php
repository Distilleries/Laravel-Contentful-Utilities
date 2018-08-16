<?php

namespace Distilleries\Contentful\Api\Delivery;

use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\ResponseInterface;
use Distilleries\Contentful\Api\DeliveryApi;

class Cached extends Live implements DeliveryApi
{
    /**
     * {@inheritdoc}
     */
    public function entries(array $parameters = []): array
    {
        $keyCache = $this->keyCache($parameters);

        $response = $this->query('entries', $parameters);

        return $this->handleResponse($response, $keyCache);
    }

    /**
     * {@inheritdoc}
     */
    public function assets(array $parameters = []): array
    {
        $keyCache = $this->keyCache($parameters);

        $response = $this->query('assets', $parameters);

        return $this->handleResponse($response, $keyCache);
    }

    /**
     * Return key cache for given parameters.
     *
     * @param  array  $parameters
     * @return string
     */
    private function keyCache(array $parameters): string
    {
        return 'delivery_api_' . (config('contentful.use_preview') ? 'preview_' : '') . md5(json_encode($parameters));
    }

    /**
     * Use cached response if status code is not in 2xx OR fetch new one and store it in cache.
     *
     * @param  \Psr\Http\Message\ResponseInterface  $response
     * @param  string  $keyCache
     * @return array
     */
    private function handleResponse(ResponseInterface $response, string $keyCache): array
    {
        if (($response->getStatusCode() >= 300) || ($response->getStatusCode() <= 100)) {
            $data = Cache::get($keyCache);
        } else {
            $data = $this->decodeResponse($response);
            Cache::forever($keyCache, $data);
        }

        return ! empty($data) ? $data : [];
    }
}
