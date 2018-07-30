<?php

namespace Distilleries\Contentful\Api\Delivery;

use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Distilleries\Contentful\Api\BaseApi;
use Distilleries\Contentful\Api\DeliveryApi;

class Live extends BaseApi implements DeliveryApi
{
    /**
     * {@inheritdoc}
     */
    protected $baseUrl = 'https://cdn.contentful.com';

    /**
     * Preview base URL API.
     *
     * @var string
     */
    protected $previewBaseUrl = 'https://preview.contentful.com';

    /**
     * {@inheritdoc}
     */
    public function entries(array $parameters = []): array
    {
        $response = $this->query('entries', $parameters);

        return $this->decodeResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function assets(array $parameters = []): array
    {
        $response = $this->query('assets', $parameters);

        return $this->decodeResponse($response);
    }

    /**
     * Return a raw items response matching given endpoint and parameters.
     *
     * @param  string  $endpoint
     * @param  array  $parameters
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function query(string $endpoint, array $parameters): ResponseInterface
    {
        $token = ! empty($this->config['use_preview']) ? 'preview' : 'live';

        $url = isset($parameters['id']) ? $this->url($endpoint) . '/' . $parameters['id'] : $this->url($endpoint);
        unset($parameters['id']);

        return $this->client->request('GET', $url, [
            RequestOptions::QUERY => array_merge($parameters, [
                'access_token' => $this->config['tokens']['delivery'][$token],
            ]),
        ]);
    }
}
