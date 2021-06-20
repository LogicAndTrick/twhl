<?php

namespace App\Helpers\BBCode;

use App\Helpers\BBCode\Elements\DefaultElement;
use Illuminate\Support\Str;

/**
 * Class Parser
 * @package App\Helpers\BBCode
 */
class Parser
{
    public $elements = array();
    public $tags = array();
    public $post_processors = array();
    public $text_processors = array();

    function __construct($config = array())
    {
        $this->LoadConfiguration($config);
    }

    function LoadConfiguration($config) {
        if (array_key_exists('elements', $config) && is_array($config['elements'])) {
            foreach ($config['elements'] as $cfg) {
                if (!array_key_exists('class', $cfg)) continue;
                $cls = $cfg['class'];
                $e = new $cls($cfg);
                foreach ($cfg as $k => $v) {
                    if ($k == 'class') continue;
                    $e->$k = $v;
                }
                $this->elements[] = $e;
            }
        }
        if (array_key_exists('tags', $config) && is_array($config['tags'])) {
            foreach ($config['tags'] as $cfg) {
                if (!array_key_exists('class', $cfg)) continue;
                $cls = $cfg['class'];
                $e = new $cls($cfg);
                foreach ($cfg as $k => $v) {
                    if ($k == 'class') continue;
                    $e->$k = $v;
                }
                $this->tags[] = $e;
            }
        }
        if (array_key_exists('post_processors', $config) && is_array($config['post_processors'])) {
            foreach ($config['post_processors'] as $cfg) {
                if (!array_key_exists('class', $cfg)) continue;
                $cls = $cfg['class'];
                $e = new $cls($cfg);
                foreach ($cfg as $k => $v) {
                    if ($k == 'class') continue;
                    $e->$k = $v;
                }
                $this->post_processors[] = $e;
            }
        }
        if (array_key_exists('text_processors', $config) && is_array($config['text_processors'])) {
            foreach ($config['text_processors'] as $cfg) {
                if (!array_key_exists('class', $cfg)) continue;
                $cls = $cfg['class'];
                $e = new $cls($cfg);
                foreach ($cfg as $k => $v) {
                    if ($k == 'class') continue;
                    $e->$k = $v;
                }
                $this->text_processors[] = $e;
            }
        }
    }

    public function ParseExcerpt($text, $length = 200, $scope = 'excerpt') {
        $len = Str::length($text);
        if ($len > $length) {
            $text = Str::substr($text, 0, $length);
        }
        $parsed = $this->Parse($text, $scope);
        if ($len > $length) {
            $parsed .= '...';
        }
        return $parsed;
    }

    public function Parse($text, $scope = '') {
        $result = $this->ParseResult($text, $scope);
        return $result->text;
    }

    public function ParseResult($text, $scope = '') {
        $result = new ParseResult();
        $result->text = $this->ParseBlock($result, $text, $scope);
        return $result;
    }

    public function ParseBlock($result, $text, $scope = '') {
        $text = str_replace("\r", "", $text);
        $elements = $this->SplitElements($text, $scope);
        $str = '';
        foreach ($elements as $e) {
            $str .= $e->Parse($result, $scope) . "\n";
        }
        return trim($str);
    }

    public function SplitElements($text, $scope) {
        $lines = new Lines($text);
        $elements = array();
        $default = array();
        $inscope = array();

        // Only use the elements in scope
        foreach ($this->elements as $e) {
            if ($e->InScope($scope)) $inscope[] = $e;
        }

        while ($lines->Next())
        {
            $matched = false;
            foreach ($inscope as $e)
            {
                if ($e->Matches($lines))
                {
                    $con = $e->Consume($this, $lines);
                    if ($con)
                    {
                        // We've got a new element match - add the default element first
                        if (count($default) > 0) $elements[] = new DefaultElement($this, $default);
                        $default = array();

                        // Add the new element
                        $elements[] = $con;
                        $matched = true;

                        break;
                    }
                }
            }
            if (!$matched) $default[] = $lines->Value();
        }
        if (count($default) > 0) $elements[] = new DefaultElement($this, $default);
        return $elements;
    }

    public function CleanString($text) {
        $text = htmlspecialchars($text);
        return $text;
    }

    public function CleanUrl($text) {
        $text = str_replace(' ', '%20', $text);
        return $text;
    }

    public function PostProcessString($result, $text, $scope) {
        $str = $text;
        foreach ($this->post_processors as $pp) {
            if ($pp->InScope($scope)) {
                $str = $pp->Process($result, $str, $scope);
            }
        }
        return $str;
    }

    public function ParseBBCode($result, $text, $scope, $type) {
        $state = new State($text);
        $str = '';

        $inscope = array();

        // Only use the tags in scope
        foreach ($this->tags as $t) {
            if ($t->InScope($scope, $type)) $inscope[] = $t;
        }

        while (!$state->Done())
        {
            $str .= $this->ParsePlainText($result, $state->ScanTo('['), $scope, $type);
            $token = $state->GetToken();
            $found = false;
            foreach ($inscope as $t) {
                if ($t->Matches($state, $token)) {
                    $parsed = $t->Parse($result, $this, $state, $scope);
                    if ($parsed !== false && $parsed !== null) {
                        $str .= $parsed;
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) $str .= $this->ParsePlainText($result, $state->Next(), $scope, $type);
        }

        $str = $this->PostProcessString($result, $str, $scope);
        return $str;
    }

    public function ParsePlainText($result, $text, $scope, $type) {
        $str = $text;
        foreach ($this->text_processors as $pp) {
            if ($pp->InScope($scope)) {
                $str = $pp->Process($result, $str, $scope);
            }
        }
        return $str;
    }
}