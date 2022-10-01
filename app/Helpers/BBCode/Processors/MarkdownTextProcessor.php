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

        $break_chars = '[!\^()+=\[\]{}"\'<>?,.\s]';

        // pre-condition: start of a line OR one of: !?^()+=[]{}"'<>,. OR whitespace
        $pre = "%(?<=^|$break_chars)";

        // first and last character is NOT whitespace. everything else is fine except for <> or newlines
        $mid = '((?=[^<>\r\n\s])[^<>\r\n]+?(?<=[^<>\r\n\s]))';

        // post-condition: end of a line OR one of: !?^()+=[]{}"'<>,.:; OR whitespace
        $post = "(?=$break_chars|\$|[:;])%imu";

        // Code (base64 it to prevent additional processing)
        $text = preg_replace_callback("{$pre}`{$mid}`{$post}", function($g){ $e=base64_encode($g[1]); return "<code mdtpb64>{$e}</code>"; }, $text);

        // Bold
        $text = preg_replace("{$pre}\*{$mid}\*{$post}", '<strong>$1</strong>', $text);

        // Italics
        $text = preg_replace("{$pre}/{$mid}/{$post}", '<em>$1</em>', $text);

        // Underline
        $text = preg_replace("{$pre}_{$mid}_{$post}", '<span class="underline">$1</span>', $text);

        // Strikethrough
        $text = preg_replace("{$pre}~{$mid}~{$post}", '<span class="strikethrough">$1</span>', $text);

        // Un-base64 the md code blocks
        $text = preg_replace_callback('%<code mdtpb64>(.*?)</code>%si', function($g){ $d=base64_decode($g[1]); return "<code>{$d}</code>"; }, $text);

        return $text;
    }

}
