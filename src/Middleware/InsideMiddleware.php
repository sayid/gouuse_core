<?php
namespace GouuseCore\Http\Middleware;

use Illuminate\Support\Facades\Cache;
use Closure;

/**
 * 判断
 * @author zhangyubo
 *
 */
class InsideMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //验证只能内网访问
    	if (!preg_match('/192\.168\.(.*)/', $_SERVER['REMOTE_ADDR'])) {
    		die('error inside');
    	}
    	define('REQUEST_IS_LOCAL', true);
    	return $next($request);
    }
}
