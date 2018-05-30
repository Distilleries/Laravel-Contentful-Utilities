<?php

namespace Distilleries\Contentful\Helpers;

use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class Image
{
    /**
     * Auto-detect image format to serve.
     *
     * @param  string|null $format
     * @return string|null
     */
    protected static function autoDetectFormat($format = null)
    {
        $agent = new Agent;
        $browser = mb_strtolower($agent->browser());

        if (empty($format) and ($browser === 'chrome') and !$agent->isMobile()) {
            $format = 'webp';
        }

        return $format;
    }

    /**
     * Return Contentful image URL with transformations parameters.
     *
     * @param  string $url
     * @param  integer|null $width
     * @param  integer|null $height
     * @param  string|null $format
     * @param  integer|null $quality
     * @param  boolean|null $isProgressive
     * @param  boolean|null $fit
     * @return string
     */
    public static function getUrl($url, $width = null, $height = null, $format = null, $quality = null, $isProgressive = null, $fit = null)
    {
        $stringResult = '';
        $format = static::autoDetectFormat($format);

        collect([
            'w' => $width,
            'h' => $height,
            'q' => !empty($quality) ? $quality : config('contentful.image.quality', 80),
            'fm' => config('contentful.image.webp_enabled')?$format:false,
            'fl' => ($format !== 'webp') ? (!empty($isProgressive) ? 'progressive' : config('contentful.image.progressive', null)) : null,
            'fit' => !empty($fit) ? (($fit === 'default') ? null : $fit) : 'fill',
        ])->filter(function ($value, $key) {
            return !empty($value);
        })->each(function ($value, $key) use (&$stringResult) {
            $stringResult .= $key . '=' . $value . '&';
        });


        if(Str::contains($url,'?')){
            $url = explode("?",$url);
            $url = $url[0];
        }

        $search = config('contentful.image.replace_host');
        $dest = config('contentful.image.dest_host');

        if(!empty($search) && !empty($dest)){
            $url = Str::replaceFirst($search,$dest,$url);
        }

        return !empty($url) ? $url . '?' . trim($stringResult, '&') : '';
    }
}