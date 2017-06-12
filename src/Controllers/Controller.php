<?php
namespace GouuseCore\Controllers;

use GouuseCore\Core\BaseGouuse;
use GouuseCore\Helpers\OptionHelper;//配置文件
use GouuseCore\Libraries\LogLib;

/**
 * 基类 复写了lumen基类
 * @author zhangyubo
 *
 */
class Controller extends BaseGouuse
{
	use \Laravel\Lumen\Routing\ProvidesConvenienceMethods;

	protected $output;
	protected $is_encrypt = 0;

	public function __construct()
	{
		parent::__construct();

		/*
		 * lihonglin
		 * 2017-06-07
		 * 增加：重写Request中提交的数据，如果是app端加密数据，进行解密后将数据重新组合到Request中
		 */
		if(isset(app()['Illuminate\Http\Request'])){
			$request_obj =app()['Illuminate\Http\Request'];
			$info = $request_obj->input("info");
			if($info){
				//执行解密
				$key=substr(md5(env('AES_KEY')."gou"),0,8);
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
    					app()['Illuminate\Http\Request']->attributes->set($key, $value);
    					app()['Illuminate\Http\Request']->query->set($key, $value);
    					app()['Illuminate\Http\Request']->request->set($key, $value);
    				}
				}
			}
		}
	}

	/**
	 * 后续扩展返回数据 加密等等
	 * @param array $data
	 * @param int 是否加密
	 * @param boolean 是否写入日志
	 * @return unknown
	 */
	public function display(array $data, $encrypt = 0, $is_log = true)
	{
		if (defined('REQUEST_IS_LOCAL')) {
			//内部访问时数据不加密
			$encrypt = 0;
		} else {
		    if(!$encrypt){
		      $encrypt = $this->is_encrypt;
		    }
		}
		$msg = 'ok';
		$code = isset($data['code']) ? $data['code'] : 0;
		if (!empty($code)) {
			$lang = isset($_REQUEST['app_lang']) ? $_REQUEST['app_lang'] : 'zh_cn';
			$lang = $lang ? $lang : 'zh_cn';
			$error_code = OptionHelper::getOption('error_code','options',$lang);
			$msg = isset($error_code[$code]) ? $error_code[$code] : '未知错误';

			if(isset($data['data'])){
				foreach ($data['data'] as $key => $value) {
					$msg=str_replace("{".$key."}", $value, $msg);
				}
			}
		}
		$data['msg'] = $msg;

		/*
		 * lihonglin
		 * 2017-06-07
		 * 增加:系统底层统一日志
		 */
		//判断有提交和返回数据，写入到日志中
		if($is_log){
			if(isset(app()['Illuminate\Http\Request'])){

				//从底层获取request对像，并获得提交的全部参数，除header信息除外
				$request = app()['Illuminate\Http\Request']->request->all();

				//统一将提交的参数和返回的数据写入日程
				$this->LogLib->log_info(['param' => json_encode($request), 'result' => json_encode($data)]);
			}
		}

		if ($encrypt) {
			//执行加密
			$key=substr(md5(env('AES_KEY')."gou"),0,8);
			$data = $this->EncryptLib->encrypt($data, $key);
		}
		//return $data;
		return response($data, 200)->send();
	}

	/**
	 * The middleware defined on the controller.
	 *
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * Define a middleware on the controller.
	 *
	 * @param  string  $middleware
	 * @param  array  $options
	 * @return void
	 */
	public function middleware($middleware, array $options = [])
	{
		$this->middleware[$middleware] = $options;
	}

	/**
	 * Get the middleware for a given method.
	 *
	 * @param  string  $method
	 * @return array
	 */
	public function getMiddlewareForMethod($method)
	{
		$middleware = [];

		foreach ($this->middleware as $name => $options) {
			if (isset($options['only']) && ! in_array($method, (array) $options['only'])) {
				continue;
			}

			if (isset($options['except']) && in_array($method, (array) $options['except'])) {
				continue;
			}

			$middleware[] = $name;
		}

		return $middleware;
	}
}
