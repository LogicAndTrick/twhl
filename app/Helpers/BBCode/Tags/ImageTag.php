<?php

namespace App\Helpers\BBCode\Tags;
 
class ImageTag extends Tag {

    function __construct()
    {
        $this->token = 'img';
        $this->element = 'img';
        $this->main_option = 'url';
        $this->options = array('url');
    }

    public function FormatResult($result, $parser, $scope, $options, $text)
    {
        $str = '<' . $this->element;
        $class = trim($this->element_class . ($this->token == 'simg' ? ' inline' : ''));
        if ($class) $str .= ' class="' . $class . '"';
        $url = $text;
        if (array_key_exists('url', $options)) {
            $url = $options['url'];
        }
        if (!preg_match('%^([a-z]{2,10}://)%i', $url)) {
            $url = 'http://' . $url;
        }
        $str .= ' src="' . $url . '"';
        $str .= ' />';
        return $str;
    }

    public function Validate($options, $text)
    {
        $url = $text;
        if (array_key_exists('url', $options)) $url = $options['url'];
        return stristr($url, '<script') === false && preg_match('%^([a-z]{2,10}://)?([^]"\n ]+?)$%i', $url);
    }
}
