<?php
namespace GouuseCore\Helpers;

use Illuminate\Support\Facades\Cache;

class OptionHelper
{
    /**
     * 加载选项文件
    * @param  [type] $option_name [description]
    * @return [array]              [description]
    */
    public static function getOption($option_name, $dir = "options", $lang_name = "zh_cn")
    {
        if ($lang_name == "" && defined('DEFAULT_LANG_NAME')) {
            $lang_name = DEFAULT_LANG_NAME;
        }
        if ($lang_name) {
            $cache_key = 'option:'.__FUNCTION__.$option_name."_".$dir."_".$lang_name;
        } else {
        	$cache_key = 'option:'.__FUNCTION__.$option_name."_".$dir;
        }
    
        $cache_data = Cache::get($cache_key);
    
        if (!$cache_data) {
            $sub_dir = "";
    
            $separate_point=strpos($dir, '/');
            if ($separate_point !== false) {
                $sub_dir = preg_replace("/(.*?)\/(.*)/is", "$2", $dir);
                $dir = substr($dir, 0, $separate_point);
            }
            if ($sub_dir != "" && substr($sub_dir, -1)!="/") {
                $sub_dir = $sub_dir."/";
            }
    
    
            $array_file = array();
    
            $array_option_name = explode("_", $option_name);
            $option_name_first = array_shift($array_option_name);
            $option_name_end = join("_", $array_option_name);
			$root_path = ROOT_PATH;
            if ($lang_name && $dir == "options") {//options目录考虑语言包
                if ($option_name_end != "") {
                    $array_file[] = $root_path . "/resources/".$dir."/".$lang_name.
                    "/".$sub_dir.$option_name_first."/".$option_name_end.".inc.php";
                }
                $array_file[] = $root_path . "/resources/".$dir."/".$lang_name.
                "/".$sub_dir.$option_name_first."/".$option_name.".inc.php";
                $array_file[] = $root_path . "/resources/".$dir."/".$lang_name.
                "/".$sub_dir.$option_name.".inc.php";
            }
            if ($option_name_end != "") {
                $array_file[]= $root_path .  "/resources/".$dir."/".
                $sub_dir.$option_name_first."/".$option_name_end.".inc.php";
            }
            $array_file[]=$root_path .  "/resources/".$dir."/".$option_name_first."/".$option_name.".inc.php";
            $array_file[]= $root_path .  "/resources/".$dir."/".$option_name.".inc.php";
            foreach ($array_file as $file) {
                if (is_file($file) == true) {
                    include($file);
                    Cache::put($cache_key, $config, 3600);
                    return $config;
                } else {
                    continue;
                }
            }
    
            if (env('APP_DEBUG') == 'true') {
                echo $file." is empty!";
            } else {
                trigger_error("File:".$option_name." is not exists!", E_USER_ERROR);
            }
            exit();
        }
        return $cache_data;
    }
}
