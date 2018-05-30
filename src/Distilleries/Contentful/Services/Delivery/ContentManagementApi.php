<?php

namespace Distilleries\Contentful\Services\Contentful;

use GuzzleHttp\RequestOptions;
use Distilleries\Contentful\Services\Contentful\Domain\Asset;
use Distilleries\Contentful\Services\Contentful\Domain\Entry;

class ContentManagementApi extends AbstractApi
{
    /**
     * API base URL.
     *
     * @var string
     */
    const BASE_URL = 'https://api.contentful.com';

    /**
     * Return a single entry.
     *
     * @param  string  $entryId
     * @return array
     */
    public function entry($entryId)
    {
        $response = $this->client->request('GET', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/entries/' . $entryId, [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Return all entries in space.
     *
     * @param  array  $parameters
     * @return array
     */
    public function entries($parameters = [])
    {
        $response = $this->client->request('GET', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/entries', [
            RequestOptions::QUERY => $parameters,
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Create given entry of specified content-type.
     *
     * @param  string  $contentType
     * @param  \Distilleries\Contentful\Services\Contentful\Domain\Entry  $entry
     * @return array
     */
    public function createEntry($contentType, Entry $entry)
    {
        $response = $this->client->request('POST', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/entries', [
            RequestOptions::BODY => json_encode([
                'fields' => $entry->fields,
                'sys' => $entry->sys,
            ]),
            RequestOptions::HEADERS => $this->headers([
                'X-Contentful-Content-Type' => $contentType,
            ]),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Update given entry with given data.
     *
     * @param  array  $entry
     * @return array
     */
    public function updateEntry($entry)
    {
        $response = $this->client->request('PUT', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/entries/' . $entry['sys']['id'], [
            RequestOptions::BODY => json_encode($entry),
            RequestOptions::HEADERS => $this->headers([
                'X-Contentful-Version' => $entry['sys']['version'],
            ]),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Publish given entry.
     *
     * @param  string  $entryId
     * @param  integer  $version
     * @return array
     */
    public function publishEntry($entryId, $version = 1)
    {
        $response = $this->client->request('PUT', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/entries/' . $entryId . '/published', [
            RequestOptions::HEADERS => $this->headers([
                'X-Contentful-Version' => $version,
            ]),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Un-publish given entry.
     *
     * @param  string  $entryId
     * @return array
     */
    public function unPublishEntry($entryId)
    {
        $response = $this->client->request('DELETE', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/entries/' . $entryId . '/published', [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Delete given entry.
     *
     * @param  string  $entryId
     * @return boolean
     */
    public function deleteEntry($entryId)
    {
        $response = $this->client->request('DELETE', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/entries/' . $entryId, [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $response->getStatusCode() === 204;
    }

    /**
     * Return a single asset.
     *
     * @param  string  $assetId
     * @return array
     */
    public function asset($assetId)
    {
        $response = $this->client->request('GET', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/assets/' . $assetId, [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Return all assets in space.
     *
     * @param  array  $parameters
     * @return array
     */
    public function assets($parameters = [])
    {
        $response = $this->client->request('GET', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/assets', [
            RequestOptions::QUERY => $parameters,
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Create given asset.
     *
     * @param  \Distilleries\Contentful\Services\Contentful\Domain\Asset  $asset
     * @return array
     */
    public function createAsset(Asset $asset)
    {
        $response = $this->client->request('POST', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/assets', [
            RequestOptions::BODY => json_encode([
                'fields' => $asset->fields,
                'sys' => $asset->sys,
            ]),
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Process asset for given locale.
     *
     * @param  string  $locale
     * @param  string  $assetId
     * @return void
     */
    public function processAsset($locale, $assetId)
    {
        $this->client->request('PUT', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/assets/' . $assetId . '/files/' . $locale . '/process', [
            RequestOptions::HEADERS => $this->headers(),
        ]);
    }

    /**
     * Publish given asset.
     *
     * @param  string  $assetId
     * @param  integer  $version
     * @return void
     */
    public function publishAsset($assetId, $version = 1)
    {
        $this->client->request('PUT', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/assets/' . $assetId . '/published', [
            RequestOptions::HEADERS => $this->headers([
                'X-Contentful-Version' => $version,
            ]),
        ]);
    }

    /**
     * Un-publish given asset.
     *
     * @param  string  $assetId
     * @return array
     */
    public function unPublishAsset($assetId)
    {
        $response = $this->client->request('DELETE', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/assets/' . $assetId . '/published', [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Delete given asset.
     *
     * @param  string  $assetId
     * @return boolean
     */
    public function deleteAsset($assetId)
    {
        $response = $this->client->request('DELETE', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/assets/' . $assetId, [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $response->getStatusCode() === 204;
    }

    /**
     * Return data about content-types defined in Contentful.
     *
     * @return array
     */
    public function contentTypes()
    {
        $response = $this->client->request('GET', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/content_types', [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Return data about editor interface of given content-type.
     *
     * @param  string  $contentTypeId
     * @return array
     */
    public function contentTypeEditorInterface($contentTypeId)
    {
        $response = $this->client->request('GET', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/content_types/' . $contentTypeId . '/editor_interface', [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Return data about locales defined in Contentful.
     *
     * @return array
     */
    public function locales()
    {
        $response = $this->client->request('GET', static::BASE_URL . '/spaces/' . $this->config['delivery.space'] . '/locales', [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Return default headers + given headers.
     *
     * @param  array  $headers
     * @return array
     */
    private function headers($headers = [])
    {
        return array_merge([
            'Content-Type' => 'application/vnd.contentful.management.v1+json',
            'Authorization' => 'Bearer ' . $this->config['management.token'],
        ], $headers);
    }
}
