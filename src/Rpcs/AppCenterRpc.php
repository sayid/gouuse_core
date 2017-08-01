<?php
namespace GouuseCore\Rpcs;

use GouuseCore\Rpcs\BaseRpc;

/**
 * 应用中心SDK
 * @author zhangyubo
 *
 */
class AppCenterRpc extends BaseRpc
{
	protected $host;
	
	function __construct()
	{
		$this->host = '';
		
	}
	
	/**
	 * 查询当前用户能管理哪些应用
	 * @return \GouuseCore\Rpcs\number[]|\GouuseCore\Rpcs\string[]|mixed
	 */
	function getManageApps()
	{
		$url = $this->host . '/app_center/v3/app/member_app';
		$result = $this->post($url, [], []);
		return $result;
	}
	
	/**
	 * 获取所有应用
	 * @param array $params
	 */
	public function getAllApps($params = [])
	{
	    $url = $this->host . '/app_center/v3/app/all';
	    $result = $this->post($url, [], $params);
	    return $result;
	}
	

}