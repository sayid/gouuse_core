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
	protected $host;

	function __construct() {
		$this->host = '';

	}

	/**
	 * 获取部门信息
	 * @param  [type] $deptId [description]
	 * @return [type]         [description]
	 */
	function getDeptInfo($deptId)
	{
		$url = $this->host . '/user_center/v3/deptInfo';
		$result = $this->post($url, [], [ 'department_id' => $deptId]);
		return $result;
	}



}
