<?php
namespace GouuseCore\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use GouuseCore\Helpers\OptionHelper;

/**
 * 前置中间件
 * @author zhangyubo
 *
 */
class BeforeMiddleware
{
	public function handle($request, Closure $next)
	{
		$lang = $request->input('app_lang');
		if (!in_array($lang, ['zh_cn', 'zh_tw'])) {
			$lang = 'zh_cn';
		}
		if (!defined('DEFAULT_LANG_NAME')) {
			define('DEFAULT_LANG_NAME', $lang);
		}
		
		/**
		 *
		 * @var unknown $paths
		 */
		$paths = explode('/', $request->path());
		if ( end($paths) === 'rpc') {
			
			$data = file_get_contents('php://input');
			$data = msgpack_unpack($data);
			$sign = $data['sign'] ?? '';
			$request_data = $data;
			unset($data['sign']);
			ksort($data);
			$check_sign = md5(http_build_query($data).env('AES_KEY'));
			if ($check_sign != $sign) {
				//验签失败
				throw new \Exception("check sign fail");
			}
			
			if (!defined('NEED_AUTH_CHECK')) {
				//rpc添加校验获取用户信息
				define('NEED_AUTH_CHECK', true);
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
			if (isset($data['code']) && $data['code']>0 && empty($data['msg'])) {
					$code = $data['code'];
					$lang = $request->input('app_lang') ? : 'zh_cn';
					$lang = $lang ? $lang : 'zh_cn';
					$error_code = OptionHelper::getOption('error_code','options',$lang);
					$msg = isset($error_code[$code]) ? $error_code[$code] : '未知错误';
					if(isset($data['data'])){
						foreach ($data['data'] as $key => $value) {
                            if ($value && !is_object($value) && !is_array($value)) {
								$msg=str_replace("{\$".$key."}", $value, $msg);
							}
						}
					}
					$data['code'] = intval($code);
					$data['msg'] = $msg;
			}
            $member_info = $request->user();

            $log_data = array();
            $log_data['param'] = $request_data;//提交参数
            $log_data['result'] = $data;//返回数据
            $log_data['uri'] = $request->path();
            $log_data['user_agent'] = $request->header('user_agent');
            $log_data['member_id'] = $member_info['member_id'] ?? 0;//用户id
            $log_data['company_id'] = $member_info['company_id'] ?? 0;//公司id
            $log_data['sql_count'] = $GLOBALS ['sql_count'] ?? 0;
            $log_data['rpc_count'] = $GLOBALS ['rpc_count'] ?? 0;
            $log_data['memory_use'] = sprintf("%3.2f",memory_get_usage()/1024/1024)."M";
            $class_load = 'GouuseCore\Libraries\LogLib';
            App::bindIf($class_load, null, true);
            $this->LogLib = App::make($class_load);

            $this->LogLib->setDriver('access');
            $this->LogLib->info('', $log_data, true);
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