<?php

namespace App\Helpers\BBCode\Tags;
 
class VaultEmbedTag extends LinkTag {

    function __construct()
    {
        $this->token = false;
        $this->element = 'div';
        $this->main_option = 'id';
        $this->options = array('id');
    }

    public function Matches($state, $token)
    {
        $peekTag = $state->Peek(7);
        $pt = $state->PeekTo(']');
        return $peekTag == '[vault:' && $pt && strlen($pt) > 7 && strstr($pt, "\n") === false;
    }

    public function Parse($result, $parser, $state, $scope)
    {
        $index = $state->Index();

        if ($state->ScanTo(':') != '[vault' || $state->Next() != ':') {
            $state->Seek($index, true);
            return false;
        }
        $str = $state->ScanTo(']');
        if ($state->Next() != ']') {
            $state->Seek($index, true);
            return false;
        }
        if (is_numeric($str)) {
        	$id = intval($str);

            $classes = ['embedded', 'vault'];
            if ($this->element_class) $classes[] = $this->element_class;

            $state->SkipWhitespace();

            $impl = implode(' ', $classes);
            return "<div class='{$impl}'>"
                        . "<div class='embed-container'>"
                            . "<div class='embed-content'>"
                                . "<div class='uninitialised' data-embed-type='vault' data-vault-id='$id'>Loading embedded content: Vault Item #$id</div>"
                            . "</div>"
                        . "</div>"
                    . "</div>";

        } else {
            $state->Seek($index, true);
            return false;
        }
    }
}
