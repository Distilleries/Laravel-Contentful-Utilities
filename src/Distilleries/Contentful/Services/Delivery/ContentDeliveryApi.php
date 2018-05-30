<?php

namespace Distilleries\Contentful\Services\Contentful;

use Distilleries\Contentful\Services\Locales;
use GuzzleHttp\RequestOptions;
use Illuminate\Cache\CacheManager;

class ContentDeliveryApi extends AbstractApi implements ContentDelivery
{

    /**
     * AbstractApi constructor.
     *
     * @param  \Illuminate\Cache\CacheManager $cache
     */
    public function __construct(CacheManager $cache,$config)
    {
        $this->client = new Client([
            'verify' => false,
        ]);

        $this->config = $config;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function entries($parameters = [])
    {
        $response = null;

        $url = $this->getUri() . '/spaces/' . $this->config['delivery.space'] . '/entries';

        if (env('DEBUGBAR_ENABLED', false)) {
            \Debugbar::measure('Call CF ContentDeliveryApi: ' . $url . '?' . http_build_query($parameters), function () use (&$response, $url, $parameters) {
                $response = $this->client->request('GET', $url, [
                    RequestOptions::QUERY => array_merge($parameters, [
                        'access_token' => config('contentful.use_preview') ? $this->config['delivery.preview'] : $this->config['delivery.token'],
                    ]),
                ]);
            });
        } else {
            $response = $this->client->request('GET', $url, [
                RequestOptions::QUERY => array_merge($parameters, [
                    'access_token' => config('contentful.use_preview') ? $this->config['delivery.preview'] : $this->config['delivery.token'],
                ]),
            ]);
        }

        return $this->parseAndReturnResult($parameters['content_type'], $this->getCacheKey($parameters, 'forever'), $response);
    }

    /**
     * {@inheritdoc}
     */
    public function entry($entryId, $locale = '')
    {
        $locale = ! empty($locale) ? $locale : app()->getLocale();
        $locale = $locale != '*'?Locales::contentful($locale):$locale;

        $parameters = [
            'id' => $entryId,
            'locale' => $locale,
            'content_type' => 'single_entry',
        ];

        $url = $this->getUri() . '/spaces/' . $this->config['delivery.space'] . '/entries/' . $entryId;

        $response = $this->client->request('GET', $url, [
            RequestOptions::QUERY => [
                'locale' => $locale,
                'access_token' => config('contentful.use_preview') ? $this->config['delivery.preview'] : $this->config['delivery.token'],
            ],
        ]);

        return $this->parseAndReturnResult($parameters['content_type'], $this->getCacheKey($parameters, 'forever'), $response);
    }


    /**
     * Return a single asset.
     *
     * @param  string  $assetId
     * @param  string  $locale
     * @return array
     */
    public function asset($assetId, $locale = '')
    {
        $locale = ! empty($locale) ? $locale : app()->getLocale();
        $locale = Locales::contentful($locale);

        $parameters = [
            'id' => $assetId,
            'locale' => $locale,
            'content_type' => 'single_entry',
        ];

        $url = $this->getUri() . '/spaces/' . $this->config['delivery.space'] . '/assets/' . $assetId;

        $response = $this->client->request('GET', $url, [
            RequestOptions::QUERY => [
                'locale' => $locale,
                'access_token' => config('contentful.use_preview') ? $this->config['delivery.preview'] : $this->config['delivery.token'],
            ],
        ]);
        return $this->parseAndReturnResult($parameters['content_type'], $this->getCacheKey($parameters, 'forever'), $response);
    }


    /**
     * Return assets for given parameters
     *
     * @param  array  $parameters
     * @return array
     */
    public function assets($parameters = [])
    {
        $url = $this->getUri() . '/spaces/' . $this->config['delivery.space'] . '/assets';

        $response = $this->client->request('GET', $url, [
            RequestOptions::QUERY => array_merge($parameters, [
                'access_token' => config('contentful.use_preview') ? $this->config['delivery.preview'] : $this->config['delivery.token'],
            ]),
        ]);

        return $this->parseAndReturnResult($parameters['content_type'], $this->getCacheKey($parameters, 'forever'), $response);
    }

}
