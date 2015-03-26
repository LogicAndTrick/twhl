<?php

namespace App\Helpers\BBCode\Elements;
 
class MdQuoteElement extends Element {

    public $parser;
    public $text;

    function __construct()
    {

    }

    function Matches($lines)
    {
        $value = $lines->Value();
        return strlen($value) > 0 && $value[0] == '>';
    }

    function Consume($parser, $lines)
    {
        $arr = array();
        $value = $lines->Value();
        $arr[] = trim(substr($value, 1));
        while ($lines->Next()) {
            $value = trim($lines->Value());
            if (strlen($value) == 0) {
                $lines->Back();
                break;
            }
            $arr[] = $value;
        }
        $el = new MdQuoteElement();
        $el->parser = $parser;
        $el->text = implode("\n", $arr);
        return $el;
    }

    function Parse($result, $scope)
    {
        return '<blockquote>' . $this->parser->ParseBBCode($result, $this->text, $scope, 'block') . '</blockquote>';
    }
}
