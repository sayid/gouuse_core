<?php
namespace GouuseCore\Helpers;

class DateHelper
{
    
    public static function showTime($datetime)
    {
        $time = $datetime;//strtotime($datetime);
        $time_day_start = strtotime(date('Y-m-d 00:00:00'));
        $minus_day = time() - $time_day_start;
        $minus = time() - $time;
        if ($minus < 60) {
            return "刚刚";
        }
        if ($minus < 3600) {
            return ceil($minus / 60)."分钟前";
        } elseif ($minus < $minus_day) {
            return "今天 ".date("H:i", $time);
        } elseif ($minus < 86400 + $minus_day) {
            return "昨天 ".date("H:i", $time);
        } elseif ($minus < 86400*2 + $minus_day) {
            return "前天 ".date("H:i", $time);
        } else {
            return date('Y-m-d H:i', $time);
        }
    }
    
    public static function microtime_float()
    {
    	list($usec, $sec) = explode(" ", microtime());
    	return ((float)$usec + (float)$sec);
    }
}
