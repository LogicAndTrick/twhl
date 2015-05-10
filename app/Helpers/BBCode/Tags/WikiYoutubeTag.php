<?php

namespace App\Helpers\BBCode\Tags;
 
class WikiYoutubeTag extends LinkTag {

    function __construct()
    {
        $this->token = false;
        $this->element = 'div';
        $this->main_option = 'id';
        $this->options = array('id');
    }

    public function Matches($state, $token)
    {
        $peekTag = $state->Peek(9);
        $pt = $state->PeekTo(']');
        return $peekTag == '[youtube:' && $pt && strlen($pt) > 9 && strstr($pt, "\n") === false;
    }

    public function Parse($result, $parser, $state, $scope)
    {
        $index = $state->Index();

        if ($state->ScanTo(':') != '[youtube' || $state->Next() != ':') {
            $state->Seek($index, true);
            return false;
        }
        $str = $state->ScanTo(']');
        if ($state->Next() != ']') {
            $state->Seek($index, true);
            return false;
        }
        if (preg_match('/^([^|\]]*?)(?:\|([^\]]*?))?$/i', $str, $regs)) {
        	$id = $regs[1];
            $params = isset($regs[2]) ? explode('|', trim($regs[2])) : [];

            if (!$this->ValidateId($id)) {
                $state->Seek($index, true);
                return false;
            }

            $caption = null;
            $classes = ['embedded', 'video'];
            if ($this->element_class) $classes[] = $this->element_class;
            foreach ($params as $p) {
                $l = strtolower($p);
                if ($this->IsClass($l)) $classes[] = $l;
                else $caption = trim($p);
            }
            if ($caption) $caption = $caption;
            $impl = implode(' ', $classes);
            $cap = $caption ? "<span class='caption'>" . $caption . '</span>' : '';
            return "<div class='{$impl}'>"
                        . "<div class='caption-panel'>"
                            . "<div class='video-container caption-body'>"
                                . "<div class='video-content'>"
                                    . "<div class='uninitialised' data-youtube-id='$id' style='background-image: url(\"https://i.ytimg.com/vi/{$id}/hqdefault.jpg\");'></div>"
                                . "</div>"
                            . "</div>"
                            . "$cap"
                        . "</div>"
                    . "</div>";

        } else {
            $state->Seek($index, true);
            return false;
        }
    }

    private function ValidateId($id)
    {
        return preg_match('%^[a-zA-Z0-9_-]{6,11}$%i', $id);
    }

    private $valid_classes = [ 'large', 'medium', 'small', 'left', 'right', 'center' ];

    private function IsClass($param) {
        return array_search($param, $this->valid_classes) !== false;
    }
}
