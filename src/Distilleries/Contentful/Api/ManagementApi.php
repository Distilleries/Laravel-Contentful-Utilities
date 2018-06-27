<?php

namespace Distilleries\Contentful\Api;

interface ManagementApi
{
    /**
     * Return locales defined in configured Contentful space.
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function locales() : array;

    /**
     * Return content-types defined in Contentful space.
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function contentTypes() : array;

    /**
     * Return editor interface for given content-type.
     *
     * @param  string  $contentTypeId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function contentTypeEditorInterface(string $contentTypeId) : array;

    /**
     * Return entries matching given parameters.
     *
     * @param  array  $parameters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function entries(array $parameters = []) : array;

    /**
     * Return a single entry for given filter fields.
     *
     * @param  string  $contentType
     * @param  array  $fields
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function entry(string $contentType, array $fields) : ?array;

    /**
     * Create entry of specified content-type with given fields data.
     *
     * @param  string  $contentType
     * @param  array  $fields
     * @param  array  $sys
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createEntry(string $contentType, array $fields, array $sys = []) : array;

    /**
     * Publish given entry.
     *
     * @param  string  $entryId
     * @param  integer  $version
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function publishEntry(string $entryId, int $version = 1) : array;

    /**
     * Unpublish given entry.
     *
     * @param  string  $entryId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function unpublishEntry(string $entryId) : array;

    /**
     * Delete given entry.
     *
     * @param  string  $entryId
     * @return boolean
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteEntry(string $entryId) : bool;

    /**
     * Return assets matching given parameters.
     *
     * @param  array  $parameters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function assets(array $parameters = []) : array;

    /**
     * Create given asset.
     *
     * @param  array  $fields
     * @param  array  $sys
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createAsset(array $fields, array $sys = []) : array;

    /**
     * Process asset for given locale.
     *
     * @param  string  $assetId
     * @param  string  $locale
     * @param  integer  $version
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function processAsset(string $assetId, string $locale, int $version = 1);

    /**
     * Publish given asset.
     *
     * @param  string  $assetId
     * @param  integer  $version
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function publishAsset(string $assetId, int $version = 1) : array;

    /**
     * Unpublish given asset.
     *
     * @param  string  $assetId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function unpublishAsset(string $assetId) : array;

    /**
     * Delete given asset.
     *
     * @param  string  $assetId
     * @return boolean
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteAsset(string $assetId) : bool;
}
