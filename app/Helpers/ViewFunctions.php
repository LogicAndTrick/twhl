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

            'home' => 'HomeController'
        );

        if (!array_key_exists($controller, $mappings)) throw new Exception('Undefined action mapping: ' . $controller);

        $act = 'get'.strtoupper(substr($action, 0, 1)).substr($action, 1);
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