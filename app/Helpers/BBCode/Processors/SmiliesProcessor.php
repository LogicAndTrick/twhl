<?php

namespace App\Helpers\BBCode\Processors;
 
class SmiliesProcessor extends Processor {

    function Process($result, $text, $scope) {

        // todo: smilies
        /*
         foreach ($smilies as $sm) {
             $search = '/(?<=\s|^)(' . preg_quote($sm['code']) . ')(?=\s|$)/im';
             $replace = '<img class="wiki-smilies" src="' . $sm['file'] . '" alt="' . $sm['alt'] . ' - ' . htmlspecialchars($sm['code']) . '" />';
             $text = preg_replace($search, $replace, $text);
         }
         */
        return $text;
    }

}

?> 