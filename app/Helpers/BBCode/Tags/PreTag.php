<?php

namespace App\Helpers\BBCode\Tags;
 
class PreTag extends Tag {

    function __construct()
    {
        $this->token = 'pre';
        $this->element = 'pre';
    }

    public function FormatResult($result, $parser, $state, $scope, $options, $text)
    {
        // The text is already html escaped at this point (the default element cleans all strings)
        $str = '<' . $this->element;
        if ($this->element_class) $str .= ' class="' . $this->element_class . '"';
        $str .= '><code>';
        $str .= $text;
        $str .= '</code></' . $this->element . '>';
        return $str;
    }
}
