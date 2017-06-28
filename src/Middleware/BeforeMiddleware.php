<?php
namespace GouuseCore\Middleware;

use Closure;

class BeforeMiddleware
{
	public function handle($request, Closure $next)
	{
		// 执行动作
		
		return $next($request);
	}
}