<?php

namespace Distilleries\Contentful\Helpers;

use Jenssegers\Agent\Agent;

class Image
{
    /**
     * Return image URL to serve based on given parameters.
     *
     * @param  string  $url
     * @param  integer  $width
     * @param  integer  $height
     * @param  string  $format
     * @param  integer  $quality
     * @param  boolean|null  $useProgressive
     * @param  string  $fit
     * @return string
     */
    public static function url(string $url, int $width = 0, int $height = 0, $format = '', int $quality = 0, ?bool $useProgressive = null, $fit = '') : string
    {
        if (empty($url)) {
            return '';
        }

        $imageUrl = '';

        $format = static::detectFormat($format);

        if ($format === 'webp') {
            $useProgressive = false;
        }
        if ($useProgressive === null) {
            $useProgressive = ! empty(config('contentful.image.use_progressive'));
        }

        if (empty($fit)) {
            $fit = 'fill';
        } else {
            $fit = ($fit !== 'default') ? $fit : null;
        }

        collect([
            'w' => $width,
            'h' => $height,
            'q' => ! empty($quality) ? $quality : config('contentful.image.default_quality'),
            'fm' => config('contentful.image.use_webp') ? $format : null,
            'fl' => $useProgressive ? 'progressive' : null,
            'fit' => $fit,
        ])->filter(function ($value) {
            return ! empty($value);
        })->each(function ($value, $key) use (& $imageUrl) {
            $imageUrl .= $key . '=' . $value . '&';
        });

        if (str_contains($url, '?')) {
            $url = explode('?', $url);
            $url = $url[0];
        }

        $searchHosts = config('contentful.image.search_hosts');
        $replaceHost = config('contentful.image.replace_host');
        if (! empty($searchHosts) and ! empty($replaceHost)) {
            $url = str_replace(explode(',', $searchHosts), $replaceHost, $url);
        }

        return ! empty($url) ? $url . '?' . trim($imageUrl, '&') : '';
    }

    /**
     * Auto-detect image format to serve (based on browser capability).
     *
     * @param  string  $format
     * @return string
     */
    protected static function detectFormat(string $format = '') : string
    {
        $agent = new Agent;

        if (empty($format)) {
            $browser = mb_strtolower($agent->browser());
            if (($browser === 'chrome') and ! $agent->isMobile()) {
                $format = 'webp';
            }
        }

        return $format;
    }
}