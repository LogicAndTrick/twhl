<?php

namespace App\Helpers\BBCode\Elements;
 
class MdListElement extends Element {

    public $parser;
    public $lines;

    public $ul_tokens = array('*', '-');
    public $ol_tokens = array('#');

    function __construct()
    {

    }

    function IsUnsortedToken($char) {
        return array_search($char, $this->ul_tokens) !== false;
    }

    function IsSortedToken($char) {
        return array_search($char, $this->ol_tokens) !== false;
    }

    function IsListToken($char) {
        return $this->IsSortedToken($char) || $this->IsUnsortedToken($char);
    }

    function Matches($lines)
    {
        $value = trim($lines->Value());
        return strlen($value) > 0 && strpos($value, ' ') !== false && $this->IsListToken($value[0]);
    }

    function Consume($parser, $lines)
    {
        $arr = array();
        do {
            $value = trim($lines->Value());
            if (strlen($value) == 0 || strpos($value, ' ') === false || !$this->IsListToken($value[0])) {
                $lines->Back();
                break;
            }
            $arr[] = $value;
        } while ($lines->Next());
        $el = new MdListElement();
        $el->parser = $parser;
        $el->lines = $arr;
        return $el;
    }

    function CreateListTree($lines) {
        $current_type = null;
        $current_list = null;
        $tree = array();
        foreach ($lines as $line) {
            $type = $line[0];

            if ($this->IsSortedToken($type)) $type = '#';
            else if ($this->IsUnsortedToken($type)) $type = '*';
            else $type = '_';

            if ($type != $current_type) {
                $current_type = $type;
                if ($current_list != null) $tree[] = $current_list;
                $current_list = array('type' => $current_type, 'items' => array());
            }
            $text = $type == '_' ? $line : substr($line, 1);
            $current_list['items'][] = trim($text);
        }
        if ($current_list != null && count($current_list) > 0) $tree[] = $current_list;
        foreach ($tree as $key => $leaf) {
            if ($leaf['type'] == '_') continue;
            $tree[$key]['tree'] = $this->CreateListTree($leaf['items']);
            unset($tree[$key]['items']);
        }
        return $tree;
    }

    function PrintTreeRecursive($result, $type, $tree, $scope) {
        $tag = $type == '#' ? 'ol' : 'ul';
        $str = '<' . $tag . '>';
        $open = false;
        foreach ($tree as $leaf) {
            $type = $leaf['type'];
            if ($type == '_') {
                foreach ($leaf['items'] as $item) {
                    if ($open) $str .= '</li>';
                    $str .= '<li>' . $this->parser->ParseBBCode($result, $item, $scope, 'inline');
                    $open = true;
                }
            } else {
                if (!$open) $str .= '<li>';
                $open = true;
                $str .= $this->PrintTreeRecursive($result, $type, $leaf['tree'], $scope);
            }
        }
        if ($open) $str .= '</li>';
        $str .= '</' . $tag . '>';
        return $str;
    }

    function PrintTree($result, $tree, $scope) {
        $str = '';
        foreach ($tree as $leaf) {
            $str .= $this->PrintTreeRecursive($result, $leaf['type'], $leaf['tree'], $scope);
        }
        return $str;
    }

    function Parse($result, $scope)
    {
        $tree = $this->CreateListTree($this->lines);
        return $this->PrintTree($result, $tree, $scope);
        return '<blockquote>' . $this->parser->ParseBBCode($result, $this->text, $scope, 'block') . '</blockquote>';
    }
}
