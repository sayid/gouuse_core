<?php
namespace GouuseCore\Middleware;

use GouuseCore\Helpers\OptionHelper;
use Closure;
use Illuminate\Support\Facades\App;
use GouuseCore\Helpers\DateHelper;

class AfterMiddleware
{
	public function handle($request, Closure $next)
	{
		$response = $next($request);
		
		$content = $response->getContent();
		if (is_string($content)) {
			$data = json_decode($content, true);
		}
		if (isset($data) && is_array($data)) {
			
			if (isset($data['code']) && $data['code']>0 && empty($data['msg'])) {
				
				$code = $data['code'];
				$lang = $request->input('app_lang') ? : 'zh_cn';
				$lang = $lang ? $lang : 'zh_cn';
				$error_code = OptionHelper::getOption('error_code','options',$lang);
				$msg = isset($error_code[$code]) ? $error_code[$code] : '未知错误';
				if(isset($data['data'])){
					foreach ($data['data'] as $key => $value) {
						if (is_string($value)) {
							$msg=str_replace("{".$key."}", $value, $msg);
						}
					}
				}
				$time = DateHelper::microtime_float();
				$data['run_time'] = $time - TIME_START;
				$data['code'] = intval($code);
				$data['msg'] = $msg;
			}
			$content = json_encode($data);
		}
		
		$member_info = $request->user();
		
		$log_data = array();
		$log_data['param'] = $request->all();//提交参数
		$log_data['token'] = $request->header('Authorization');//提交参数
		$log_data['result'] = $data ?? $result;//返回数据
		$log_data['uri'] = $request->uri();
		$log_data['user_agent'] = $request->header('user_agent');
		$log_data['member_id'] = $member_info['member_id'] ?? 0;//用户id
		$log_data['company_id'] = $member_info['company_id'] ?? 0;//公司id
		
		$class_load = 'GouuseCore\Libraries\LogLib';
		App::bindIf($class_load, null, true);
		$this->LogLib = App::make($class_load);
		
		$this->LogLib->setDriver('log');
		$this->LogLib->info('', $log_data, true);
		
		if ($request->input('source') == 2 || $request->input('source') == 3 && !defined('REQUEST_IS_LOCAL')) {
			//加密
			$key=substr(md5(env('AES_KEY')."gou"),0,8);
			$class_load = 'GouuseCore\Libraries\EncryptLib';
			App::bindIf($class_load, null, true);
			$this->EncryptLib= App::make($class_load);
			$content = $this->EncryptLib->encrypt($content, $key);
		}
		$response->setContent($content);
		return $response;
	}
}