<?php
namespace GouuseCore\Rpcs;

use GouuseCore\Rpcs\BaseRpc;

/**
 * 认证中心SDK
 * @author zhangyubo
 *
 */
class AccountRpc extends BaseRpc
{
	protected $host;

	function __construct() {
		$this->host = env('API_ACCOUNT_HOST');

	}

	function login(string $account, string $password) {
		$url = $this->host . '/user_center/v3/login';
		$result = $this->postOutside($url, [], [ 'account' => $account, 'password' => $password ]);
		return $result;
	}

	function register(array $data) {
		$url = $this->host . '/user_center/v3/register';
		$result = $this->postOutside($url, [], $data);
		return $result;
	}
}