<?php

namespace GouuseCore\Core;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;

/**
 * @author zhangyubo
 *
 */
class BaseGouuse
{
	
	
	public function __construct($params = [])
	{
	}
	
	public function __get($class)
	{
		
		if ($class == 'member_info') {
			if (!defined('NEED_AUTH_CHECK')) {
				return null;
			}
			if (defined('GOUUSE_MEMBER_INFO')) {
				return GOUUSE_MEMBER_INFO;
			}
			//define('GOUUSE_MEMBER_INFO', Auth::user());
			//return GOUUSE_MEMBER_INFO;
		} else if ($class == 'company_info') {
			if (defined('GOUUSE_COMPANY_INFO')) {
				return GOUUSE_COMPANY_INFO;
			}
		}
		
		if (substr($class, strlen($class) - 3)=='Lib') {
			if (class_exists("GouuseCore\Libraries\\".$class)) {
				$class_load = "GouuseCore\Libraries\\".$class;
				$class = strtolower($class);
			} else {
				$class_load = "App\Libraries\\".$class;
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
