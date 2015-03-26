<?php

namespace App\Helpers\BBCode;

class Lines
{
    public $lines;
    public $length;
    public $index;

    function __construct($text)
    {
        $this->lines = explode("\n", $text);
        $this->length = count($this->lines);
        $this->index = -1;
    }

    public function Back()
    {
        $this->index--;
    }

    public function Next()
    {
        $this->index++;
        return $this->index < $this->length;
    }

    public function Value()
    {
        return $this->lines[$this->index];
    }
}
