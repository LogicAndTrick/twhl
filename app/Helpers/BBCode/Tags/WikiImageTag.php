<?php

namespace App\Helpers\BBCode\Tags;
 
use App\Models\Wiki\WikiRevision;

class WikiImageTag extends LinkTag {

    function __construct()
    {
        $this->token = false;
        $this->element = 'img';
        $this->main_option = 'url';
        $this->options = array('url');
    }

    public function Matches($state, $token)
    {
        $peekTag = $state->Peek(5);
        $pt = $state->PeekTo(']');
        return $peekTag == '[img:' && $pt && strlen($pt) > 5 && strstr($pt, "\n") === false;
    }

    public function Parse($result, $parser, $state, $scope)
    {
        $index = $state->Index();

        if ($state->ScanTo(':') != '[img' || $state->Next() != ':') {
            $state->Seek($index, true);
            return false;
        }
        $str = $state->ScanTo(']');
        if ($state->Next() != ']') {
            $state->Seek($index, true);
            return false;
        }
        if (preg_match('/^([^|\]]*?)(?:\|([^\]]*?))?$/i', $str, $regs)) {
        	$image = $regs[1];
            $params = isset($regs[2]) ? explode('|', trim($regs[2])) : [];
            $src = $image;
            if (strstr($image, '/') === false) {
                $result->AddMeta('WikiImage', $image);
                $src = url('/wiki/embed/' . WikiRevision::CreateSlug($image));
            }
            $url = null;
            $caption = null;
            $classes = ['embedded', 'image'];
            if ($this->element_class) $classes[] = $this->element_class;
            foreach ($params as $p) {
                $l = strtolower($p);
                if ($this->IsClass($l)) $classes[] = $l;
                else if (strlen($l) > 4 && substr($l, 0, 4) == 'url:') $url = trim(substr($p, 4));
                else $caption = trim($p);
            }
            if ($url && $this->ValidateUrl($url)) {
                if (!preg_match('%^[a-z]{2,10}://%i', $url)) {
                    $result->AddMeta('WikiLink', $url);
                    $url = url('/wiki/page/' . WikiRevision::CreateSlug($url));
                }
            } else {
                $url = '';
            }

            $el = 'span';

            // Non-inline images should eat any whitespace after them
            if (!array_search('inline', $classes)) {
                $state->SkipWhitespace();
                $el = 'div';
            }

            // The caption is already html escaped at this point (the default element cleans all strings)
            return ' <' . $el . ' class="' . implode(' ', $classes) . '"'.($caption ? " title='$caption'" : '').'>'
                 . ($url ? '<a href="' . $parser->CleanUrl($url) . '">' : '')
                 . '<span class="caption-panel">'
                 . '<img class="caption-body" src="' . $parser->CleanUrl($src) . '" alt="' . ($caption ? $caption : 'User posted image') . '" />'
                 . ($caption ? '<span class="caption">' . $caption . '</span>' : '')
                 . '</span>'
                 . ($url ? '</a>' : '')
                 . '</' . $el . '> ';
        } else {
            return false;
        }
    }

    private function ValidateUrl($url)
    {
        return stristr($url, '<script') === false && preg_match('%^([a-z]{2,10}://)?([^]"\n ]+?)$%i', $url);
    }

    private $valid_classes = [ 'large', 'medium', 'small', 'thumb', 'left', 'right', 'center', 'inline' ];

    private function IsClass($param) {
        return array_search($param, $this->valid_classes) !== false;
    }
}
