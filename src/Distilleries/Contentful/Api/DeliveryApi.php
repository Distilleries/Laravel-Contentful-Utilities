<?php

namespace Distilleries\Contentful\Api;

use GuzzleHttp\RequestOptions;

class DeliveryApi extends BaseApi
{
    /**
     * {@inheritdoc}
     */
    protected $baseUrl = 'https://cdn.contentful.com';

    /**
     * Return the "/entries" raw response corresponding to given parameters.
     *
     * @param  array  $parameters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function entries($parameters = [])
    {
        return $this->items('entries', $parameters);
    }

    /**
     * Return the "/assets" raw response corresponding to given parameters.
     *
     * @param  array  $parameters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function assets($parameters = [])
    {
        return $this->items('assets', $parameters);
    }

    /**
     * Return a raw items response for given endpoint and parameters.
     *
     * @param  string  $endpoint
     * @param  array  $parameters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function items($endpoint, $parameters)
    {
        $response = $this->client->request('GET', $this->url($endpoint), [
            RequestOptions::QUERY => array_merge($parameters, [
                'access_token' => $this->config['delivery_token'],
            ]),
        ]);

        return $this->decodeResponse($response);
    }
}
