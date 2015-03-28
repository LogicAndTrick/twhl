<?php

namespace App\Helpers\BBCode;
 
class ParseResult
{
    public $text;
    public $meta = array();

    public function AddMeta($key, $value) {
        if (!array_key_exists($key, $this->meta)) $this->meta[$key] = [];
        $this->meta[$key][] = $value;
    }

    public function GetMeta($key) {
        return array_key_exists($key, $this->meta) ? $this->meta[$key] : array();
    }
}
