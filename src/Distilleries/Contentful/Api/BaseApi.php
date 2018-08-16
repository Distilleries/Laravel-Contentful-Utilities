<?php

namespace Distilleries\Contentful\Api;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

abstract class BaseApi
{
    /**
     * HTTP client implementation.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * Contentful configuration.
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
     * API preview Base URL.
     *
     * @var string
     */
    protected $previewBaseUrl;

    /**
     * BaseApi constructor.
     *
     * @param  \GuzzleHttp\ClientInterface $client
     * @return void
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;

        $this->config = config('contentful', []);
    }

    /**
     * Return endpoint URL.
     *
     * @param  string $endpoint
     * @return string
     */
    protected function url($endpoint): string
    {
        $baseUrl = rtrim($this->baseUrl, '/');

        if ((isset($this->config['use_preview']) && $this->config['use_preview'] == true) && !empty($this->previewBaseUrl)) {
            $baseUrl = rtrim($this->previewBaseUrl, '/');
        }

        $environment = (isset($this->config['use_environment']) && $this->config['use_environment'] == true) ? '/environments/' . $this->config['environment'] . '/' : '/';
        return $baseUrl . '/spaces/' . $this->config['space_id'] . $environment . trim($endpoint, '/');
    }

    /**
     * Decode given response.
     *
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @return array
     */
    protected function decodeResponse(ResponseInterface $response): array
    {
        return json_decode($response->getBody()->getContents(), true);
    }
}