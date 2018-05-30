<?php

namespace Distilleries\Contentful\Contentful\Webhook;

use App\Models\Asset;
use App\Models\Contentful\Mappers\AssetMapper;

class AssetHandler
{
    /**
     * Handle an incoming ContentManagementAsset request.
     * create, save, auto_save, archive, unarchive, publish, unpublish, delete
     *
     * @param  string  $action
     * @param  array  æ$payload
     * @return void
     */
    public function handle($action, $payload)
    {
        if (method_exists($this, $action)) {
            $this->$action($payload);
        }
    }

    /**
     * Create asset.
     *
     * @param  array  $payload
     * @return void
     */
    protected function create($payload)
    {
        $this->upsert($payload);
    }

    /**
     * Save entry.
     *
     * @param  array  $payload
     * @return void
     */
    protected function auto_save($payload)
    {
        //
    }

    /**
     * Save entry.
     *
     * @param  array  $payload
     * @return void
     */
    protected function save($payload)
    {
        //
    }

    /**
     * Archive asset.
     *
     * @param  array  $payload
     * @return void
     */
    protected function archive($payload)
    {
        $this->delete($payload);
    }

    /**
     * Un-archive asset.
     *
     * @param  array  $payload
     * @return void
     */
    protected function unarchive($payload)
    {
        $this->upsert($payload);
    }

    /**
     * Publish asset.
     *
     * @param  array  $payload
     * @return void
     */
    protected function publish($payload)
    {
        $this->upsert($payload);
    }

    /**
     * Un-publish asset.
     *
     * @param  array  $payload
     * @return void
     */
    protected function unpublish($payload)
    {
        $this->delete($payload);
    }

    /**
     * Delete asset.
     *
     * @param  array  $payload
     * @return void
     */
    protected function delete($payload)
    {
        Asset::query()->where('contentful_id', '=', $payload['sys']['id'])->delete();
    }

    /**
     * Return asset for given payload.
     *
     * @param  array  $payload
     * @return \App\Models\Asset
     */
    private function upsert($payload)
    {
        $map = (new AssetMapper)->map($payload);

        $asset = Asset::query()->where('contentful_id', '=', $payload['sys']['id'])->first();
        if (empty($asset)) {
            $asset = (new Asset)->forceFill($map);
        } else {
            foreach ($map['fields'] as $field => $value) {
                $asset->$field = $value;
            }
        }
        $asset->save();

        return $asset;
    }
}
