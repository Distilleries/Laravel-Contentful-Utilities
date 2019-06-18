<?php

namespace Distilleries\Contentful\Api\Management;

use GuzzleHttp\RequestOptions;
use Distilleries\Contentful\Api\BaseApi;
use Distilleries\Contentful\Api\ManagementApi;

class Api extends BaseApi implements ManagementApi
{
    /**
     * {@inheritdoc}
     */
    protected $baseUrl = 'https://api.contentful.com';

    /**
     * {@inheritdoc}
     */
    public function locales(): array
    {
        $response = $this->client->request('GET', $this->url('locales'), [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function contentTypes(): array
    {
        $response = $this->client->request('GET', $this->url('content_types'), [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function contentTypeEditorInterface(string $contentTypeId): array
    {
        $response = $this->client->request('GET', $this->url('content_types/' . $contentTypeId . '/editor_interface'), [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function entries(array $parameters = []): array
    {
        $response = $this->client->request('GET', $this->url('entries'), [
            RequestOptions::QUERY => $parameters,
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function entry(string $contentType, array $fields): ?array
    {
        $filters = [];
        $filters['content_type'] = $contentType;
        foreach ($fields as $field => $value) {
            $filters['fields.' . $field] = $value;
        }

        $response = $this->client->request('GET', $this->url('entries'), [
            RequestOptions::QUERY => $filters,
            RequestOptions::HEADERS => $this->headers(),
        ]);

        $results = $this->decodeResponse($response);

        return (isset($results['items']) && isset($results['items'][0]) && !empty($results['items'][0])) ? $results['items'][0] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function createEntry(string $contentType, array $fields, array $sys = []): array
    {
        $response = $this->client->request('POST', $this->url('entries'), [
            RequestOptions::BODY => json_encode([
                'sys' => $sys,
                'fields' => $fields,
            ]),
            RequestOptions::HEADERS => $this->headers([
                'X-Contentful-Content-Type' => $contentType,
            ]),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function publishEntry(string $entryId, int $version = 1): array
    {
        $response = $this->client->request('PUT', $this->url('entries/' . $entryId . '/published'), [
            RequestOptions::HEADERS => $this->headers([
                'X-Contentful-Version' => $version,
            ]),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function unpublishEntry(string $entryId): array
    {
        $response = $this->client->request('DELETE', $this->url('entries/' . $entryId . '/published'), [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEntry(string $entryId): bool
    {
        $response = $this->client->request('DELETE', $this->url('entries/' . $entryId), [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $response->getStatusCode() === 204;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function assets(array $parameters = []): array
    {
        $response = $this->client->request('GET', $this->url('assets'), [
            RequestOptions::QUERY => $parameters,
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function createAsset(array $fields, array $sys = []): array
    {
        $response = $this->client->request('POST', $this->url('assets'), [
            RequestOptions::BODY => json_encode([
                'fields' => $fields,
                'sys' => $sys,
            ]),
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function processAsset(string $assetId, string $locale, int $version = 1)
    {
        $this->client->request('PUT', $this->url('environments/master/assets/' . $assetId . '/files/' . $locale . '/process'), [
            RequestOptions::HEADERS => $this->headers([
                'X-Contentful-Version' => $version,
            ]),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function publishAsset(string $assetId, int $version = 1): array
    {
        $response = $this->client->request('PUT', $this->url('assets/' . $assetId . '/published'), [
            RequestOptions::HEADERS => $this->headers([
                'X-Contentful-Version' => $version,
            ]),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function unpublishAsset(string $assetId): array
    {
        $response = $this->client->request('DELETE', $this->url('assets/' . $assetId . '/published'), [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAsset(string $assetId): bool
    {
        $response = $this->client->request('DELETE', $this->url('assets/' . $assetId), [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $response->getStatusCode() === 204;
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Return default headers + given headers.
     *
     * @param  array  $headers
     * @return array
     */
    private function headers(array $headers = []): array
    {
        return array_merge([
            'Content-Type' => 'application/vnd.contentful.management.v1+json',
            'Authorization' => 'Bearer ' . $this->config['tokens']['management'],
        ], $headers);
    }
}
