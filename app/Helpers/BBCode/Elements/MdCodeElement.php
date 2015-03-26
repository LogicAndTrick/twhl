<?php

namespace App\Helpers\BBCode\Elements;
 
class MdCodeElement extends Element {

    public $parser;
    public $text;

    function __construct()
    {

    }

    function Matches($lines)
    {
        $value = $lines->Value();
        return trim($value) != '' && $value[0] == ' ';
    }

    function Consume($parser, $lines)
    {
        $rtval = rtrim($lines->Value());
        $value = trim($lines->Value());
        $level = strlen($rtval) - strlen($value);
        $expected = str_repeat(' ', $level);

        $arr = array();
        $arr[] = $value;
        while ($lines->Next()) {
            $value = $lines->Value();
            if (substr($value, 0, $level) != $expected) {
                $lines->Back();
                break;
            }
            $arr[] = rtrim(substr($value, $level));
        }
        $el = new MdCodeElement();
        $el->parser = $parser;
        $el->text = implode("\n", $arr);
        return $el;
    }

    function Parse($result, $scope)
    {
        return '<pre>' . $this->parser->CleanString($this->text) . '</pre>';
    }
}
