<?php
namespace GouuseCore\Rpcs;

use GouuseCore\Rpcs\BaseRpc;

/**
 * 事务中心SDK
 * @author zhangyubo
 *
 */
class transactionCenterRpc extends BaseRpc
{
	protected $host;
	
	function __construct() {
		$this->host = '';
		parent::__construct();
	}
	
	/**
	 * 开启事务
	 * @param unknown $param
	 * @return \GouuseCore\Rpcs\number[]|\GouuseCore\Rpcs\string[]|mixed
	 */
	function TcStart($param) {
		$url = $this->host . '/auth_center/v3/check';
		$token = explode(' ', $token);
		$token = end($token);
		$header[] = 'Authorization: bearer '.$token;
		$result = $this->postOutside($url, $header, []);
		return $result;
	}
	
	/**
	 * 登录
	 * @param string $account
	 * @param string $password
	 * @return \GouuseCore\Rpcs\number[]|\GouuseCore\Rpcs\string[]|mixed
	 */
	function login(string $account, string $password, $company_id = 0) {
		$url = $this->host . '/auth_center/v3/login';
		$result = $this->postOutside($url, [], [ 'account' => $account, 'password' => $password , 'company_id' => $company_id]);
		return $result;
	}
	

}