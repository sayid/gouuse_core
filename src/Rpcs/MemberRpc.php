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
		$this->host = '';

	}

	function getAllMembers() {
		$url = $this->host . '/account/v3/list';
		$result = $this->post($url, [], ['debug'=>1]);
		return $result;
	}

	function getByMemberId(int $member_id, int $company_id) {
		 
		$url = $this->host . '/account/v3/member_info';
		$result = $this->post($url, [], [ 'member_id' => $member_id, 'company_id' => $company_id]);
		return $result;
	}

	function getByToken($token) {
		$url = $this->host . '/account/v3/member_info';
		$token = explode(' ', $token);
		$token = end($token);
		$header[] = 'Authorization: bearer '.$token;
		$result = $this->postOutside($url, $header, [ '_token' => $token]);
		return $result;
	}
}