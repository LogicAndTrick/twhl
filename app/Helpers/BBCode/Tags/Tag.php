<?php

namespace App\Helpers\BBCode\Tags;

use App\Helpers\BBCode\Parser;
use App\Helpers\BBCode\State;

class Tag
{
    public $token;
    public $element;
    public $element_class;
    public $main_option = null;
    public $options = array();
    public $block = false;
    public $nested = false;


    public function Matches($state, $token)
    {
        return strtolower($token) == $this->token;
    }

    /**
     * @param $result
     * @param $parser Parser
     * @param $state State
     * @param $scope
     * @return mixed
     */
    public function Parse($result, $parser, $state, $scope)
    {
        $index = $state->Index();
        $tokenLength = strlen($this->token);

        $state->Seek($tokenLength + 1, false);
        $optionsString = trim($state->ScanTo(']'));
        if ($state->Next() != ']') {
            $state->Seek($index, true);
            return false;
        }
        $options = array();
        if (strlen($optionsString) > 0) {
            if ($optionsString[0] == '=') $optionsString = $this->main_option . $optionsString;
            preg_match_all('/(?=\s|^)\s*([^ ]+?)=([^\s]*)\b(?!=)/sim', $optionsString, $res, PREG_SET_ORDER);
            for ($i = 0; $i < count($res); $i++) {
                $name = trim($res[$i][1]);
                $value = trim($res[$i][2]);
                $options[$name] = $value;
            }
        }

        if ($this->nested) {
            $stack = 1;
            $text = '';
            while (!$state->Done()) {
                $text .= $state->ScanTo('[');
                $tok = $state->GetToken();
                if ($tok == $this->token) $stack++;
                if ($tok == '/' . $this->token && $state->Peek($tokenLength + 3) == '[/' . $this->token . ']') $stack--;
                if ($stack == 0) {
                    $state->Seek(strlen($this->token) + 3, false);
                    if (!$this->Validate($options, $text)) break;
                    return $this->FormatResult($result, $parser, $state, $scope, $options, $text);
                }
                $text .= $state->Next();
            }
            $state->Seek($index, true);
            return false;
        } else {
            $text = $state->ScanTo('[/' . $this->token . ']');
            if ($state->Peek($tokenLength + 3) == '[/' . $this->token . ']' && $this->Validate($options, $text)) {
                $state->Seek(strlen($this->token) + 3, false);
                return $this->FormatResult($result, $parser, $state, $scope, $options, $text);
            } else {
                $state->Seek($index, true);
                return false;
            }
        }
    }

    public function Validate($options, $text)
    {
        return true;
    }

    public function FormatResult($result, $parser, $state, $scope, $options, $text)
    {
        $str = '<' . $this->element;
        if ($this->element_class) $str .= ' class="' . $this->element_class . '"';
        $str .= '>';
        $str .= $parser->ParseBBCode($result, $text, $scope, $this->block ? 'block' : 'inline');
        $str .= '</' . $this->element . '>';
        return $str;
    }

    public $scopes = array();

    public function InScope($scope, $type)
    {
        if ($this->block && $type != 'block') return false;
        return !$scope || $scope == '' || array_search($scope, $this->scopes) !== false;
    }
}