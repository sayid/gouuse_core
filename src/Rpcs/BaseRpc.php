<?php
namespace GouuseCore\Rpcs;

use Log;
use Illuminate\Support\Facades\Auth;
use GouuseCore\Exceptions\GouuseRpcException;
use Ixudra\Curl\Facades\Curl;

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
	}
	public function preData()
	{
		if (empty(self::$current_member_id)) {
			self::$user = isset(app()['gouuse_member_info']) ? app()['gouuse_member_info'] : [];
			self::$current_member_id = self::$user['member_id'] ?? 0;
			self::$company_info = isset(app()['gouuse_company_info']) ? app()['gouuse_company_info'] : [];
		}

	}

	public function postOutside($url, $header = [], $data = [])
	{
		if (strpos($url, '/')===false && strpos($url, 'http') === false) {
			$url = '/'.$url;
		}
		if (strpos($url, '/')===0) {
			if (empty(self::$gatewaylib)) {
				self::$gatewaylib = new \GouuseCore\Libraries\GatewayLib();
			}
			$url = self::$gatewaylib->getHost($url) . $url;
		}
		$result = Curl::to($url)
		->withHeaders($header)
		->withData($data)
		->post();
		
		$class_load = 'GouuseCore\Libraries\LogLib';
		App::bindIf($class_load, null, true);
		$this->LogLib = App::make($class_load);
		
		$this->LogLib->setDriver('rpc');
		
		$log_data = [
				'uri' => $url,
				'member_id' => self::$current_member_id ?? 0,
				'company_id' => self::$user ['company_id'] ?? 0,
				'param' => $data,
				'header' => $header
		];
		$this->LogLib->info('', $log_data, true);
		
		return $this->buildResult($result, $url);
	}

	public function post($url, $header = [], $data = [])
	{
		if (strpos($url, '/')===false && strpos($url, 'http') === false) {
			$url = '/'.$url;
		}
		if (strpos($url, '/')===0) {
			if (empty(self::$gatewaylib)) {
				self::$gatewaylib = new \GouuseCore\Libraries\GatewayLib();
			}
			$url = self::$gatewaylib->getHost($url).$url;
		}

		$this->preData();
		$header[] = 'GOUUSE-INSIDE: '.time();
		if (self::$current_member_id) {
			$header[] = 'CURRENT-MEMBER-ID:' . self::$current_member_id;
			$header [] = 'CURRENT-COMPANY-ID:' . self::$user ['company_id'] ?? 0;
			$data['GOUUSE_XX_V3_MEMBER_INFO'] = json_encode (self::$user);
			$data['GOUUSE_XX_V3_COMPANY_INFO'] = json_encode (self::$company_info);
		}

		$result = Curl::to($url)
		->withHeaders($header)
		->withData($data)
		->post();
		
		$class_load = 'GouuseCore\Libraries\LogLib';
		App::bindIf($class_load, null, true);
		$this->LogLib = App::make($class_load);
		
		$this->LogLib->setDriver('rpc');
		
		$log_data = [
				'uri' => $url,
				'member_id' => self::$current_member_id ?? 0,
				'company_id' => self::$user ['company_id'] ?? 0,
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
			throw new GouuseRpcException("通信失败请稍后重试：".$url);
		}
		if ($result['code'] != 0 && isset($result['exception'])) {
			throw new GouuseRpcException($result['exception']);
		}
		return $result;
	}
}
