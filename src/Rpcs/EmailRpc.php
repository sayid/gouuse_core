<?php
namespace GouuseCore\Rpcs;

use GouuseCore\Rpcs\BaseRpc;

/**
 * 邮件服务SDK
 * @author zhangyubo
 *
 */
class EmailRpc extends BaseRpc
{
	protected $host_pre = '/email_service/';
	
	function __construct() {

	}

	/**
	 * 短信发送接口
	 * @param array $data
	 * @return \GouuseCore\Rpcs\number[]|\GouuseCore\Rpcs\string[]|mixed
	 */
	function send($data = []) {
		$url = '/email_service/v3/send';
		$result = $this->post($url, [], $data);
		return $result;
	}

}
