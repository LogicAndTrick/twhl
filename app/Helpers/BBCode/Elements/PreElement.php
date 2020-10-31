<?php

namespace App\Helpers\BBCode\Elements;
 
use App\Helpers\BBCode\Tags\FontTag;

class PreElement extends Element {

    public $parser;
    public $text;
    public $lang;
    public $highlight;

    function __construct()
    {

    }

    protected $supported_languages = ["php", "dos", "css", "cpp", "cs", "ini", "json", "xml", "angelscript", "javascript"];

    function Matches($lines)
    {
        $value = $lines->Value();
        return substr(trim($value), 0, 4) == '[pre' && preg_match('/\[pre(=[a-z ]+)?\]/si', $value);
    }

    function Consume($parser, $lines)
    {
        $current = $lines->Current();

        $arr = array();

        $line = trim($lines->Value());
        preg_match('/\[pre(?:=([a-z ]+))?\]/si', $line, $res);
        $line = substr($line, strlen($res[0]));
        $lang = null;
        $hl = false;
        if (isset($res[1])) {
            $spl = explode(' ', $res[1]);
            $hl = array_search('highlight', $spl) !== false;
            $spl = array_filter($spl, function($s) { return $s !== 'highlight'; });
            $lang = count($spl) > 0 ? $spl[0] : null;
        }

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

        // Process highlight commands
        $highlight = [];
        if ($hl) {
            // Highlight commands get their own line so we need to keep track of which lines we're removing as we go
            $new_arr = [];
            $first_line = 0;
            foreach ($arr as $line) {
                if (substr($line, 0, 2) == '@@') {
                    if (preg_match('/^@@(?:(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z]+|\d+)(?::(\d+))?)?$/im', $line, $params)) {
                        $num_lines = 1;
                        $color = '#FF8000';
                        for ($i = 1; $i < count($params); $i++) {
                            $p = $params[$i];
                            if (FontTag::IsValidColor($p)) $color = $p;
                            else if (is_numeric($p)) $num_lines = intval($p);
                        }
                    	$highlight[] = [$first_line, $num_lines, $color];
                    	continue;
                    }
                }
                $first_line++;
                $new_arr[] = $line;
            }
            $arr = $new_arr;

            // Make sure highlights don't overlap each other or go past the end of the block
            $highlight[] = [count($arr)-1, 0, ''];
            for ($i = 0; $i < count($highlight) - 1; $i++) {
                $curr = $highlight[$i];
                $next = $highlight[$i + 1];
                $last_line = $curr[0] + $curr[1] - 1;
                if ($last_line >= $next[0]) $highlight[$i][1] = $next[0] - $curr[0];
            }
            $highlight = array_filter($highlight, function ($h) { return $h[1] > 0; });
        }

        $el = new PreElement();
        $el->parser = $parser;
        $el->text = implode("\n", $arr);
        $el->lang = $lang;
        $el->highlight = $highlight;
        return $el;
    }

    function Parse($result, $scope)
    {
        $highlights = implode('', array_map(function ($h) {
            return "<div class=\"line-highlight\" style=\"top: {$h[0]}em; height: {$h[1]}em; background: {$h[2]};\"></div>";
        }, $this->highlight));
        $text = $this->parser->CleanString($this->text);
        return '<pre' . ($this->lang ? ' class="lang-' . $this->lang . '"' : '') . '><code>' . $highlights . $text . '</code></pre>';
    }
}
