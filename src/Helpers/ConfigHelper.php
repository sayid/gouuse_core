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
    public static function getConfig($option_name, $dir = "", $lang_name = "zh_cn")
    {
        if ($lang_name == "" && defined('DEFAULT_LANG_NAME')) {
            $lang_name = DEFAULT_LANG_NAME;
        }
        if ($lang_name) {
            $cache_key = "x_system_get_options_".$option_name."_".$dir."_".$lang_name;
        } else {
            $cache_key = "x_system_get_options_".$option_name."_".$dir;
        }
    
        $cache_data = Cache::get($cache_key);
    
        if (!$cache_data) {
        	
            $sub_dir = "";
    
            $dir = str_replace(["//",".."], "",  $dir);
            
            $dir = str_replace(".", "/", $dir);
            
            $array_file = array();
    
            $array_option_name = explode("_", $option_name);
            $option_name_first = array_shift($array_option_name);
            $option_name_end = join("_", $array_option_name);
            $root_path = substr(__DIR__, 0, -12);
            $root_path .  "/resources/".$dir."/".$option_name.".php";
            return require_once $root_path .  "/resources/options/".$dir."/".$option_name.".php";
        }
        
        return $cache_data;
    }
}
