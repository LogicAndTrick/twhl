<?php

namespace App\Helpers\BBCode\Tags;
 
class WikiCategoryTag extends LinkTag {

    function __construct()
    {
        $this->token = false;
        $this->element = '';
    }

    public function Matches($state, $token)
    {
        $peekTag = $state->Peek(5);
        $pt = $state->PeekTo(']');
        return $peekTag == '[cat:' && $pt && strlen($pt) > 5 && strstr($pt, "\n") === false;
    }

    public function Parse($result, $parser, $state, $scope)
    {
        $index = $state->Index();

        if ($state->ScanTo(':') != '[cat' || $state->Next() != ':') {
            $state->Seek($index, true);
            return false;
        }
        $str = $state->ScanTo(']');
        if ($state->Next() != ']') {
            $state->Seek($index, true);
            return false;
        }
        $state->SkipWhitespace();
        $result->AddMeta('WikiCategory', $str);
        return '';
    }
}
