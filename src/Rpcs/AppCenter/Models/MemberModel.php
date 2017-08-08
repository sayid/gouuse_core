<?php
namespace GouuseCore\Rpcs\UserCenters\Models;

use GouuseCore\Rpcs\MemberRpc;

/**
 * 用户中心SDK
 * @author zhangyubo
 *
 */
class MemberModel extends MemberRpc
{
	function __construct() {
		
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
