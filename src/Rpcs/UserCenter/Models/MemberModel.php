<?php
namespace GouuseCore\Rpcs\UserCenters\Models;

use GouuseCore\Rpcs\UserCenter\Rpc;

/**
 * 用户中心SDK
 * @author zhangyubo
 *
 */
class MemberModel extends Rpc
{
	function __construct() {
		parent::__construct($service_name);
	}
		
	/**
	 * 获取公司所有员工id
	 * @param int $company_id
	 * @return mixed
	 */
	public function getAllMemberId($company_id = 0)
	{
		return $this->do('MemberModel', 'getAllMemberId', [$company_id]);
		
	}
}
