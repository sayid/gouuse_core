<?php
namespace GouuseCore\Rpcs;

use GouuseCore\Rpcs\BaseRpc;

/**
 * 部门SDK
 * @author zhangyubo
 *
 */
class DepartmentRpc extends BaseRpc
{

	protected $host_pre = '/user_center/';
	
	function __construct() {
		parent::__construct();
	}

	/**
	 * 获取部门信息
	 * @param  [type] $deptId [description]
	 * @return [type]         [description]
	 */
	function getDeptInfo($deptId)
	{
		$url = '/user_center/v3/deptInfo';
		$result = $this->post($url, [], [ 'department_id' => $deptId]);
		return $result;
	}



}
