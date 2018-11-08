<?php

namespace Sunra\PhpSimple;

require 'DomParser'.DIRECTORY_SEPARATOR.'simple_html_dom.php';

class HtmlDomParser
{
    /**
     * @return \DomParser\simple_html_dom
     */
    public static function file_get_html()
    {
        return call_user_func_array('\DomParser\file_get_html', func_get_args());
    }

    /**
     * get html dom from string.
     *
     * @return \DomParser\simple_html_dom
     */
    public static function str_get_html()
    {
        return call_user_func_array('\DomParser\str_get_html', func_get_args());
    }
}
