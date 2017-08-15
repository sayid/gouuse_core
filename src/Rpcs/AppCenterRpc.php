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
	protected $host_pre = '/app_center/';
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 查询当前用户能管理哪些应用
	 * @return \GouuseCore\Rpcs\number[]|\GouuseCore\Rpcs\string[]|mixed
	 */
	function getManageApps()
	{
		$url = '/app_center/v3/app/member_app';
		$result = $this->post($url, [], []);
		return $result;
	}
	
	/**
	 * 获取所有应用
	 * @param array $params
	 */
	public function getAllApps($params = [])
	{
	    $url = '/app_center/v3/app/all';
	    $result = $this->post($url, [], $params);
	    return $result;
	}
	
	/**
	 * 删除用户批量删除应用管理员
	 * @param array $params
	 */
	public function deleteMemberAppRole($params = [])
	{
	    $url = '/app_center/v3/app/delete_member_app_role';
	    $result = $this->post($url, [], $params);
	    return $result;
	}
	
	/**
	 * 添加公司安装应用和增加应用超级管理员
	 * @param integer $company_id
	 * @param integer $member_id
	 */
	public function addDefaultAppAndManage($company_id, $member_id)
	{
	    $url = '/app_center/v3/app/add_app_manage';
	    $result = $this->post($url, [], ['company_id' => $company_id, 'member_id' => $member_id]);
	    return $result;
	}
}
