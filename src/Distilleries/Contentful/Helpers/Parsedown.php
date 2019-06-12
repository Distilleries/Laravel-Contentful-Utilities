<?php

namespace Distilleries\Contentful\Helpers;

use Parsedown as BaseParsedown;

class Parsedown extends BaseParsedown
{
    /**
     * {@inheritdoc}
     */
    protected $voidElements = [
        'area',
        'base',
        'br',
        'col',
        'command',
        'embed',
        'hr',
        'img',
        'input',
        'link',
        'meta',
        'param',
        'source',
        'blockquote',
    ];
}
