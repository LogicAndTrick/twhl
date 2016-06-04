<?php

namespace App\Helpers\BBCode\Elements;
 
class MdPanelElement extends Element {

    public $parser;
    public $text;
    public $meta;
    public $title;

    function __construct()
    {

    }

    function Matches($lines)
    {
        $value = $lines->Value();
        return substr($value, 0, 3) == '~~~';
    }

    function Consume($parser, $lines)
    {
        $current = $lines->Current();

        $meta = substr($lines->Value(), 3);
        $title = '';

        $found = false;
        $arr = array();
        while ($lines->Next()) {
            $value = rtrim($lines->Value());
            if ($value == '~~~') {
                $found = true;
                break;
            }
            if (strlen($value) > 0 && $value[0] == ':') $title = trim(substr($value, 1));
            else $arr[] = $value;
        }

        if (!$found) {
            $lines->SetCurrent($current);
            return null;
        }

        $el = new MdPanelElement();
        $el->parser = $parser;
        $el->text = implode("\n", $arr);
        $el->meta = strtolower(trim($meta));
        $el->title = $title;
        return $el;
    }

    function Parse($result, $scope)
    {
        $cls = '';
        if ($this->meta == 'message') $cls = 'panel panel-success';
        else if ($this->meta == 'info') $cls = 'panel panel-info';
        else if ($this->meta == 'warning') $cls = 'panel panel-warning';
        else if ($this->meta == 'error') $cls = 'panel panel-danger';
        else $cls = 'panel panel-default';
        return "<div class=\"embed-panel $cls\">" . ($this->title != '' ? "<div class=\"panel-heading\">{$this->parser->CleanString($this->title)}</div>" : '') . "<div class=\"panel-body\">" . $this->parser->ParseBlock($result, $this->text, $scope) . '</div></div>';
    }
}
