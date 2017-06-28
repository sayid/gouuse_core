<?php
namespace GouuseCore\Middleware;

use Closure;

/**
 * 前置中间件
 * @author zhangyubo
 *
 */
class BeforeMiddleware
{
	public function handle($request, Closure $next)
	{
		// 执行动作
		$info = $request->input("info");
		if($info){
			//执行解密
			$key=substr(md5(env('AES_KEY')."gou"),0,8);
			$class_load = 'GouuseCore\Libraries\EncryptLib';
			App::bindIf($class_load, null, true);
			$this->EncryptLib= App::make($class_load);
			$info = $this->EncryptLib->decrypt($info, $key);
			$request_array = json_decode($info, true);
			
			/*
			 * 以下三个方法：attributes、query、request
			 * @see Symfony\Component\HttpFoundation\Request 底层方法封装
			 * @see Symfony\Component\HttpFoundation\ParameterBag 接口实现
			 */
			if (count($request_array)>0) {
				foreach ($request_array as $key => $value) {
					if ($key == 'source') {
						if ($value == 2 || $value == 3) {
							$this->is_encrypt = 1;
						}
					}
					$request->attributes->set($key, $value);
					$request->query->set($key, $value);
					$request->request->set($key, $value);
				}
			}
		}
		
		return $next($request);
	}
}