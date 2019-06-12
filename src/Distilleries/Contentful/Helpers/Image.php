<?php

namespace Distilleries\Contentful\Helpers;

use Illuminate\Support\Str;

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
    public static function url(string $url, int $width = 0, int $height = 0, $format = '', int $quality = 0, ?bool $useProgressive = null, $fit = ''): string
    {
        if (empty($url)) {
            return '';
        }

        $imageUrl = '';

        $format = static::detectFormat($format);

        collect([
            'w' => $width,
            'h' => $height,
            'q' => !empty($quality) ? $quality : config('contentful.image.default_quality'),
            'fm' => config('contentful.image.use_webp') ? $format : null,
            'fl' => static::detectProgressive($format, $useProgressive) ? 'progressive' : null,
            'fit' => static::detectFit($fit),
        ])->filter(function ($value) {
            return !empty($value);
        })->each(function ($value, $key) use (& $imageUrl) {
            $imageUrl .= $key . '=' . $value . '&';
        });

        return static::replaceHosts($url, $imageUrl);
    }

    /**
     * Replace assets hosts by configured ones.
     *
     * @param null|string  $url
     * @param string  $imageUrl
     * @return string
     */
    protected static function replaceHosts(?string $url, string $imageUrl): string
    {
        if (Str::contains($url, '?')) {
            $url = explode('?', $url);
            $url = $url[0];
        }

        $searchHosts = config('contentful.image.search_hosts');
        $replaceHost = config('contentful.image.replace_host');
        if (! empty($searchHosts) && ! empty($replaceHost)) {
            $url = str_replace(explode(',', $searchHosts), $replaceHost, $url);
        }

        return ! empty($url) ? $url . '?' . trim($imageUrl, '&') : '';
    }

    /**
     * Auto-detect image format to serve (based on browser capability).
     *
     * @param  string  $format
     * @return string|null
     */
    protected static function detectFormat(string $format = ''): ?string
    {
        /** @var \Jenssegers\Agent\Agent $agent */
        $agent = app('agent');

        if (empty($format)) {
            $browser = mb_strtolower($agent->browser());
            if (($browser === 'chrome') && !$agent->isMobile()) {
                $format = config('contentful.image.use_webp') ? 'webp' : null;
            }
        }

        return $format;
    }

    /**
     * Detect if fit can be used.
     *
     * @param string|null  $fit
     * @return string|null
     */
    protected static function detectFit(?string $fit = null): ?string
    {
        if (empty($fit)) {
            $fit = 'fill';
        } else {
            $fit = ($fit !== 'default') ? $fit : null;
        }

        return $fit;
    }

    /**
     * Detect if progressive image can be used.
     *
     * @param null|string  $format
     * @param bool|null  $useProgressive
     * @return bool
     */
    protected static function detectProgressive(?string $format = '', ?bool $useProgressive = null): bool
    {
        if ($format === 'webp') {
            $useProgressive = false;
        }

        if ($useProgressive === null) {
            $useProgressive = ! empty(config('contentful.image.use_progressive'));
        }

        return $useProgressive;
    }
}
