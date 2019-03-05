<?php

namespace App\Helpers\BBCode\Processors;
 
class NewLineProcessor extends Processor {

    function Process($result, $text, $scope) {

        $text = trim($text);

        $text = preg_replace_callback('%(<pre[^>]*>)(.*?)</pre>%si', function($g){ $e=base64_encode($g[2]); return "{$g[1]}{$e}</pre>"; }, $text);

        // Get rid of all whitespace surrounding block elements
        $block = '(div|blockquote|pre)';
        $text = preg_replace('/(\s)+<'.$block.'/si', '<$2', $text);
        $text = preg_replace('%</'.$block.'>(\s)+%si', '</$1>', $text);


        $text = preg_replace('/\n{2,}/si', "\n\n", $text);
        $text = preg_replace('/\n/si', "<br>", $text);

        $text = preg_replace_callback('%(<pre[^>]*>)(.*?)</pre>%si', function($g){ $d=base64_decode($g[2]); return "{$g[1]}{$d}</pre>"; }, $text);

        return $text;
    }

}
