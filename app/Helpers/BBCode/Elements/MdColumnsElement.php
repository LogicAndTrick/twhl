<?php

namespace App\Helpers\BBCode\Elements;
 
class MdColumnsElement extends Element {

    public $parser;
    public $column_definitions;
    public $columns;

    function __construct()
    {

    }

    function Matches($lines)
    {
        $value = $lines->Value();
        return substr($value, 0, 10) == '%%columns=';
    }

    function Consume($parser, $lines)
    {
        $current = $lines->Current();

        $meta = substr($lines->Value(), 10);
        $col_defs = explode(':', $meta);
        $total = 0;

        foreach ($col_defs as $d) {
            $def = intval($d);
            if (is_integer($def) && $def > 0) {
                $total += $def;
            } else {
                $lines->SetCurrent($current);
                return null;
            }
        }

        if ($total != 12) {
            $lines->SetCurrent($current);
            return null;
        }

        $num = count($col_defs);

        $found = false;
        $arr = [];
        $cols = [];
        while ($lines->Next() && $num > 0) {
            $value = rtrim($lines->Value());
            if ($value == '%%') {
                $num--;
                $cols[] = implode("\n", $arr);
                $arr = [];
            } else {
                $arr[] = $value;
            }
            if ($num == 0) break;
        }

        if ($num != 0 || count($arr) > 0) {
            $lines->SetCurrent($current);
            return null;
        }

        $el = new MdColumnsElement();
        $el->parser = $parser;
        $el->columns = $cols;
        $el->column_definitions = $col_defs;
        return $el;
    }

    function Parse($result, $scope)
    {
        $str = '<div class="row">';
        for ($i = 0; $i < count($this->column_definitions); $i++) {
            $str .= '<div class="col-md-'.intval($this->column_definitions[$i]).'">'
                  . $this->parser->ParseBlock($result, $this->columns[$i], $scope)
                  . '</div>';
        }
        $str .= '</div>';
        return $str;
    }
}
