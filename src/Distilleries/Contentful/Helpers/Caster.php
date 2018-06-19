<?php

namespace Distilleries\Contentful\Helpers;

use Parsedown;

class Caster
{
    /**
     * Cast value to a string.
     *
     * @param  mixed  $str
     * @return string
     */
    public static function string($str) : string
    {
        return (string) $str;
    }

    /**
     * Transform data to its JSON representation.
     *
     * @param  mixed  $data
     * @param  mixed  $default
     * @return string
     */
    public static function toJson($data, $default = null) : string
    {
        return ! empty($data) ? json_encode($data) : $default;
    }

    /**
     * Transform a JSON string to its associative array representation.
     *
     * @param  mixed  $json
     * @return array|null
     */
    public static function fromJson($json) : ?array
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
    public static function markdown($md, $default = null) : ?string
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
    public static function integer($int, $default = null) : ?int
    {
        return is_numeric($int) ? (int) $int : $default;
    }

    /**
     * Return entry ID in given "Link" array.
     *
     * @param  array  $entry
     * @param  mixed  $default
     * @return string|null
     */
    public static function entryId(array $entry, $default = null) : ?string
    {
        return (isset($entry['sys']) and isset($entry['sys']['id'])) ? $entry['sys']['id'] : $default;
    }
}
