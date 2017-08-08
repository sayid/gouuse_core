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
	
	protected $host_pre = '/user_center/';
	
	protected $client;
	
	protected $service_name = 'UserCenter';
	
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
		
		
		$userdata = msgpack_pack($userdata);
		
		//$host = env('API_GATEWAY_HOST').$this->host_pre.'rpc';
		
		$client = new \swoole_client(SWOOLE_SOCK_TCP);
		$host = 'user_center.localhost.com';
		if (!$client->connect('user_center.localhost.com', 80, -1))
		{
			exit("connect failed. Error: {$client->errCode}\n");
		}
		$msg = "POST   ".$this->host_pre."v3/rpc   HTTP/1.0\r\n"
				. "Host: $host\r\n"
				. "Content-Type: application/x-www-form-urlencoded\r\n"
				. "Content-Length: ".strlen($userdata)."\r\n"
				. "Connection: Keep-Alive\r\n\r\n"
				.$userdata;
										
		$client->send($msg);
		$data = $client->recv(8192, \swoole_client::MSG_PEEK);
		echo $data;
		$client->close(true);
	}
	
}
