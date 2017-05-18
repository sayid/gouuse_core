<?php
namespace GouuseCore\Rpcs;

use GouuseCore\Rpcs\BaseRpc;

/**
 * 用户中心SDK
 * @author zhangyubo
 *
 */
class MemberRpc extends BaseRpc
{
	protected $host;

	function __construct() {
		$this->host = env('API_ACCOUNT_HOST');

	}

	function getAllMembers() {
		$this->host = $this->host . '/account/v3/list';
		$result = $this->post($this->host, [], ['debug'=>1]);
		return $result;
	}

	function getByMemberId(int $member_id, int $company_id) {
		 
		$this->host = $this->host . '/account/v3/member_info';
		$result = $this->post($this->host, [], [ 'member_id' => $member_id, 'company_id' => $company_id]);
		return $result;
	}

	function getByToken($token) {
		$this->host = $this->host . '/account/v3/member_info';
		$token = explode(' ', $token);
		$token = end($token);
		$header[] = 'Authorization: bearer '.$token;
		$result = $this->postOutside($this->host, $header, [ '_token' => $token]);
		return $result;
	}

	function login(string $account, string $password) {
		$this->host = $this->host . '/account/v3/login';
		$result = $this->post($this->host, [], [ 'account' => $account, 'password' => $password ]);
		return $result;
	}

	function register(array $data) {
		$this->host = $this->host . '/account/v3/register';
		$result = $this->post($this->host, [], $data);
		return $result;
	}


}