<?php

namespace App\Helpers\BBCode\Elements;

class DefaultElement extends Element
{
    public $parser;
    public $text;

    function __construct($parser, $lines)
    {
        $this->parser = $parser;
        $this->text = trim(implode("\n", $lines));
    }

    function Parse($result, $scope)
    {
        return $this->parser->ParseBBCode($result, $this->text, $scope, 'block');
    }

    function Matches($lines)
    {
        return false;
    }

    function Consume($parser, $lines)
    {
        return false;
    }
}