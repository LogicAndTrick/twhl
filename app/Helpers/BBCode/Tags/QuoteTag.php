<?php

namespace App\Helpers\BBCode\Tags;
 
class QuoteTag extends Tag {

    function __construct()
    {
        $this->token = 'quote';
        $this->element = 'blockquote';
        $this->nested = true;
        $this->block = true;
        $this->main_option = 'name';
        $this->options = array('name');
    }

    public function FormatResult($result, $parser, $scope, $options, $text)
    {
        $str = '<' . $this->element;
        if ($this->element_class) $str .= ' class="' . $this->element_class . '"';
        $str .= '>';
        if (array_key_exists('name', $options)) $str .= '<strong>' . $parser->CleanString($options['name']) . ' said:</strong>';
        $str .= $parser->ParseBBCode($result, $text, $scope, $this->block ? 'block' : 'inline');
        $str .= '</' . $this->element . '>';
        return $str;
    }
}
