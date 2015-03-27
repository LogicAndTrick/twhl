<?php

namespace App\Helpers\BBCode\Tags;
 
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
                // add image reference
                $src = '/wiki/embed/' . $image;
            }
            return '<img src="' . $src . '" />';
            // --- todo: classes, titles, captions, etc
            $text = isset($regs[3]) && $regs[3] ? $regs[3] : $page;
            $page = str_ireplace(' ', '_', $page);
            $page = preg_replace('%[^a-z0-9-_()]%si', '', $page);
            // todo generate url properly
            $url = '/wiki/' . $page . ($bkmk ? '#' . $bkmk : '');
            return '<a href="' . $url . '">' . $parser->CleanString($text) . '</a>';
        } else {
            return false;
        }
    }

    public function Validate($options, $text)
    {
        $url = $text;
        if (array_key_exists('url', $options)) $url = $options['url'];
        return stristr($url, '<script') === false && preg_match('%^([a-z]{2,10}://)?([^]"\n ]+?)$%i', $url);
    }
}
