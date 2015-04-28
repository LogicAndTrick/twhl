<?php

if (!function_exists('tag'))
{
    function tag($type, $arguments, $content) {
        $str = '<'.$type;
        foreach ($arguments as $a) {
            if (is_array($a)) {
                foreach ($a as $key => $val) {
                    $str .= ' ' . $key . '="' . $val . '"';
                }
            }
        }
        if ($content) $str .= '>' . $content . '</' . $type . '>';
        else $str .= ' />';
        return $str;
    }
}

if (!function_exists('act'))
{
    function act($controller, $action) {

        $mappings = array(
            'auth' => 'Auth\AuthController',
            'password' => 'Auth\PasswordController',

            'forum' => 'Forum\ForumController',
            'thread' => 'Forum\ThreadController',
            'post' => 'Forum\PostController',

            'home' => 'HomeController',

            'wiki' => 'Wiki\WikiController',
            'vault' => 'Vault\VaultController',
            'comment' => 'Comments\CommentController',
            'news' => 'News\NewsController',

            'api' => 'Api\ApiController'
        );

        if (!array_key_exists($controller, $mappings)) throw new Exception('Undefined action mapping: ' . $controller);

        $action = preg_replace_callback('/(^|-)([a-z])/i', function ($g) { return strtoupper($g[2]); }, $action);

        $act = $action;
        if (substr($action, 0, 3) != 'Get' && substr($action, 0, 4) != 'Post') {
            $act = 'get'.strtoupper(substr($action, 0, 1)).substr($action, 1);
        } else {
            $act = strtolower($action[0]) . substr($action, 1);
        }
        $arr = array();
        for ($i = 2; $i < func_num_args(); $i++) {
            $arg = func_get_arg($i);
            if (!is_array($arg)) $arr[] = $arg;
        }
        return action($mappings[$controller].'@'.$act, $arr);
    }
}

if (!function_exists('actlink'))
{
    function actlink($text, $controller, $action) {
        $args = func_get_args();
        array_splice($args, 0, 1);
        $args[] = array('href' => call_user_func_array('act', $args));
        array_splice($args, 0, 2);
        return tag('a', $args, $text);
    }
}

if (!function_exists('permission'))
{
    function permission($permission = true) {
        $user = Auth::user();
        if ($permission === true && !$user) return false;
        if (is_string($permission) && (!$user || !$user->hasPermission($permission))) return false;
        return true;
    }
}

if (!function_exists('format_filesize'))
{
    function format_filesize($bytes)
    {
        if ($bytes < 1024) return $bytes . 'b';
        $kbytes = $bytes / 1024;
        if ($kbytes < 1024) return round($kbytes, 2) . 'kb';
        $mbytes = $kbytes / 1024;
        if ($mbytes < 1024) return round($mbytes, 2) . 'mb';
        $gbytes = $mbytes / 1024;
        if ($gbytes < 1024) return round($gbytes, 2) . 'gb';
        $tbytes = $gbytes / 1024;
        if ($tbytes < 1024) return round($tbytes, 2) . 'tb';
        $pbytes = $tbytes / 1024;
        return round($pbytes, 2) . 'pb';
    }
}