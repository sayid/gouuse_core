<?php
namespace GouuseCore\Rpcs\UserCenter;

use Illuminate\Support\Facades\App;
use GouuseCore\Exceptions\GouuseRpcException;
use Ixudra\Curl\Facades\Curl;

/**
 * API SDK基类
 * @author zhangyubo
 *
 */
class Rpc
{
	
	private static $current_member_id;
	private static $user;
	private static $company_info;
	
	//服务URI前缀
	protected $host_pre = '/transaction_center/';
	
	protected $client;
	
	public function __get($class)
	{
		if ($class == 'member_info') {
			if (!defined('NEED_AUTH_CHECK')) {
				return null;
			}
			if (isset(app()['gouuse_member_info'])) {
				return app()['gouuse_member_info'];
			}
			app()['gouuse_member_info']= Auth::user();
			return app()['gouuse_member_info'];
		} else if ($class == 'company_info') {
			if (isset(app()['gouuse_company_info'])) {
				return app()['gouuse_company_info'];
			}
		}
	}
	
	public function __construct()
	{
		//$host = env('API_GATEWAY_HOST').$this->host_pre.'rpc';
		//$this->hprose_client = new \Hprose\Http\Client($host, false);
	}
	
	/**
	 * 执行rpc
	 * @param unknown $class
	 * @param unknown $method
	 * @param array $args
	 */
	public function do($class, $method, $args = [])
	{
		
		$userdata = [
				'GOUUSE_XX_V3_MEMBER_INFO' => app()['gouuse_member_info'] ?? [],
				'GOUUSE_XX_V3_COMPANY_INFO' => app()['gouuse_company_info'] ?? [],
				'args' => $args,
				'c' => $class,
				'm' => $method
		];
		
		ksort($userdata);
		//计算签名
		$userdata['sign'] = md5(http_build_query($userdata).env('AES_KEY'));
		
		$userdata = msgpack_pack($userdata);
		
		
		$client = new \swoole_client(SWOOLE_SOCK_TCP);
		$host = env('API_GATEWAY_HOST');
		$host = str_replace(['http://','https://'], '', $host);
		if (!$client->connect($host, 80, -1))
		{
			exit("connect failed. Error: {$client->errCode}\n");
		}
		
		$client->set(array(
				'open_eof_check' => true,
				'package_eof' => "\r\n\r\n",
		));
		
		$msg = "POST   ".$this->host_pre."v3/rpc   HTTP/1.0\r\n"
				. "Host: $host\r\n"
				. "Content-Type: application/x-www-form-urlencoded\r\n"
				. "Content-Length: ".strlen($userdata)."\r\n"
				. "Connection: Keep-Alive\r\n\r\n"
				.$userdata;
										
		$client->send($msg);
		
		$data = $client->recv();
		$length = strpos($data, "#");
		$data = substr($data, $length + 1);
		try {
			$data = msgpack_unpack($data);
		} catch (\ErrorException $e) {
			
		}
		$client->close(true);
		
		if (is_array($data) && isset($data['code']) && isset($data['exception'])) {
			//异常
			throw new GouuseRpcException($data['exception']);
		}
		return $data;
	}
	
	
	/**
	 * 魔术方法 自动调用远程方法
	 * @param unknown $name
	 * @param unknown $arguments
	 * @return unknown
	 */
	public function ___call($name, $arguments)
	{
		return StringHelper::getClassname(get_class($this));
		return $this->do(StringHelper::getClassname(get_class($this)), $name, $arguments);
	}
}
