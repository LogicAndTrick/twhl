<?php

namespace App\Helpers\BBCode\Elements;
use Illuminate\Support\Str;
 
class MdCodeElement extends Element {

    public $parser;
    public $text;

    function __construct()
    {

    }

    function Matches($lines)
    {
        $value = $lines->Value();
        return substr($value, 0, 3) == '```';
    }

    function Consume($parser, $lines)
    {
        $current = $lines->Current();

        $first_line = rtrim(substr($lines->Value(), 3));

        $arr = [];
        $arr[] = $first_line;

        $found = false;
        while ($lines->Next()) {
            $value = rtrim($lines->Value());
            if (Str::endsWith($value, '```')) {
                $last_line = rtrim(substr($value, 0, -3));
                $arr[] = $last_line;
                $found = true;
                break;
            } else {
                $arr[] = $value;
            }
        }
        if (!$found) {
            $lines->SetCurrent($current);
            return null;
        }

        // Trim blank lines from the start and end of the array
        for ($i = 0; $i < 2; $i++) {
            while (count($arr) > 0 && trim($arr[0]) == '') array_shift($arr);
            $arr = array_reverse($arr);
        }

        // Replace all tabs with 4 spaces
        $arr = array_map(function ($a) {
            return str_replace("\t", '    ', $a);
        }, $arr);

        // Find the longest common whitespace amongst all lines (ignore blank lines)
        $longest_whitespace = array_reduce($arr, function ($c, $i) {
            if (strlen(trim($i)) == 0) return $c;
            $wht = strlen($i) - strlen(ltrim($i));
            return min($wht, $c);
        }, 9999);

        // Dedent all lines by the longest common whitespace
        $arr = array_map(function ($a) use ($longest_whitespace) {
            return substr($a, $longest_whitespace);
        }, $arr);

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
