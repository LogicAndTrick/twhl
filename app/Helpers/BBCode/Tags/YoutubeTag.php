<?php

namespace App\Helpers\BBCode\Tags;
 
class YoutubeTag extends Tag {

    function __construct()
    {
        $this->token = 'youtube';
        $this->element = 'div';
        $this->main_option = 'id';
        $this->options = array('id');
    }

    public function FormatResult($result, $parser, $state, $scope, $options, $text)
    {
        $id = array_key_exists('id', $options) ? $options['id'] : $text;
        $classes = ['embedded', 'video'];
        if ($this->element_class) $classes[] = $this->element_class;
        $impl = implode(' ', $classes);

        return "<div class='{$impl}'>"
                    . "<div class='caption-panel'>"
                        . "<div class='video-container caption-body'>"
                            . "<div class='video-content'>"
                                . "<div class='uninitialised' data-youtube-id='$id' style='background-image: url(\"https://i.ytimg.com/vi/{$id}/hqdefault.jpg\");'></div>"
                            . "</div>"
                        . "</div>"
                    . "</div>"
                . "</div>";
    }

    public function Validate($options, $text)
    {
        $url = $text;
        if (array_key_exists('id', $options)) $url = $options['id'];
        return preg_match('%^[a-zA-Z0-9_-]{6,11}$%i', $url);
    }
}
