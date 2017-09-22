<?php
namespace GouuseCore\Helpers;

use Illuminate\Support\Facades\Cache;

class ConfigHelper
{
    /**
     * 加载选项文件
     * @param  [type] $option_name [description]
     * @return [array]              [description]
     */
    public static function getConfig($option_name, $dir = "")
    {
        $cache_key = env('SERVICE_ID').":x_system_get_options_".$option_name."_".$dir;

        $cache_data = Cache::get($cache_key);

        if (!$cache_data) {

            $sub_dir = "";

            $option_name= str_replace(["//",".."], "",  $option_name);

            $option_name= str_replace(".", "/", $option_name);

            $dir = str_replace(["//",".."], "",  $dir);

            $dir = str_replace(".", "/", $dir);

            $array_file = array();

            $array_option_name = explode("_", $option_name);
            $option_name_first = array_shift($array_option_name);
            $option_name_end = join("_", $array_option_name);
            $root_path = substr(__DIR__, 0, -12);
            $path = $root_path .  "/resources/options/".$dir."/".$option_name.".php";
            if (!is_file($path)) {
                $path = $root_path .  "/resources/options/".$dir."/".$option_name.".inc.php";
            }
            return require_once $path;
        }

        return $cache_data;
    }
}
