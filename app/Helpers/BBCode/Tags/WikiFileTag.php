<?php

namespace App\Helpers\BBCode\Tags;
 
use App\Models\Wiki\WikiRevision;

class WikiFileTag extends Tag {

    function __construct()
    {
        $this->token = false;
        $this->element = null;
        $this->main_option = null;
        $this->options = [];
    }

    private function getTag($state)
    {
        $peekTag = $state->Peek(6);
        $pt = $state->PeekTo(']');
        if ($peekTag == "[file:" && $pt && strlen($pt) > 6 && strstr($pt, "\n") === false) return 'file';
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
        $str = htmlspecialchars_decode($state->ScanTo(']'));
        if ($state->Next() != ']') {
            $state->Seek($index, true);
            return false;
        }

        if (preg_match('/^([^#\]]+?)(?:\|([^\]]*?))?$/i', $str, $regs)) {
        	$page = $regs[1];
            $result->AddMeta('WikiUpload', $page);
            $text = isset($regs[2]) && $regs[2] ? $regs[2] : htmlspecialchars($page);
            $slug = WikiRevision::CreateSlug($page);
            $url = act('wiki', 'embed', $slug);
            $info_url = act('wiki', 'embed-info', $slug);
            return '<span class="embedded-inline download" data-info="' . $info_url . '"><a href="' . $parser->CleanUrl($url) . '"><span class="fa fa-download"></span> ' . $text . '</a></span>';
        } else {
            $state->Seek($index, true);
            return false;
        }
    }
}
