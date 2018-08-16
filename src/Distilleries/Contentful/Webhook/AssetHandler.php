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
     */
    public function __construct()
    {
        $this->assets = new AssetsRepository;
    }

    /**
     * Handle an incoming ContentManagementAsset request.
     * (create, save, auto_save, archive, unarchive, publish, unpublish, delete)
     *
     * @param  string $action
     * @param  array $payload
     * @param  boolean $isPreview
     * @return void
     */
    public function handle(string $action, array $payload, bool $isPreview)
    {
        $actionMethods = ['create', 'archive', 'unarchive', 'publish', 'unpublish', 'delete'];
        $actionMethods = !empty($isPreview) ? array_merge($actionMethods, ['save', 'auto_save']) : $actionMethods;

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
     * @param  array $payload
     * @return void
     */
    protected function auto_save($payload)
    {
        $this->upsertAsset($payload);
    }

    /**
     * Save asset.
     *
     * @param  array $payload
     * @return void
     */
    protected function save($payload)
    {
        $this->upsertAsset($payload);
    }

    /**
     * Create asset.
     *
     * @param  array $payload
     * @return void
     */
    protected function create($payload)
    {
        $this->upsertAsset($payload);
    }

    /**
     * Archive asset.
     *
     * @param  array $payload
     * @return void
     */
    protected function archive($payload)
    {
        $this->deleteAsset($payload);
    }

    /**
     * Un-archive asset.
     *
     * @param  array $payload
     * @return void
     */
    protected function unarchive($payload)
    {
        $this->upsertAsset($payload);
    }

    /**
     * Publish asset.
     *
     * @param  array $payload
     * @return void
     */
    protected function publish($payload)
    {
        $this->upsertAsset($payload);
    }

    /**
     * Un-publish asset.
     *
     * @param  array $payload
     * @return void
     */
    protected function unpublish($payload)
    {
        $this->deleteAsset($payload);
    }

    /**
     * Delete asset.
     *
     * @param  array $payload
     * @return void
     */
    protected function delete($payload)
    {
        $this->deleteAsset($payload);
    }

    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------
    // --------------------------------------------------------------------------------

    /**
     * Upsert asset in DB.
     *
     * @param  array $payload
     * @return void
     */

    protected function upsertAsset($payload)
    {
        $locales = Locale::all();
        $locales = is_array($locales) ? collect($locales) : $locales;
        $this->assets->toContentfulModel($payload, $locales);
    }

    /**
     * Delete asset from DB.
     *
     * @param  array $payload
     * @return void
     */
    protected function deleteAsset($payload)
    {
        $this->assets->delete($payload['sys']['id']);
    }
}
