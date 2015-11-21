<?php

namespace App\Helpers\BBCode\Elements;
 
class MdHeadingElement extends Element {

    public $parser;
    public $text;
    public $level;

    function __construct()
    {

    }

    function Matches($lines)
    {
        $value = $lines->Value();
        return strlen($value) > 0 && $value[0] == '=';
    }

    function Consume($parser, $lines)
    {
        $value = trim($lines->Value());
        preg_match('/^(=*+)(.*?)=*$/i', $value, $res);
        $level = min(6, strlen($res[1]));
        $text = trim($res[2]);
        $el = new MdHeadingElement();
        $el->parser = $parser;
        $el->text = $text;
        $el->level = $level;
        return $el;
    }

    function Parse($result, $scope)
    {
        $text = $this->parser->CleanString($this->text);
        return '<h' . $this->level . '>' . $this->parser->ParseBBCode($result, $text, $scope, 'inline') . '</h' . $this->level . '>';
    }
}
