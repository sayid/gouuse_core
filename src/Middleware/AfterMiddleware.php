<?php
namespace GouuseCore\Middleware;

use GouuseCore\Helpers\OptionHelper;
use Closure;
use Illuminate\Support\Facades\App;

class AfterMiddleware
{
	public function handle($request, Closure $next)
	{
		$response = $next($request);
		
		$content = $data= $response->getContent();
		if (is_string($data)) {
			$data = json_decode($data, true);
		}
		if (is_array($data) && $data['code']>0 && empty($data['msg'])) {
			$code = $data['code'];
			$lang = $request->input('app_lang') ? : 'zh_cn';
			$lang = $lang ? $lang : 'zh_cn';
			$error_code = OptionHelper::getOption('error_code','options',$lang);
			$msg = isset($error_code[$code]) ? $error_code[$code] : '未知错误';
			
			if(isset($data['data'])){
				foreach ($data['data'] as $key => $value) {
					$msg=str_replace("{".$key."}", $value, $msg);
				}
			}
			$data['code'] = strval($code);
			$data['msg'] = $msg;
			$content = json_encode($data);
		}
		
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