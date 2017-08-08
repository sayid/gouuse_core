<?php
namespace GouuseCore\Helpers;

use App;

class RpcHelper
{
	
	public static function load($service_name, $class)
	{
	    return App::make('\GouuseCore\Rpcs\\'.$service_name.'\\'.$class);
	}
}
