<?php

namespace GouuseCore\Rpcs;

use Illuminate\Support\Facades\App;
use GouuseCore\Exceptions\GouuseRpcException;
use Ixudra\Curl\Facades\Curl;
use GouuseCore\Helpers\StringHelper;

/**
 * API SDK基类
 * @author zhangyubo
 *
 */
class BaseRpc
{
	
	private static $current_member_id;
	private static $user;
	private static $company_info;
	private static $gatewaylib;
	private $LogLib;
	
	
	public function __construct()
	{
		self::$gatewaylib = new \GouuseCore\Libraries\GatewayLib();
		$class_load = 'GouuseCore\Libraries\LogLib';
		App::bindIf($class_load, null, true);
		$this->LogLib = App::make($class_load);
		$this->LogLib->setDriver('rpc');
	}
	
	public function preData()
	{
		if (empty(self::$current_member_id)) {
			self::$user = defined('GOUUSE_MEMBER_INFO') ? GOUUSE_MEMBER_INFO : [];
			self::$current_member_id = self::$user['member_id'] ?? 0;
			self::$company_info = defined('GOUUSE_COMPANY_INFO') ? GOUUSE_COMPANY_INFO: [];
		}
		
	}
	
	public function postOutside($url, $header = [], $data = [])
	{
		if (strpos($url, '/') === false && strpos($url, 'http') === false) {
			$url = '/' . $url;
		}
		if (strpos($url, '/') === 0) {
			if (empty(self::$gatewaylib)) {
				self::$gatewaylib = new \GouuseCore\Libraries\GatewayLib();
			}
			$url = self::$gatewaylib->getHost($url) . $url;
		}
		$result = Curl::to($url)
		->withHeaders($header)
		->withData($data)
		->post();
		
		
		
		
		
		$log_data = [
				'uri' => $url,
				'member_id' => self::$current_member_id ?? 0,
				'company_id' => isset(self::$user ['company_id']) ? self::$user ['company_id'] : 0,
				'param' => $data,
				'header' => $header
		];
		$this->LogLib->info('', $log_data, true);
		
		return $this->buildResult($result, $url);
	}
	
	public function post($url, $header = [], $data = [])
	{
		if (strpos($url, '/') === false && strpos($url, 'http') === false) {
			$url = '/' . $url;
		}
		if (strpos($url, '/') === 0) {
			if (empty(self::$gatewaylib)) {
				self::$gatewaylib = new \GouuseCore\Libraries\GatewayLib();
			}
			$url = self::$gatewaylib->getHost($url) . $url;
		}
		
		$this->preData();
		$company_id = 0;
		if (isset(self::$user ['company_id'])) {
			$company_id = self::$user ['company_id'];
		}
		$header[] = 'GOUUSE-INSIDE: ' . time();
		if (self::$current_member_id) {
			$header[] = 'CURRENT-MEMBER-ID:' . self::$current_member_id;
			$header [] = 'CURRENT-COMPANY-ID:' . $company_id;
			$data['GOUUSE_XX_V3_MEMBER_INFO'] = json_encode(self::$user);
			$data['GOUUSE_XX_V3_COMPANY_INFO'] = json_encode(self::$company_info);
		}
		
		$result = Curl::to($url)
		->withHeaders($header)
		->withData($data)
		->post();
		
		$log_data = [
				'uri' => $url,
				'member_id' => self::$current_member_id ?? 0,
				'company_id' => $company_id,
				'param' => $data,
				'header' => $header
		];
		$this->LogLib->info('', $log_data, true);
		
		return $this->buildResult($result, $url);
	}
	
	/**
	 * parse数据 json to array
	 * @param unknown $result
	 * @return number[]|string[]|mixed
	 */
	public function buildResult($result, $url)
	{
		$result = json_decode($result, true);
		
		if (empty($result) || !is_array($result) || !isset($result['code'])) {
			throw new GouuseRpcException("通信失败请稍后重试：" . $url);
		}
		if ($result['code'] != 0 && isset($result['exception'])) {
			throw new GouuseRpcException($result['exception']);
		}
		return $result;
	}
	
