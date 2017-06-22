<?php
namespace GouuseCore\Rpcs;

use GouuseCore\Rpcs\BaseRpc;

/**
 * 用户中心SDK
 * @author zhangyubo
 *
 */
class AuthCenterRpc extends BaseRpc
{
	protected $host;
	
	function __construct() {
		$this->host = '';
		
	}
	
	function check($token) {
		$url = $this->host . '/auth_center/v3/check';
		$token = explode(' ', $token);
		$token = end($token);
		$header[] = 'Authorization: bearer '.$token;
		$result = $this->postOutside($url, $header, []);
		return $result;
	}
	

}