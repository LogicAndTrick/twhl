<?php

namespace App\Helpers\BBCode;
 
class ParseResult
{
    public $text;
    public $meta = array();

    /**
     * @param $key string
     * @param $value mixed
     */
    public function AddMeta($key, $value) {
        if (!array_key_exists($key, $this->meta)) $this->meta[$key] = [];
        if (array_search($value, $this->meta[$key]) === false) $this->meta[$key][] = $value;
    }

    /**
     * @param $key string
     * @return array
     */
    public function GetMeta($key) {
        return array_key_exists($key, $this->meta) ? $this->meta[$key] : array();
    }
}
