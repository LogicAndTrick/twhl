<?php

namespace App\Helpers\BBCode\Processors;
 
class MarkdownTextProcessor extends Processor {

    function Process($result, $text, $scope) {

        /*
         * Like everything else here, this isn't exactly markdown, but it's close.
         * _underline_
         * /italics/
         * *bold*
         * ~strikethrough~
         * `code`
         * Very simple rules: no nesting, no newlines, must start/end on a word boundary
         */

        // pre-condition: start of a line OR one of: !?^()+=[]{}"'<>,. OR whitespace
        $pre = '%(?<=^|[!\^()+=\[\]{}"\'<>?,.\s])';

        // first and last character is NOT whitespace. everything else is fine except for <> or newlines
        $mid = '([^<>\r\n\s][^<>\r\n]*?[^<>\r\n\s])';

        // post-condition: end of a line OR one of: !?^()+=[]{}"'<>,. OR whitespace
        $post = '(?=[!\^()+=\[\]{}"\'<>?,.\s]|$)%imu';

        // Bold
        $text = preg_replace("{$pre}\*{$mid}\*{$post}", '<strong>$1</strong>', $text);

        // Italics
        $text = preg_replace("{$pre}/{$mid}/{$post}", '<em>$1</em>', $text);

        // Underline
        $text = preg_replace("{$pre}_{$mid}_{$post}", '<span class="underline">$1</span>', $text);

        // Strikethrough
        $text = preg_replace("{$pre}-{$mid}-{$post}", '<span class="strikethrough">$1</span>', $text);

        // Code
        $text = preg_replace("{$pre}`{$mid}`{$post}", '<code>$1</code>', $text);

        return $text;
    }

}
