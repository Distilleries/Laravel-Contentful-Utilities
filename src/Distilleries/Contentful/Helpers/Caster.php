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
     * @return string
     */
    public static function json($data) : string
    {
        return ! empty($data) ? json_encode($data) : '';
    }

    /**
     * Transform markdown content to an HTML string.
     *
     * @param  mixed  $md
     * @return string
     */
    public static function markdown($md) : string
    {
        if (empty($md)) {
            return '';
        }

        return (new Parsedown)->setBreaksEnabled(true)->text($md);
    }

    /**
     * Cast an integer to an integer value otherwise to null.
     *
     * @param  mixed  $int
     * @return integer|null
     */
    public static function integer($int) : ?int
    {
        return is_numeric($int) ? (int) $int : null;
    }

    /**
     * Return entry ID in given "Link" array.
     *
     * @param  array  $entry
     * @return string|null
     */
    public static function entryId(array $entry) : ?string
    {
        return (isset($entry['sys']) and isset($entry['sys']['id'])) ? $entry['sys']['id'] : null;
    }
}
