<?php

namespace App\Helpers\BBCode\Processors;
 
class AutoLinkingProcessor extends Processor {

    function Process($result, $text, $scope) {

        $text = ' ' . $text;
        $text = preg_replace(array(
            "#([]\n ])([a-z]{3,6})://([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^][\t \n]*)?[^][\".,?!;:\n\t ])#i",
            "#([]\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^\t \n]*)?[^][\".,?!;:\n\t ])#i",
            "#([]\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)?[\w]+[^][\".,?!;:\n\t ])#i"
        ), array(
            "\\1<a href=\"\\2://\\3.\\4\\5\">\\2://\\3.\\4\\5</a>",
            "\\1<a href=\"http://www.\\2.\\3\\4\">www.\\2.\\3\\4</a>",
            "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>"
        ),$text);
        $text = substr($text, 1);

        return $text;
    }

}

?> 