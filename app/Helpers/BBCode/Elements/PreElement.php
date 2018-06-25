<?php

namespace App\Helpers\BBCode\Elements;
 
class PreElement extends Element {

    public $parser;
    public $text;

    function __construct()
    {

    }

    function Matches($lines)
    {
        $value = $lines->Value();
        return substr(trim($value), 0, 5) == '[pre]';
    }

    function Consume($parser, $lines)
    {
        $current = $lines->Current();

        $arr = array();
        $line = substr(trim($lines->Value()), 5);
        if (substr(trim($line), -6) == '[/pre]') {
            $lines->Next();
            $arr[] = substr(trim($line), 0, -6);
        } else {
            if (strlen($line) > 0) $arr[] = $line;
            $found = false;
            while ($lines->Next()) {
                $value = $lines->Value();
                if (substr(trim($value), -6) == '[/pre]') {
                    $found = true;
                    $value = substr(trim($value), 0, -6);
                }
                $value = rtrim($value);
                if (count($arr) > 0 || strlen($value) > 0) $arr[] = $value;
                if ($found) break;
            }
            if (!$found) {
                $lines->SetCurrent($current);
                return null;
            }
        }

        $el = new PreElement();
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
