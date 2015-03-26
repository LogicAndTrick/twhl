<?php

namespace App\Helpers\BBCode\Elements;
 
class MdTableElement extends Element {

    public $parser;
    public $rows;

    function __construct()
    {

    }

    function Matches($lines)
    {
        $value = rtrim($lines->Value());
        return strlen($value) >= 2 && $value[0] == '|' && ($value[1] == '=' || $value[1] == '-');
    }

    function Consume($parser, $lines)
    {
        $arr = array();
        do {
            $value = rtrim($lines->Value());
            if (strlen($value) < 2 || $value[0] != '|' || ($value[1] != '=' && $value[1] != '-')) {
                $lines->Back();
                break;
            }
            $arr[] = [ 'cells' => $this->SplitTable(substr($value, 2)), 'type' => $value[1] == '=' ? 'th' : 'td' ];
        } while ($lines->Next());
        $el = new MdTableElement();
        $el->parser = $parser;
        $el->rows = $arr;
        return $el;
    }

    private function SplitTable($text)
    {
        $ret = array();
        $level = 0;
        $last = 0;
        $text = trim($text);
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++)
        {
            $c = substr($text, $i, 1);
            if ($c == '[') $level++;
            else if ($c == ']') $level--;
            else if (($c == '|' && $level == 0) || $i == $len - 1) {
                $ret[] = substr($text, $last, $i-$last);
                $last = $i + 1;
            }
        }
        return $ret;
    }

    function PrintTable($result, $scope) {
        $str = '<table class="table table-bordered">';
        $max_cells = 1;
        foreach ($this->rows as $row) {
            $max_cells = max($max_cells, count($row['cells']));
        }
        foreach ($this->rows as $row) {
            $str .= '<tr>';
            $count = count($row['cells']);
            for ($i = 0; $i < $max_cells; $i++) {
                $text = '';
                if ($i < $count) $text = $this->parser->ParseBBCode($result, $row['cells'][$i], $scope, 'inline');
                $str .= '<' . $row['type'] . '>' . $text . '</' . $row['type'] . '>';
            }
            $str .= '</tr>';
        }
        $str .= '</table>';
        return $str;
    }

    function Parse($result, $scope)
    {
        return $this->PrintTable($result, $scope);
    }
}
