<?php
namespace GouuseCore\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use GouuseCore\Libraries\CodeLib;

class Authenticate
{
	
	/**
	 * The authentication guard factory instance.
	 *
	 * @var \Illuminate\Contracts\Auth\Factory
	 */
	protected $auth;
	
	
	/**
	 * Create a new middleware instance
	 * Authenticate constructor.
	 * @param Auth $auth
	 */
	public function __construct(Auth $auth)
	{
		$this->auth = $auth;
	}
	
	/**
	 * Handle an incoming request.
	 * @param $request
	 * @param Closure $next
	 * @param null $guard
	 * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|mixed
	 */
	public function handle($request, Closure $next, $guard = null)
	{
		// 注册全局变量 标示启用auth
		if (!defined('NEED_AUTH_CHECK')) {
			define('NEED_AUTH_CHECK', true);
		}
		
		if ($this->auth->guard($guard)->guest()) {
			return response([
					'code' => CodeLib::AUTH_REQUIRD], 200);
		}
		if (is_numeric($code = $request->user())) {
			return response([
					'code' => $code], 200);
		}
		return $next($request);
	}
}