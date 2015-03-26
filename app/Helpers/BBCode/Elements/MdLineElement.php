<?php

namespace App\Helpers\BBCode\Elements;
 
class MdLineElement extends Element {

    public $parser;

    function __construct()
    {

    }

    function Matches($lines)
    {
        $value = rtrim($lines->Value());
        return strlen($value) >= 3 && $value == str_repeat('-', strlen($value));
    }

    function Consume($parser, $lines)
    {
        $el = new MdLineElement();
        $el->parser = $parser;
        return $el;
    }

    function Parse($result, $scope)
    {
        return '<hr />';
    }
}
