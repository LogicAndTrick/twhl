<?php

namespace App\Helpers\BBCode\Tags;
 
class SpoilerTag extends Tag {

    function __construct()
    {
        $this->token = 'spoiler';
        $this->element = 'span';
        $this->element_class = 'spoiler';
        $this->main_option = 'text';
        $this->options = array('text');
        $this->all_options_in_main = true;
    }

    public function FormatResult($result, $parser, $state, $scope, $options, $text)
    {
        $t = 'Spoiler';
        if (array_key_exists('text', $options) && strlen(trim($options['text'])) > 0) {
            $t = trim($options['text']);
        }

        $str = '<' . $this->element;
        if ($this->element_class) $str .= ' class="' . $this->element_class . '"';
        $str .= ' title="' . $t . '"';
        $str .= '>';
        $str .= $parser->ParseBBCode($result, $text, $scope, $this->block ? 'block' : 'inline');
        $str .= '</' . $this->element . '>';
        return $str;
    }
}
