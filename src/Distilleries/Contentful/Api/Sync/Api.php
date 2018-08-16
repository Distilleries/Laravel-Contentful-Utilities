<?php

namespace Distilleries\Contentful\Api\Sync;

use GuzzleHttp\RequestOptions;
use Distilleries\Contentful\Api\BaseApi;
use Distilleries\Contentful\Api\SyncApi;

class Api extends BaseApi implements SyncApi
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
     * Sync token next URL parameter.
     *
     * @var string
     */
    protected $syncToken = '';

    /**
     * {@inheritdoc}
     */
    public function syncInitial(string $type = 'Entry'): array
    {
        $response = $this->client->request('GET', $this->url('sync'), [
            RequestOptions::QUERY => [
                'type' => $type,
                'initial' => true,
                'access_token' => $this->accessToken(),
            ],
        ]);

        $response = $this->decodeResponse($response);
        $this->setSyncToken($response);

        return $response['items'];
    }

    /**
     * {@inheritdoc}
     */
    public function syncNext(): array
    {
        if (empty($this->syncToken)) {
            return [];
        }

        $response = $this->client->request('GET', $this->url('sync'), [
            RequestOptions::QUERY => [
                'sync_token' => $this->syncToken,
                'access_token' => $this->accessToken(),
            ],
        ]);

        $response = $this->decodeResponse($response);
        $this->setSyncToken($response);

        return $response['items'];
    }

    /**
     * Return access token to use.
     *
     * @return string
     */
    private function accessToken(): string
    {
        $token = config('contentful.use_preview') ? 'preview' : 'live';

        return $this->config['tokens']['delivery'][$token];
    }

    /**
     * Parse sync token from response nextSyncUrl parameter.
     *
     * @param  array  $response
     * @return void
     */
    private function setSyncToken(array $response)
    {
        $this->syncToken = '';

        if (isset($response['nextPageUrl']) && ! empty($response['nextPageUrl'])) {
            $data = parse_url($response['nextPageUrl']);

            if (isset($data['query']) && ! empty($data['query'])) {
                parse_str($data['query'], $params);

                if (isset($params['sync_token'])) {
                    $this->syncToken = $params['sync_token'];
                }
            }
        }
    }
}