	/*************************以下为rpc方法调用专用***************************/
	/**
	 * 执行rpc
	 * @param unknown $class
	 * @param unknown $method
	 * @param array $args
	 * @param int $async 是否异步调用
	 */
	public function do($class, $method, $args = [], $async = 0)
	{
		if (env('APP_DEBUG') == true) {
			$GLOBALS['rpc_count'] = isset($GLOBALS['rpc_count']) ? $GLOBALS['rpc_count'] + 1 : 1;
		}
		
		$userdata = [
				'GOUUSE_XX_V3_MEMBER_INFO' => defined('GOUUSE_MEMBER_INFO') ? GOUUSE_MEMBER_INFO : [],
				'GOUUSE_XX_V3_COMPANY_INFO' => defined('GOUUSE_COMPANY_INFO') ? GOUUSE_COMPANY_INFO : [],
				'args' => $args,
				'c' => $class,
				'm' => $method
		];
		if ($async) {
			$userdata['rpc_folder'] = $this->rpc_folder;
		}
		//实例化日志类
		$class_load = 'GouuseCore\Libraries\LogLib';
		App::bindIf($class_load, null, true);
		$this->LogLib = App::make($class_load);
		$this->LogLib->setDriver('rpc');
		
		if ($async) {
			//异步调用 将调用信息放入队列异步执行
			
			$class_load = 'GouuseCore\Libraries\MqLib';
			App::bindIf($class_load, null, true);
			$this->MqLib = App::make($class_load);
			
			$data_mq = array();
			$data_mq['data'] = $userdata;
			$data_mq['topic_name'] = 'rpc_async'; //rpc 异步执行
			$data_mq['service_id'] = env('SERVICE_ID');
			ksort($data_mq);
			//计算签名
			$data_mq['sign'] = md5(http_build_query($data_mq).env('AES_KEY'));
			
			$data = array();
			$data['topic_name'] = 'v3-main';
			$data['message_body'] = json_encode($data_mq);
			$re = $this->MqLib->sendTopic($data);
			$result = (array)$re;
			
			$log_data = [
					'uri' => $this->rpc_folder . '->' . $class.'->'.$method.'()',
					'member_id' => $userdata['GOUUSE_XX_V3_MEMBER_INFO']['member_id'] ?? 0,
					'company_id' => $userdata['GOUUSE_XX_V3_MEMBER_INFO']['company_id'] ?? 0,
					'param' => $args,
					'async' => 1,
					'response' => $result
			];
			$this->LogLib->info('', $log_data, true);
			
			if ($result) {
				return true;
			} else {
				return false;
			}
		}
		
		ksort($userdata);
		//计算签名
		$userdata['sign'] = md5(http_build_query($userdata).env('AES_KEY'));
		$userdata = msgpack_pack($userdata);
		
		if (isset($this->_private_host) && $this->_private_host) {
			$host = $this->_private_host;
		} else {
			$host = env('API_GATEWAY_HOST');
		}
		
		$host = str_replace(['http://','https://'], '', $host);
		
		$msg = "POST   ".$this->host_pre."v3/rpc   HTTP/1.0\r\n"
				. "Host: $host\r\n"
				. "Content-Type: application/x-www-form-urlencoded\r\n"
				. "Content-Length: ".strlen($userdata)."\r\n"
				. "Connection: Keep-Alive\r\n\r\n"
				.$userdata;
										
				if(!extension_loaded('swoole')) {
					//没有安装Swoole扩展
					$fp = fsockopen($host, 80, $errno, $errstr, 30);
					if (!$fp) {
						exit("connect failed. Error: {$client->errCode}\n");
					}
					fwrite($fp, $msg);
                    $data = '';
					$mark_start = false;
                    while (!feof($fp)) {
                        $data = $data . fread($fp, 1024);
                        if ($mark_start == false && strpos($data, "\r\n\r\n")) {
                            $mark_start = true;
                            $data = substr($data, strpos($data, "\r\n\r\n")+4);
                        }
                    }

					if (empty($data)) {
						throw new GouuseRpcException($host.$this->host_pre.'v3/rpc not found:' . $data);
					}
					fclose($fp);
				} else {
					$client = new \swoole_client(SWOOLE_SOCK_TCP);
					if (!$client->connect($host, 80, -1))
					{
						exit("connect failed. Error: {$client->errCode}\n");
					}
					$client->set(array(
							'open_eof_check' => true,
							'package_eof' => "\r\n\r\n",
					));
					
					$client->send($msg);
		            $data = $client->recv(10240);
		            $data = substr($data, strpos($data, "\r\n\r\n")+4);
                    if (substr($data, 0, 1) != "#") {
                        throw new GouuseRpcException($data);
                    }
		            while (1) {
		                $tmp = $client->recv(10240);
		                if (empty($tmp)) {
		                    break;
		                }
		                $data = $data . $tmp;
		            }
		            $client->close(true);
				}
				if (substr($data, 0, 1) != "#") {
            		throw new GouuseRpcException($data);
        		}			
				
				$data = substr($data, 1);
				try {
					$data = msgpack_unpack($data);
				} catch (\ErrorException $e) {
					throw new GouuseRpcException($e->getMessage());
				}
				
				
				$log_data = [
						'uri' => $this->rpc_folder . '->' . $class.'->'.$method.'()',
						'member_id' => $userdata['GOUUSE_XX_V3_MEMBER_INFO']['member_id'] ?? 0,
						'company_id' => $userdata['GOUUSE_XX_V3_MEMBER_INFO']['company_id'] ?? 0,
						'param' => $args,
						'response' => $data,
						'async' => 0
				];
				$this->LogLib->info('', $log_data, true);
				
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
		return $this->do(StringHelper::getClassname(get_class($this)), $name, $arguments);
	}
	
	public function __get($class)
	{
		
		if ($class == 'member_info') {
			if (!defined('NEED_AUTH_CHECK')) {
				return null;
			}
			if (defined('GOUUSE_MEMBER_INFO')) {
				return GOUUSE_MEMBER_INFO;
			}
		} else if ($class == 'company_info') {
			if (defined('GOUUSE_COMPANY_INFO')) {
				return GOUUSE_COMPANY_INFO;
			}
		}
		$path = str_replace('GouuseCore\Rpcs\\', '', get_class($this));
		$this->rpc_folder = substr($path, 0, strpos($path, "\\"));
		if (substr($class, strlen($class) - 3)=='Lib') {
			$class_load = "GouuseCore\Rpcs\\".$this->rpc_folder."\Libraries\\".$class;
		} elseif (substr($class, strlen($class) - 5)=='Model') {
			$class_load = "GouuseCore\Rpcs\\".$this->rpc_folder."\Models\\".$class;
		}  elseif (substr($class, strlen($class) - 3)=='Rpc') {
			$class_load = "GouuseCore\Rpcs\\".$this->rpc_folder."\Rpc";
		} else {
			return;
		}
		App::bindIf($class_load, null, true);
		$obj = App::make($class_load);
		return $obj;
	}
}
