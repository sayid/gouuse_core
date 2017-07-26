<?php
namespace GouuseCore\Rpcs;

use GouuseCore\Rpcs\BaseRpc;

/**
 * 部门SDK
 * @author zhangyubo
 *
 */
class departmentRpc extends BaseRpc
{
	protected $host;

	function __construct() {
		$this->host = '';

	}

	function getAllMembers() {
		$url = $this->host . '/user_center/v3/member_list';
		$result = $this->post($url, [], ['debug'=>1]);
		return $result;
	}

	function getByMemberId(int $member_id, int $company_id) {
		 
		$url = $this->host . '/user_center/v3/member_info';
		$result = $this->post($url, [], [ 'member_id' => $member_id, 'company_id' => $company_id]);
		return $result;
	}
}
