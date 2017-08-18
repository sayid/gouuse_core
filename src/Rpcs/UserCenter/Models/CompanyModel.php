<?php
namespace GouuseCore\Rpcs\UserCenter\Models;

use GouuseCore\Rpcs\UserCenter\Rpc;

/**
 * 用户中心SDK
 * @author zhangyubo
 *
 */
class CompanyModel extends Rpc
{
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * 获取公司所信息
	 * @param int $company_id
	 * @return mixed
	 */
	public function getById($company_id)
	{
		return $this->do('CompanyModel', 'getById', [$company_id]);
	}
}