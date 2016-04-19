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
        if (trim($value) == '') return false;
        if (strlen($value) > 4 && substr($value, 0, 4) == '    ') return true;
        if ($value[0] == "\t") return true;
        return false;
    }

    function Consume($parser, $lines)
    {
        $val = $lines->Value();
        if ($val[0] == "\t") {
            $expected = "\t";
            $level = 1;
            $value = substr($val, 1);
        } else {
            $rtval = rtrim($val);
            $value = trim($val);
            $level = strlen($rtval) - strlen($value);
            $expected = str_repeat(' ', $level);
        }

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
        $text = $this->parser->CleanString($this->text);
        return '<pre><code>' . $text . '</code></pre>';
    }
}
