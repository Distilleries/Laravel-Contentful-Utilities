<?php

namespace Distilleries\Contentful\Services\Contentful;

use Exception;

class ContentDeliveryApiCache extends ContentDeliveryApi
{
    /**
     * {@inheritdoc}
     */
    public function entries($parameters = [])
    {
        $model = $parameters['content_type'];
        if (empty(config('cache.api_enabled', false))) {
            return parent::entries($parameters);
        }

        return $this->cache->remember($this->getCacheKey($parameters), config('cache.timelife'), function () use ($parameters, $model) {
            $cacheKey = $this->getCacheKey($parameters, 'forever');

            try {
                return parent::entries($parameters);
            } catch (Exception $e) {
                $value = $this->getFromCache($model, $cacheKey);
                if (! empty($value)) {
                    return $value;
                }
                throw new Exception(trans('errors.unable_to_call_api'), 0, $e);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function asset($entryId, $locale = '')
    {
        $model = 'asset';

        if (empty(config('cache.api_enabled', false))) {
            return parent::entries($entryId,$locale);
        }

        return $this->cache->remember($this->getCacheKey([$entryId,$locale]), config('cache.timelife'), function () use ($entryId,$locale, $model) {
            $cacheKey = $this->getCacheKey([$entryId,$locale], 'forever');

            try {
                return parent::asset($entryId,$locale);
            } catch (Exception $e) {
                $value = $this->getFromCache($model, $cacheKey);
                if (! empty($value)) {
                    return $value;
                }
                throw new Exception(trans('errors.unable_to_call_api'), 0, $e);
            }
        });
    }


}
