<?php

namespace App\Helpers\BBCode\Processors;
 
class SmiliesProcessor extends Processor {

    public $smilies = array();

    private $initialised = false;
    private $search;
    private $replace;

    private function Initialise() {
        if ($this->initialised) return;
        $this->initialised = true;
        $s = [];
        $r = [];
        foreach ($this->smilies as $img => $seqs) {
            foreach ($seqs as $seq) {
                $s[] = '/(?<=^|\s|>)' . preg_quote($seq, '/') . '(?=\s|$|<)/im';
                $r[] = '<img class="smiley" src="' . asset("images/smilies/$img.png") . '" alt="' . htmlspecialchars($seq) . '" />';
            }
        }
        $this->search = $s;
        $this->replace = $r;
    }

    function Process($result, $text, $scope) {

        // todo: smilies
        /*
         foreach ($smilies as $sm) {
             $search = '/(?<=\s|^)(' . preg_quote($sm['code']) . ')(?=\s|$)/im';
             $replace = '<img class="wiki-smilies" src="' . $sm['file'] . '" alt="' . $sm['alt'] . ' - ' . htmlspecialchars($sm['code']) . '" />';
             $text = preg_replace($search, $replace, $text);
         }
         */

        $this->Initialise();

        $text = preg_replace_callback('%(<pre[^>]*>)(.*?)</pre>%si', function($g){ $e=base64_encode($g[2]); return "{$g[1]}{$e}</pre>"; }, $text);

        $text = preg_replace($this->search, $this->replace, $text);

        $text = preg_replace_callback('%(<pre[^>]*>)(.*?)</pre>%si', function($g){ $d=base64_decode($g[2]); return "{$g[1]}{$d}</pre>"; }, $text);

        return $text;
    }

}
