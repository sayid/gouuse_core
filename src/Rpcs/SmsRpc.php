<?php
namespace GouuseCore\Rpcs;

use GouuseCore\Rpcs\BaseRpc;

/**
 * 短信服务SDK
 * @author zhangyubo
 *
 */
class SmsRpc extends BaseRpc
{
	protected $host_pre = '/sms_service/';
	
	function __construct() {
		parent::__construct();
	}

	/**
	 * 短信发送接口
	 * @param array $data
	 * @return \GouuseCore\Rpcs\number[]|\GouuseCore\Rpcs\string[]|mixed
	 */
	function send($data = []) {
		$url = '/sms_service/v3/send';
		$result = $this->post($url, [], $data);
		return $result;
	}

}
