<?php

namespace App\Helpers\BBCode\Processors;
 
class NewLineProcessor extends Processor {

    function Process($result, $text, $scope) {

        $text = trim($text);

        $text = preg_replace('/\n{2,}/si', "\n\n", $text);
        $text = preg_replace('/\n/si', "<br>\n", $text);

        return $text;
    }

}

?> 