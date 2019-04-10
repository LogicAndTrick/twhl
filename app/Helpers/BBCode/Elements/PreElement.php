<?php

namespace App\Helpers\BBCode\Elements;
 
class PreElement extends Element {

    public $parser;
    public $text;
    public $lang;

    function __construct()
    {

    }

    protected $supported_languages = ["php", "dos", "css", "cpp", "cs", "ini", "json", "xml", "angelscript", "javascript"];

    function Matches($lines)
    {
        $value = $lines->Value();
        return substr(trim($value), 0, 4) == '[pre' && preg_match('/\[pre(=[a-z]+)?\]/si', $value);
    }

    function Consume($parser, $lines)
    {
        $current = $lines->Current();

        $arr = array();

        $line = trim($lines->Value());
        preg_match('/\[pre(?:=([a-z]+))?\]/si', $line, $res);
        $line = substr($line, strlen($res[0]));
        $lang = isset($res[1]) ? $res[1] : null;

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
        $el->lang = $lang;
        return $el;
    }

    function Parse($result, $scope)
    {
        $text = $this->parser->CleanString($this->text);
        return '<pre' . ($this->lang ? ' class="lang-' . $this->lang . '"' : '') . '><code>' . $text . '</code></pre>';
    }
}
