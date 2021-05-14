<?php

namespace App\Helpers\BBCode\Tags;

use App\Models\Wiki\WikiRevisionCredit;
use Illuminate\Support\Str;

class WikiArchiveTag extends LinkTag {

    function __construct()
    {
        $this->token = false;
        $this->element = '';
    }

    public function Matches($state, $token)
    {
        $peekTag = $state->Peek(9);
        $pt = $state->PeekTo(']');
        return $peekTag == '[archive:' && $pt && strlen($pt) > 9 && strstr($pt, "\n") === false;
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
        $credit->type = WikiRevisionCredit::ARCHIVE;

        $sections = explode('|', $str);
        foreach ($sections as $section) {
            $spl = explode(':', $section, 2);
            $key = $spl[0];
            $val = count($spl) > 1 ? $spl[1] : '';
            switch ($key) {
                case 'archive':
                    $credit->name = $val;
                    break;
                case 'description':
                    $credit->description = $val;
                    break;
                case 'url':
                    $credit->url = $val;
                    break;
                case 'wayback':
                    $credit->wayback_url = intval($val);
                    break;
                case 'full':
                    $credit->type = WikiRevisionCredit::FULL;
            }
        }
        if ($credit->wayback_url && $credit->url && !Str::startsWith($credit->wayback_url, ['http://', 'https://'])) {
            $credit->wayback_url = "https://web.archive.org/web/{$credit->wayback_url}/{$credit->url}";
        }
        $state->SkipWhitespace();
        $result->AddMeta('WikiCredit', $credit);
        return '';
    }
}
