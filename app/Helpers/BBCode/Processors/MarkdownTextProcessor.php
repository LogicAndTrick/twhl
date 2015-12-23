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

        // Bold
        $text = preg_replace('/\*([.,;\'"]*?\b[^<>\r\n]*?\b[.,;\'"]*?)\*/si', '<strong>$1</strong>', $text);

        // Italics
        $text = preg_replace('%/([.,;\'"]*?\b[^<>\r\n]*?\b[.,;\'"]*?)/%si', '<em>$1</em>', $text);

        // Underline
        $text = preg_replace('/\b_([^<>\r\n]*?)_\b/si', '<span class="underline">$1</span>', $text);

        // Strikethrough
        $text = preg_replace('/~([.,;\'"]*?\b[^<>\r\n]*?\b[.,;\'"]*?)~/si', '<span class="strikethrough">$1</span>', $text);

        // Code
        $text = preg_replace('/`([.,;\'"]*?\b[^<>]*?\b[.,;\'"]*?)`/si', '<code>$1</code>', $text);

        return $text;
    }

}
