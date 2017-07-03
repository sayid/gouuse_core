<?php
namespace GouuseCore\Middleware;

use Closure;

/**
 * 判断
 * @author zhangyubo
 *
 */
abstract  class Authenticate
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
    	// 注册全局变量 标示启用auth
    	define('NEED_AUTH_CHECK', true);
    	
    	if ($this->auth->guard($guard)->guest()) {
    		return response([
    				'code' => CodeLib::MEMBER_AUTH_FAILD], 200);
    	}
    	
    	return $next($request);
    }
}
