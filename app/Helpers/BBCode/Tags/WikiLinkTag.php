<?php

namespace App\Helpers\BBCode\Tags;
 
use App\Models\Wiki\WikiRevision;

class WikiLinkTag extends LinkTag {

    function __construct()
    {
        $this->token = false;
        $this->element = 'a';
        $this->main_option = 'url';
        $this->options = array('url');
    }

    public function Matches($state, $token)
    {
        $pt = $state->PeekTo(']]');
        return $pt && strlen($pt) > 1 && $pt[1] == '[' && strstr($pt, "\n") === false
            && preg_match('/([^\]]*?)(?:\|([^\]]*?))?/i', substr($pt, 2));
    }

    public function Parse($result, $parser, $state, $scope)
    {
        $index = $state->Index();

        if ($state->Next() != '[' || $state->Next() != '[') {
            $state->Seek($index, true);
            return false;
        }
        $str = $state->ScanTo(']]');
        if ($state->Next() != ']' || $state->Next() != ']') {
            $state->Seek($index, true);
            return false;
        }
        if (preg_match('/^([^\]]+?)(?:\|([^\]]*?))?$/i', $str, $regs)) {
        	$page = htmlspecialchars_decode($regs[1]);
            $text = isset($regs[2]) && $regs[2] ? $regs[2] : $page;
            $hash = '';
            if (str_contains($page, '#')) {
                $spl = explode('#', $page, 2);
                $page = $spl[0];
                $hash = '#' . preg_replace('%[^\da-z?/:@\-._~!$&\'()*+,;=]%i', '_', $spl[1]);
            }
            $result->AddMeta('WikiLink', $page);
            $url = act('wiki', 'page', WikiRevision::CreateSlug($page)) . $hash;
            return '<a href="' . $parser->CleanUrl($url) . '">' . $text . '</a>';
        } else {
            $state->Seek($index, true);
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
