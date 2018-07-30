<?php

namespace Distilleries\Contentful\Webhook;

use Distilleries\Contentful\Models\Locale;
use Distilleries\Contentful\Repositories\AssetsRepository;

class AssetHandler
{
    /**
     * Assets repository implementation.
     *
     * @var \Distilleries\Contentful\Repositories\AssetsRepository
     */
    protected $assets;

    /**
     * AssetHandler constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->assets = new AssetsRepository;
    }

    /**
     * Handle an incoming ContentManagementAsset request.
     * (create, save, auto_save, archive, unarchive, publish, unpublish, delete)
     *
     * @param  string  $action
     * @param  array  $payload
     * @param  boolean  $isPreview
     * @return void
     */
    public function handle(string $action, array $payload, bool $isPreview)
    {
        $actionMethods = ['create', 'archive', 'unarchive', 'publish', 'unpublish', 'delete'];
        $actionMethods = ! empty($isPreview) ? array_merge($actionMethods, ['save', 'auto_save']): $actionMethods;

        if (method_exists($this, $action) and in_array($action, $actionMethods)) {
            $this->$action($payload);
        }
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Auto-save asset.
     *
     * @param  array  $payload
     * @return void
     */
    private function auto_save($payload)
    {
        $this->upsertAsset($payload);
    }

    /**
     * Save asset.
     *
     * @param  array  $payload
     * @return void
     */
    private function save($payload)
    {
        $this->upsertAsset($payload);
    }

    /**
     * Create asset.
     *
     * @param  array  $payload
     * @return void
     */
    private function create($payload)
    {
        $this->upsertAsset($payload);
    }

    /**
     * Archive asset.
     *
     * @param  array  $payload
     * @return void
     */
    private function archive($payload)
    {
        $this->deleteAsset($payload);
    }

    /**
     * Un-archive asset.
     *
     * @param  array  $payload
     * @return void
     */
    private function unarchive($payload)
    {
        $this->upsertAsset($payload);
    }

    /**
     * Publish asset.
     *
     * @param  array  $payload
     * @return void
     */
    private function publish($payload)
    {
        $this->upsertAsset($payload);
    }

    /**
     * Un-publish asset.
     *
     * @param  array  $payload
     * @return void
     */
    private function unpublish($payload)
    {
        $this->deleteAsset($payload);
    }

    /**
     * Delete asset.
     *
     * @param  array  $payload
     * @return void
     */
    private function delete($payload)
    {
        $this->deleteAsset($payload);
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Upsert asset in DB.
     *
     * @param  array  $payload
     * @return void
     */

    private function upsertAsset($payload)
    {
        $this->assets->toContentfulModel($payload,Locale::all());
    }

    /**
     * Delete asset from DB.
     *
     * @param  array  $payload
     * @return void
     */
    private function deleteAsset($payload)
    {
        $this->assets->delete($payload['sys']['id']);
    }
}
