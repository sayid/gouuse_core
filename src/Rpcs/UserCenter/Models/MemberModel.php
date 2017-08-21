<?php
namespace GouuseCore\Rpcs\UserCenter\Models;

use GouuseCore\Rpcs\UserCenter\Rpc;

/**
 * 用户中心SDK
 * @author zhangyubo
 *
 */
class MemberModel extends Rpc
{
	function __construct() {
		parent::__construct();
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
	
	/**
	 * 获取公司下所有用户简要信息
	 * @param unknown $company_id
	 */
	public function getAllSimple($company_id)
	{
		$cache_key = $this->service_id . StringHelper::getClassname(get_class($this)) . __FUNCTION__ . $company_id;
		$cache_data= $this->CacheLib->get($cache_key);
		if (empty($cache_data_ids)) {
			return $this->do('MemberModel', 'getAllSimple', [$company_id]);
		}
		return $cache_data;
	}
}
