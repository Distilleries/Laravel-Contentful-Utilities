<?php

namespace Distilleries\Contentful\Models\Transformers;

use Illuminate\Support\Collection;
use Distilleries\Contentful\Models\Asset;
use Distilleries\Contentful\Helpers\Image;

/**
 * @OAS\Schema(schema="Asset", type="object",
 *   @OAS\Property(property="title", type="string", description="Asset title"),
 *   @OAS\Property(property="description", type="string", description="Asset description"),
 *   @OAS\Property(property="url", type="string", description="Asset URL")
 * )
 */
class AssetTransformer
{
    /**
     * Transform given asset.
     *
     * @param  \Distilleries\Contentful\Models\Asset  $model
     * @param  array  $parameters
     * @return array
     */
    public function transform(Asset $model, array $parameters = []) : array
    {
        if (starts_with($model->content_type, 'image/')) {
            $width = 0;
            if (isset($parameters['width'])) {
                $width = (int) $parameters['width'];
            }

            $height = 0;
            if (isset($parameters['height'])) {
                $height = (int) $parameters['height'];
            }

            $fit = '';
            if (isset($parameters['fit'])) {
                $fit = trim($parameters['fit']);
            }

            $url = Image::url($model->url, $width, $height, '', 0, null, $fit);
        } else {
            $url = $model->url;
        }

        return [
            'title' => ! empty($model->title) ? $model->title : null,
            'description' => ! empty($model->description) ? $model->description : null,
            'url' => $url,
        ];
    }

    /**
     * Transform given collection data.
     *
     * @param  \Illuminate\Support\Collection  $collection
     * @param  array  $parameters
     * @return \Illuminate\Support\Collection
     */
    public function transformCollection(Collection $collection, array $parameters = []) : Collection
    {
        $collection->filter(function ($value) {
            return ! empty($value);
        });

        if ($collection->isEmpty()) {
            return collect();
        }

        return $collection->transform(function ($model) use ($parameters) {
            return $this->transform($model, $parameters);
        });
    }
}
