<?php

use Illuminate\Support\Str;

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
        $arr = array();
        for ($i = 2; $i < func_num_args(); $i++) {
            $arg = func_get_arg($i);
            if (!is_array($arg)) $arr[] = $arg;
            else foreach ($arg as $k => $v) $arr[$k] = $v;
        }
        return url($controller.'/'.$action, $arr);
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

if (!function_exists('egg'))
{
    function egg()
    {
        return \App\Helpers\Egg::GetEggClass();
    }
}

if (!function_exists('render_time'))
{
    function render_time()
    {
        return \App\Helpers\Egg::GetRenderTime();
    }
}

if (!function_exists('ordinal'))
{
    function ordinal($number, $include_number = true)
    {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        $n = $include_number ? $number : '';
        if (($number % 100) >= 11 && ($number%100) <= 13) return $n . 'th';
        else return $n . $ends[$number % 10];
    }
}

if (!function_exists('header_data')) {
    function header_data() {

        return \Illuminate\Support\Facades\Cache::remember('header_data', now()->addHour(), function () {

            $user = \App\Models\Accounts\User::orderBy('created_at', 'desc')->first();
            $comp = \App\Models\Competitions\Competition::orderBy('created_at', 'desc')->where('status_id', '!=', \App\Models\Competitions\CompetitionStatus::DRAFT)->first();

            return [
                'user' => $user,
                'competition' => $comp
            ];
        });

    }
}

function bbcode($text, $scope = '') {
    /** @var LogicAndTrick\WikiCodeParser\Parser $parser */
    $parser = app('bbcode');
    $result = $parser->ParseResult($text, $scope);
    return $result->ToHtml();
}

function bbcode_result($text, $scope = '') {
    /** @var LogicAndTrick\WikiCodeParser\Parser $parser */
    $parser = app('bbcode');
    $result = $parser->ParseResult($text, $scope);
    return $result;
}

function bbcode_excerpt($text, $length = 200, $scope = 'excerpt') {
    /** @var LogicAndTrick\WikiCodeParser\Parser $parser */
    $parser = app('bbcode');
    $len = Str::length($text);
    if ($len > $length) $text = Str::substr($text, 0, $length);
    $parsed = $parser->ParseResult($text, $scope)->ToHtml();
    if ($len > $length) $parsed .= '...';
    return $parsed;
}
