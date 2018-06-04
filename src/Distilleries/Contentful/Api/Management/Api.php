<?php

namespace Distilleries\Contentful\Api\Management;

use Exception;
use GuzzleHttp\RequestOptions;
use Distilleries\Contentful\Api\BaseApi;

class Api extends BaseApi
{
    /**
     * {@inheritdoc}
     */
    protected $baseUrl = 'https://api.contentful.com';

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
            'Authorization' => 'Bearer ' . $this->config['api']['management']['token'],
        ], $headers);
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Return locales defined in Contentful.
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function locales()
    {
        $response = $this->client->request('GET', $this->url('locales'), [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Return data about content-types defined in Contentful.
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function contentTypes()
    {
        $response = $this->client->request('GET', $this->url('content_types'), [
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Return data about editor interface of given content-type.
     *
     * @param  string  $contentTypeId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function contentTypeEditorInterface($contentTypeId)
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
     * Return entries for given parameters.
     *
     * @param  array  $parameters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function entries($parameters = [])
    {
        $response = $this->client->request('GET', $this->url('entries'), [
            RequestOptions::QUERY => $parameters,
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    /**
     * Create given entry of specified content-type.
     *
     * @param  string  $contentType
     * @param  array  $entry
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createEntry($contentType, $entry)
    {
        $response = $this->client->request('POST', $this->url('entries'), [
            RequestOptions::BODY => json_encode($entry),
            RequestOptions::HEADERS => $this->headers([
                'X-Contentful-Content-Type' => $contentType,
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function publishEntry($entryId, $version = 1)
    {
        $response = $this->client->request('PUT', $this->url('entries/' . $entryId . '/published'), [
            RequestOptions::HEADERS => $this->headers([
                'X-Contentful-Version' => $version,
            ]),
        ]);

        return $this->decodeResponse($response);
    }

    // @TODO...
    public function unPublishEntry($entryId)
    {
        throw new Exception('Must implements unPublishEntry()');
    }

    // @TODO...
    public function deleteEntry($entryId)
    {
        throw new Exception('Must implements deleteEntry()');
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Return assets for given parameters.
     *
     * @param  array  $parameters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function assets($parameters = [])
    {
        $response = $this->client->request('GET', $this->url('assets'), [
            RequestOptions::QUERY => $parameters,
            RequestOptions::HEADERS => $this->headers(),
        ]);

        return $this->decodeResponse($response);
    }

    // @TODO...
    public function createAsset($asset)
    {
        throw new Exception('Must implements createAsset()');
    }

    // @TODO...
    public function processAsset($assetId, $locale)
    {
        throw new Exception('Must implements processAsset()');
    }

    // @TODO...
    public function publishAsset($assetId, $version = 1)
    {
        throw new Exception('Must implements publishAsset()');
    }

    // @TODO...
    public function unPublishAsset($assetId)
    {
        throw new Exception('Must implements unPublishAsset()');
    }

    // @TODO...
    public function deleteAsset($assetId)
    {
        throw new Exception('Must implements deleteAsset()');
    }
}
