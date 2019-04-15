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
        // The text is already html escaped at this point (the default element cleans all strings)
        return '<code>' . $text . '</code>';
    }
}
