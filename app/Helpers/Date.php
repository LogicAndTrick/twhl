<?php namespace App\Helpers;

class Date
{
    public static function Now()
    {
        return gmdate('Y-m-d H:i:s');
    }

    public static function AddSeconds($date, $seconds)
    {
        return gmdate('Y-m-d H:i:s', strtotime($date) + $seconds);
    }

    public static function Format($date, $format)
    {
        return gmdate($format, strtotime($date));
    }

    public static function Timezone($date, $timezone = 0)
    {
        $time = strtotime($date);
        $time += $timezone * 3600;
        return gmdate('Y-m-d H:i:s', $time);
    }

    public static function DiffDays($date1, $date2)
    {
        return abs(floor((strtotime($date1) - strtotime($date2)) / (60 * 60 * 24)));
    }

    public static function DiffMinutes($date1, $date2)
    {
        return abs(floor((strtotime($date1) - strtotime($date2)) / (60)));
    }

    public static function NowBetween($start, $end)
    {
        return Date::Between(Date::Now(), $start, $end);
    }

    public static function Between($date, $start, $end)
    {
        $d = strtotime($date);
        return strtotime($start) < $d && strtotime($end) > $d;
    }

    public static function TimeAgo($date)
    {
        $time = strtotime($date);
        $now = strtotime(Date::Now());

        // http://css-tricks.com/snippets/php/time-ago-function/
        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths = array(60, 60, 24, 7, 4.35, 12, 10);

        $difference     = $now - $time;
        for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
            $difference /= $lengths[$j];
        }
        $difference = round($difference);
        $period = $periods[$j] . ($difference != 1 ? 's' : '');

        return "$difference $period ago";
    }
}