<?php

namespace App\Helpers\BBCode\Tags;
 
class CodeTag extends Tag {

    function __construct()
    {
        $this->token = 'code';
        $this->element = 'code';
    }

    public function FormatResult($result, $parser, $state, $scope, $options, $text)
    {
        $text = $parser->CleanString($text);
        return '<code>' . $text . '</code>';
    }
}
