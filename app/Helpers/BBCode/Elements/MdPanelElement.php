<?php

namespace App\Helpers\BBCode\Elements;
 
class MdPanelElement extends Element {

    public $parser;
    public $text;
    public $meta;

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

        $found = false;
        $arr = array();
        while ($lines->Next()) {
            $value = rtrim($lines->Value());
            if ($value == '~~~') {
                $found = true;
                break;
            }
            $arr[] = $value;
        }

        if (!$found) {
            $lines->SetCurrent($current);
            return null;
        }

        $el = new MdPanelElement();
        $el->parser = $parser;
        $el->text = implode("\n", $arr);
        $el->meta = strtolower(trim($meta));
        return $el;
    }

    function Parse($result, $scope)
    {
        $cls = 'well';
        if ($this->meta == 'message') $cls = 'alert alert-success';
        else if ($this->meta == 'info') $cls = 'alert alert-info';
        else if ($this->meta == 'warning') $cls = 'alert alert-warning';
        else if ($this->meta == 'error') $cls = 'alert alert-danger';
        return "<div class=\"$cls\">" . $this->parser->ParseBlock($result, $this->text, $scope) . '</div>';
    }
}
