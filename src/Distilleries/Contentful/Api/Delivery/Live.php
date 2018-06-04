<?php

namespace Distilleries\Contentful\Api\Delivery;

use GuzzleHttp\RequestOptions;
use Distilleries\Contentful\Api\BaseApi;

class Live extends BaseApi implements Api
{
    /**
     * {@inheritdoc}
     */
    protected $baseUrl = 'https://cdn.contentful.com';

    /**
     * {@inheritdoc}
     */
    public function entries($parameters = [])
    {
        return $this->items('entries', $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function assets($parameters = [])
    {
        return $this->items('assets', $parameters);
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

    /**
     * Return a raw items response for given endpoint and parameters.
     *
     * @param  string  $endpoint
     * @param  array  $parameters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function items($endpoint, $parameters)
    {
        $response = $this->client->request('GET', $this->url($endpoint), [
            RequestOptions::QUERY => array_merge($parameters, [
                'access_token' => $this->config['api']['delivery']['token'],
            ]),
        ]);

        return $this->decodeResponse($response);
    }
}
