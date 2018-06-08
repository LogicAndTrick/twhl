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
        if (preg_match('/^([^#\]]+?)(?:#([^\]]*?))?(?:\|([^\]]*?))?$/i', $str, $regs)) {
        	$page = htmlspecialchars_decode($regs[1]);
            $bkmk = isset($regs[2]) ? trim($regs[2]) : '';
            $text = isset($regs[3]) && $regs[3] ? $regs[3] : $page;
            $result->AddMeta('WikiLink', $page);
            $url = act('wiki', 'page', WikiRevision::CreateSlug($page)) . ($bkmk ? '#' . $bkmk : '');
            return '<a href="' . $parser->CleanUrl($url) . '">' . $text . '</a>';
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
