<?php

namespace App\Helpers\BBCode\Tags;

use App\Models\Wiki\WikiRevisionCredit;

class WikiCreditTag extends LinkTag {

    function __construct()
    {
        $this->token = false;
        $this->element = '';
    }

    public function Matches($state, $token)
    {
        $peekTag = $state->Peek(8);
        $pt = $state->PeekTo(']');
        return $peekTag == '[credit:' && $pt && strlen($pt) > 8 && strstr($pt, "\n") === false;
    }

    public function Parse($result, $parser, $state, $scope)
    {
        $index = $state->Index();

        if ($state->Next() != '[') {
            $state->Seek($index, true);
            return false;
        }

        $str = $state->ScanTo(']');

        if ($state->Next() != ']') {
            $state->Seek($index, true);
            return false;
        }

        $credit = new WikiRevisionCredit();
        $credit->type = WikiRevisionCredit::CREDIT;

        $sections = explode('|', $str);
        foreach ($sections as $section) {
            $spl = explode(':', $section, 2);
            $key = $spl[0];
            $val = $spl[1];
            switch ($key) {
                case 'credit';
                    $credit->description = $val;
                    break;
                case 'user';
                    $credit->user_id = intval($val);
                    break;
                case 'name';
                    $credit->name = $val;
                    break;
                case 'url';
                    $credit->url = $val;
                    break;
            }
        }
        $state->SkipWhitespace();
        $result->AddMeta('WikiCredit', $credit);
        return '';
    }
}
