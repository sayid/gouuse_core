<?php
namespace GouuseCore\Rpcs\UserCenter\Models;

use GouuseCore\Rpcs\UserCenter\Rpc;

/**
 * 用户中心SDK
 * @author zhangyubo
 *
 */
class SystemMemberModel extends Rpc
{
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * 获取公司所有员工id
	 * @param int $company_id
	 * @return mixed
	 */
	public function getById($member_id)
	{
		return $this->do('SystemMemberModel', 'getById', [$member_id]);
	}
}