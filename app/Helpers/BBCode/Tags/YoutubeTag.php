<?php

namespace App\Helpers\BBCode\Tags;
 
class YoutubeTag extends Tag {

    function __construct()
    {
        $this->token = 'youtube';
        $this->element = 'iframe';
        $this->main_option = 'id';
        $this->options = array('id');
        $this->element_class = 'youtube';
    }

    public function FormatResult($result, $parser, $scope, $options, $text)
    {
        $str = '<iframe width="640" height="390" frameborder="0" allowfullscreen';
        if ($this->element_class) $str .= ' class="' . $this->element_class . '"';
        $id = $text;
        if (array_key_exists('id', $options)) {
            $id = $options['id'];
        }
        $url = "https://www.youtube.com/embed/{$id}?rel=0";
        $str .= ' src="' . $url . '"';
        $str .= '></iframe>';
        return $str;
    }

    public function Validate($options, $text)
    {
        $url = $text;
        if (array_key_exists('id', $options)) $url = $options['id'];
        return preg_match('%^[a-zA-Z0-9_-]{6,11}$%i', $url);
    }
}
