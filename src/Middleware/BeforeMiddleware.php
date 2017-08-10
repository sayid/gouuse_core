<?php
namespace GouuseCore\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

/**
 * 前置中间件
 * @author zhangyubo
 *
 */
class BeforeMiddleware
{
	public function handle($request, Closure $next)
	{
		/**
		 *
		 * @var unknown $paths
		 */
		$paths = explode('/', $request->path());
		if ( end($paths) === 'rpc') {
			
			$data = file_get_contents('php://input');
			$data = msgpack_unpack($data);
			$sign = $data['sign'] ?? '';
			unset($data['sign']);
			ksort($data);
			$check_sign = md5(http_build_query($data).env('AES_KEY'));
			if ($check_sign != $sign) {
				//验签失败
				throw new \Exception("check sign fail");
			}
			
			
			$class = $data['c'] ?? '';
			$method = $data['m'] ?? '';
			$args = $data['args'] ?? [];
			if (!defined('GOUUSE_MEMBER_INFO')) {
				define('GOUUSE_MEMBER_INFO', $data['GOUUSE_XX_V3_MEMBER_INFO'] ?? []);
			}
			if (!defined('GOUUSE_COMPANY_INFO')) {
				define('GOUUSE_COMPANY_INFO', $data['GOUUSE_XX_V3_COMPANY_INFO'] ?? []);
			}
			if (substr($class, strlen($class) - 3)=='Lib') {
				$class_load = "App\Libraries\\".$class;
			} elseif (substr($class, strlen($class) - 5)=='Model') {
				$class_load = "App\Models\\".$class;
			}
			
			App::bindIf($class_load, null, true);
			$obj = App::make($class_load);
			$data = call_user_func_array(array($obj, $method), $args);
			return response('#'.msgpack_pack($data));
		}
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