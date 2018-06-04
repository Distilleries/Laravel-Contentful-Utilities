<?php

namespace Distilleries\Contentful\Api;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

abstract class BaseApi
{
    /**
     * HTTP client implementation.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * API configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * API base URL.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * BaseApi constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client([
            'verify' => false,
        ]);

        $this->config = config('contentful');
    }

    /**
     * Return endpoint URL.
     *
     * @param  string  $endpoint
     * @return string
     */
    protected function url($endpoint)
    {
        return rtrim($this->baseUrl, '/') . '/spaces/' . $this->config['api']['space'] . '/' . trim($endpoint, '/');
    }

    /**
     * Decode given response.
     *
     * @param  \Psr\Http\Message\ResponseInterface  $response
     * @return array
     */
    protected function decodeResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody()->getContents(), true);
    }
}
