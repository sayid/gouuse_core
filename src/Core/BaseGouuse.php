<?php

namespace GouuseCore\Core;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

/**
 * @author zhangyubo
 *
 */
class BaseGouuse
{
	
	
	public function __construct($params = [])
	{
	}

    public function __set($key, $value)
    {
        if ($key == 'member_info' || $key == 'company_info') {
            $key = $key == 'member_info' ? 'GOUUSE_MEMBER_INFO' : 'GOUUSE_COMPANY_INFO';
            Config::set($key, $value);
        } else {
            Config::set($key, $value);
        }
        $this->{$key} = $value;
    }

	public function __get($class)
	{
		
		if ($class == 'member_info') {
			if (!defined('NEED_AUTH_CHECK')) {
				return null;
			}
            return Config::get('GOUUSE_MEMBER_INFO');
			if (defined('GOUUSE_MEMBER_INFO')) {
				return GOUUSE_MEMBER_INFO;
			}
			//define('GOUUSE_MEMBER_INFO', Auth::user());
			//return GOUUSE_MEMBER_INFO;
		} else if ($class == 'company_info') {
            return Config::get('GOUUSE_COMPANY_INFO');
			if (defined('GOUUSE_COMPANY_INFO')) {
				return GOUUSE_COMPANY_INFO;
			}
		}
		if (substr($class, strlen($class) - 3)=='Lib') {		
			if (class_exists("App\Libraries\\".$class)) {
				$class_load = "App\Libraries\\".$class;
				$class = strtolower($class);
			} else {
				$class_load = "GouuseCore\Libraries\\".$class;
				$class = strtolower($class);
			}
			
		} elseif (substr($class, strlen($class) - 5)=='Model') {
			$class = str_replace('_', '', $class);
			$class_load = "App\Models\\".$class;
			$class = strtolower($class);
		} elseif (substr($class, strlen($class) - 3)=='Rpc') {
			$class_load = "GouuseCore\Rpcs\\".$class;
			$class = strtolower($class);
		} else {
			return;
		}
		App::bindIf($class_load, null, true);
		$obj = App::make($class_load);
		return $obj;
	}
}
