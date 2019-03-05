<?php

namespace App\Helpers\BBCode\Processors;
 
class AutoLinkingProcessor extends Processor {

    function Process($result, $text, $scope) {

        $text = preg_replace(array(
            '%(?<=^|\s)(https?://[^\]["\s]+)(?=\s|$)%im',
            '/(?<=^|\s)([^\]["\s@]+@[^\]["\s@]+\.[^\]["\s@]+)(?=\s|$)/im'
        ), array(
            '<a href="\1">\1</a>',
            '<a href="mailto:\1">\1</a>'
        ),$text);

        return $text;
    }

}
