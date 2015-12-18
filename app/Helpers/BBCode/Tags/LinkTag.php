<?php

namespace App\Helpers\BBCode\Tags;
 
class LinkTag extends Tag {

    function __construct()
    {
        $this->token = 'url';
        $this->element = 'a';
        $this->main_option = 'url';
        $this->options = array('url');
    }

    public function FormatResult($result, $parser, $state, $scope, $options, $text)
    {
        $str = '<' . $this->element;
        if ($this->element_class) $str .= ' class="' . $this->element_class . '"';
        $url = $text;
        if (array_key_exists('url', $options)) {
            $url = $options['url'];
        }
        if ($this->token == 'email') {
            $url = 'mailto:' . $url;
        } else if (!preg_match('%^([a-z]{2,10}://)%i', $url)) {
            $url = 'http://' . $url;
        }
        $str .= ' href="' . $parser->CleanUrl($url) . '"';
        $str .= '>';
        if (array_key_exists('url', $options)) {
            $str .= $parser->ParseBBCode($result, $text, $scope, $this->block ? 'block' : 'inline');
        } else {
            $str .= $text;
        }
        $str .= '</' . $this->element . '>';
        return $str;
    }

    public function Validate($options, $text)
    {
        $url = $text;
        if (array_key_exists('url', $options)) $url = $options['url'];
        return stristr($url, '<script') === false && preg_match('%^([a-z]{2,10}://)?([^]"\n ]+?)$%i', $url);
    }
}
