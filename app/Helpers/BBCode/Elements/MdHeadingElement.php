<?php

namespace App\Helpers\BBCode\Elements;
 
use App\Helpers\BBCode\ParseResult;
use Illuminate\Support\Arr;

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

    static function getUniqueAnchor(ParseResult $result, int $level, string $text) : string
    {
        $id = preg_replace('%[^\da-z?/:@\-._~!$&\'()*+,;=]%i', '_', $text);
        $anchor = $id;
        $inc = 1;
        do {
            // Find duplicate
            $dup = Arr::first($result->GetMeta('Heading'), fn($x) => $x['id'] === $anchor);
            if (!$dup) break;
            $inc++;
            $anchor = "{$id}_{$inc}";
        } while (true);
        return $anchor;
    }

    function Parse($result, $scope)
    {
        $id = MdHeadingElement::getUniqueAnchor($result, $this->level, $this->text);
        $result->AddMeta('Heading', [
            'level' => $this->level,
            'text' => $this->text,
            'id' => $id
        ]);
        $text = $this->parser->CleanString($this->text);
        return '<h' . $this->level . ' id="' . $id . '">' . $this->parser->ParseBBCode($result, $text, $scope, 'inline') . '</h' . $this->level . '>';
    }
}
