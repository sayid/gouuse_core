<?php
namespace GouuseCore;

class Application extends \Laravel\Lumen\Application
{
	
    public function getMiddleware()
    {
        return $this->middleware;
    }
    
    public function callTerminableMiddleware($response)
    {
        parent::callTerminableMiddleware($response);
    }
}

if (!defined('TIME_START')){
	function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	$time_start=microtime_float();
	define('TIME_START', $time_start);
}

if (!defined('ROOT_PATH'))
	define('ROOT_PATH', substr(__DIR__,0,-9));
