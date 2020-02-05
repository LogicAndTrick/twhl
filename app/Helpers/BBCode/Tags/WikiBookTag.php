<?php

namespace App\Helpers\BBCode\Tags;

use App\Models\Wiki\WikiRevisionBook;

class WikiBookTag extends LinkTag {

    function __construct()
    {
        $this->token = false;
        $this->element = '';
    }

    public function Matches($state, $token)
    {
        $peekTag = $state->Peek(6);
        $pt = $state->PeekTo(']');
        return $peekTag == '[book:' && $pt && strlen($pt) > 5 && strstr($pt, "\n") === false;
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

        $book = new WikiRevisionBook();

        $sections = explode('|', $str);
        foreach ($sections as $section) {
            $spl = explode(':', $section, 2);
            $key = $spl[0];
            $val = $spl[1];
            switch ($key) {
                case 'book';
                    $book->book_name = $val;
                    break;
                case 'chapter';
                    $book->chapter_name = $val;
                    break;
                case 'chapternumber';
                    $book->chapter_number = intval($val);
                    break;
                case 'pagenumber';
                    $book->page_number = intval($val);
                    break;
            }
        }
        $result->AddMeta('WikiBook', $book);
        return '';
    }
}
