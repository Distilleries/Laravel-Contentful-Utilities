<?php

namespace Distilleries\Contentful\Helpers;

use Distilleries\Contentful\Models\Location;
use Illuminate\Support\Collection;
use Parsedown;
use Illuminate\Support\Carbon;

class Caster
{
    /**
     * Cast value to a string.
     *
     * @param  mixed  $str
     * @return string
     */
    public static function string($str): string
    {
        return (string) $str;
    }

    /**
     * Cast value to a string.
     *
     * @param  mixed  $str
     * @param  mixed  $default
     * @return \Illuminate\Support\Carbon|null
     */
    public static function datetime($str, $default = null): ?Carbon
    {
        if (empty($str)) {
            return $default;
        }

        try {
            $carbon = new Carbon($str);
        } catch (\Exception $e) {
            $carbon = null;
        }

        return $carbon;
    }

    /**
     * Transform data to its JSON representation.
     *
     * @param  mixed  $data
     * @param  mixed  $default
     * @return string
     */
    public static function toJson($data, $default = null): string
    {
        return ! empty($data) ? json_encode($data): $default;
    }

    /**
     * Transform a JSON string to its associative array representation.
     *
     * @param  mixed  $json
     * @return array|null
     */
    public static function fromJson($json): ?array
    {
        if (empty($json)) {
            return null;
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }

    /**
     * Transform markdown content to an HTML string.
     *
     * @param  mixed  $md
     * @param  mixed  $default
     * @return string
     */
    public static function markdown($md, $default = null): ?string
    {
        if (empty($md)) {
            return $default;
        }

        return (new Parsedown)->setBreaksEnabled(true)->text($md);
    }

    /**
     * Cast an integer to an integer value otherwise to null.
     *
     * @param  mixed  $int
     * @param  mixed  $default
     * @return integer|null
     */
    public static function integer($int, $default = null): ?int
    {
        return is_numeric($int) ? (int) $int : $default;
    }

    /**
     * Cast an boolean to an boolean value otherwise to null.
     *
     * @param  mixed  $bool
     * @param  mixed  $default
     * @return boolean|null
     */
    public static function boolean($bool, $default = null): ?bool
    {
        return is_bool($bool) ? (bool) $bool : $default;
    }

    /**
     * Cast an float to an float value otherwise to null.
     *
     * @param  mixed  $float
     * @param  mixed  $default
     * @return float|null
     */
    public static function float($float, $default = null): ?float
    {
        return is_float($float) ? (float) $float : $default;
    }


    /**
     * Cast an array to an array value otherwise to null.
     *
     * @param  mixed  $array
     * @param  mixed  $default
     * @return array|null
     */
    public static function toArray($array, $default = null): ?array
    {
        return is_array($array) ? (array) $array : $default;
    }

    /**
     * Cast an array to an collection value otherwise to null.
     *
     * @param  mixed  $array
     * @param  mixed  $default
     * @return Collection|null
     */
    public static function collect($array, $default = null): ?Collection
    {
        return is_array($array) ? collect($array) : $default;
    }

    /**
     * Return entry ID in given "Link" array.
     *
     * @param  array  $entry
     * @param  mixed  $default
     * @return string|null
     */
    public static function entryId(array $entry, $default = null): ?string
    {
        return (isset($entry['sys']) and isset($entry['sys']['id'])) ? $entry['sys']['id'] : $default;
    }

    /**
     * Return a Location object
     *
     * @param  array  $entry
     * @param  Location  $default
     * @return Location|null
     */
    public static function location(array $entry, ?Location $default = null): ?Location
    {
        return isset($entry['default'])? new Location($entry['default']):$default;
    }
}
