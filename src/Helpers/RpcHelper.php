<?php
namespace GouuseCore\Helpers;

use Illuminate\Support\Facades\App;

class RpcHelper
{
	
	public static function load($service_name, $class = 'Rpc')
	{
		if (substr($class, strlen($class) - 3)=='Lib') {
			$class_load = '\GouuseCore\Rpcs\\'.$service_name.'\Libraries\\'.$class;
		} elseif (substr($class, strlen($class) - 5)=='Model') {
			$class_load = '\GouuseCore\Rpcs\\'.$service_name.'\Models\\'.$class;
		} elseif ($class == 'Rpc') {
			$class_load = '\GouuseCore\Rpcs\\'.$service_name.'\Rpc';
		}
		App::bindIf($class_load, null, true);
		$obj = App::make($class_load);
		$obj->rpc_folder = $service_name;
		return $obj;
	}
}
