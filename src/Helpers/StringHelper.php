<?php
namespace GouuseCore\Helpers;

class StringHelper
{
	/**
	 * 校验字符串是否json格式
	 * @param unknown $string
	 * @return boolean
	 */
	public static function isJson($string)
	{
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
	
	public static function sinaShortUrl($src_url)
	{
		$src_url = base64_decode($src_url);
		$url = "http://api.t.sina.com.cn/short_url/shorten.json?source=3835836564&url_long=$src_url";
		//设置附加HTTP头
		$addHead = array(
				"Content-type: application/json"
		);
		//初始化curl，当然，你也可以用fsockopen代替
		$curl_obj = curl_init();
		//设置网址
		curl_setopt($curl_obj, CURLOPT_URL, $url);
		//附加Head内容
		curl_setopt($curl_obj, CURLOPT_HTTPHEADER, $addHead);
		//是否输出返回头信息
		curl_setopt($curl_obj, CURLOPT_HEADER, 0);
		//将curl_exec的结果返回
		curl_setopt($curl_obj, CURLOPT_RETURNTRANSFER, 1);
		//设置超时时间
		curl_setopt($curl_obj, CURLOPT_TIMEOUT, 15);
		//执行
		$result = curl_exec($curl_obj);
		//关闭curl回话
		curl_close($curl_obj);
		$re = json_decode($result);
		//p($re);die;
		if (@$re->error_code) {
			return ['code'=>$re->error_code,'msg'=>$re->error];
		}
		return ['code'=>0,'data'=>array('url'=>$re[0]->url_short)];
	}
	
	
	public static function getNamespace($class)
	{
		return array_slice(explode('\\', $class), 0, -1);
	}
	
	public static function getClassname($class)
	{
		return join('', array_slice(explode('\\', $class), -1));
	}
}
