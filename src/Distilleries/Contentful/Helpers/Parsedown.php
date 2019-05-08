<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 08/05/2019
 * Time: 15:18
 */

namespace Distilleries\Contentful\Helpers;


class Parsedown extends \Parsedown
{
    protected $voidElements = array(
        'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source','blockquote',
    );

}