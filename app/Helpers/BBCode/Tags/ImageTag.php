<?php

namespace App\Helpers\BBCode\Tags;
 
class ImageTag extends Tag {

    function __construct()
    {
        $this->token = 'img';
        $this->element = 'span';
        $this->main_option = 'url';
        $this->options = array('url');
    }

    public function FormatResult($result, $parser, $scope, $options, $text)
    {
        $url = $text;
        if (array_key_exists('url', $options)) {
            $url = $options['url'];
        }
        if (!preg_match('%^([a-z]{2,10}://)%i', $url)) {
            $url = 'http://' . $url;
        }

        $classes = ['embedded', 'image'];
        if ($this->element_class) $classes[] = $this->element_class;
        if ($this->token == 'simg') $classes[] = 'inline';

        return '<span class="' . implode(' ', $classes) . '"><span class="caption-panel">'
             . '<img class="caption-body" src="' . $parser->CleanUrl($url) . '" alt="User posted image" />'
             . '</span></span>';
    }

    public function Validate($options, $text)
    {
        $url = $text;
        if (array_key_exists('url', $options)) $url = $options['url'];
        return stristr($url, '<script') === false && preg_match('%^([a-z]{2,10}://)?([^]"\n ]+?)$%i', $url);
    }
}
