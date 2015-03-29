<?php

namespace App\Helpers\BBCode\Tags;
 
class QuickLinkTag extends LinkTag {

    function __construct()
    {
        $this->token = false;
        $this->element = 'a';
        $this->main_option = 'url';
        $this->options = array('url');
    }

    public function Matches($state, $token)
    {
        $pt = substr($state->PeekTo(']'), 1);
        return $pt && strstr($pt, "\n") === false
            && preg_match('%^([a-z]{2,10}://[^\]]*?)(?:\|([^\]]*?))?$%i', $pt);
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
        if (preg_match('%^([a-z]{2,10}://[^\]]*?)(?:\|([^\]]*?))?$%i', $str, $regs)) {
        	$url = $regs[1];
            $text = isset($regs[2]) && $regs[2] ? $regs[2] : $url;
            $options = ['url' => $url];
            if (!$this->Validate($options, $text)) return false;
            return '<a href="' . $parser->CleanUrl($url) . '">' . $parser->CleanString($text) . '</a>';
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
