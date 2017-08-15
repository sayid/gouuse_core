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
	protected $host_pre = '/user_center/';

	function __construct() {
		parent::__construct();
	}

	function getAllMembers() {
		$url = '/user_center/v3/member_list';
		$result = $this->post($url, [], ['debug'=>1]);
		return $result;
	}

	function getByMemberId(int $member_id, int $company_id) {
		 
		$url = '/user_center/v3/member_info';
		$result = $this->post($url, [], [ 'member_id' => $member_id, 'company_id' => $company_id]);
		return $result;
	}
}
