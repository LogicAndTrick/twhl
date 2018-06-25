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

    private $tags = ['img', 'video', 'audio'];

    private function getTag($state)
    {
        foreach ($this->tags as $tag) {
            $peekTag = $state->Peek(2 + strlen($tag));
            $pt = $state->PeekTo(']');
            if ($peekTag == "[{$tag}:" && $pt && strlen($pt) > (2 + strlen($tag)) && strstr($pt, "\n") === false) return $tag;
        }
        return null;
    }

    public function Matches($state, $token)
    {
        $tag = $this->getTag($state);
        return $tag != null;
    }

    public function Parse($result, $parser, $state, $scope)
    {
        $index = $state->Index();

        $tag = $this->getTag($state);
        if ($state->ScanTo(':') != "[{$tag}" || $state->Next() != ':') {
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
                $image = htmlspecialchars_decode($image);
                $result->AddMeta('WikiUpload', $image);
                $src = act('wiki', 'embed', WikiRevision::CreateSlug($image));
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
            if ($tag == 'img' && $url && $this->ValidateUrl($url)) {
                if (!preg_match('%^[a-z]{2,10}://%i', $url)) {
                    $url = htmlspecialchars_decode($url);
                    $result->AddMeta('WikiLink', $url);
                    $url = act('wiki', 'page', WikiRevision::CreateSlug($url));
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

            return ' <' . $el . ' class="' . implode(' ', $classes) . '"'.($caption ? ' title="'. htmlspecialchars($caption) . '"' : '').'>'
                 . ($url ? '<a href="' . $parser->CleanUrl($url) . '">' : '')
                 . '<span class="caption-panel">'
                 . $this->getEmbedObject($tag, $parser, $src, $caption)
                 . ($caption ? '<span class="caption">' . htmlspecialchars($caption) . '</span>' : '')
                 . '</span>'
                 . ($url ? '</a>' : '')
                 . '</' . $el . '> ';
        } else {
            return false;
        }
    }

    private function getEmbedObject($tag, $parser, $url, $caption)
    {
        switch ($tag) {
            case 'img':
                return '<img class="caption-body" src="' . $parser->CleanUrl($url) . '" alt="' . ($caption ? htmlspecialchars($caption) : 'User posted image') . '" />';
            case 'video':
            case 'audio':
                return "<$tag class=\"caption-body\" src=\"$url\" controls>Your browser doesn't support embedded $tag.</$tag>";
        }
        return '';
    }

    private function ValidateUrl($url)
    {
        return stristr($url, '<script') === false; // && preg_match('%^([a-z]{2,10}://)?([^]"\n ]+?)$%i', $url);
    }

    private $valid_classes = [ 'large', 'medium', 'small', 'thumb', 'left', 'right', 'center', 'inline' ];

    private function IsClass($param) {
        return array_search($param, $this->valid_classes) !== false;
    }
}
