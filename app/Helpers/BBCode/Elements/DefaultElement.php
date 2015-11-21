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
        $text = $this->parser->CleanString($this->text);
        return $this->parser->ParseBBCode($result, $text, $scope, 'block');
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