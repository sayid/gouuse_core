<?php
namespace GouuseCore\Rpcs\AttendenceCenter;

use GouuseCore\Rpcs\BaseRpc;

/**
 * API SDK基类
 * @author zhangyubo
 *
 */
class Rpc extends BaseRpc
{
	protected $host_pre = '/attendence_center/';
	
	protected $service_id = 101;
	
	/**
	 * 执行考勤统计接口
	 * @param array $data
	 * @return \GouuseCore\Rpcs\number[]|\GouuseCore\Rpcs\string[]|mixed
	 */
	function doAttendenceStatistic($data = []) {
		$url = $this->host_pre . 'v3/statistics/statistics_do';
		$result = $this->postOutside($url, [], $data);
		return $result;
	}
	
}
