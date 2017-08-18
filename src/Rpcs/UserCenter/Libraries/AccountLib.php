<?php
namespace GouuseCore\Rpcs\UserCenter\Libraries;

use GouuseCore\Rpcs\UserCenter\Rpc;

class AccountLib extends Rpc
{
    public function __construct()
    {
        parent::__construct();
    }
    
  
	/**
	 * 校验token
	 * @param unknown $company_id
	 * @return unknown
	 */
    public function check($token)
    {
    	$cache_data = $this->CacheLib->get(md5($token));
    	if ($cache_data) {
    		//从缓存内提取token数据
    		
    		$member_id = $cache_data['member_id'] ?? 0;
    		$company_id = $cache_data['company_id'] ?? 0;
    		$super_admin = $cache_data['super_admin'] ?? 0;
    		$first_login = $cache_data['first_login'] ?? 0;
    		
    		if ($super_admin) {
    			//A后台用户
    			$member_info = RpcHelper::load('UserCenter', 'SystemMemberModel')->getById($member_id);
    		} else {
    			if ($company_id) {
    				$company_info = RpcHelper::load('UserCenter', 'CompanyModel')->getById($member_id);
    				$admin_id = $company_info['admin_id'];
    			}
    			$member_info= RpcHelper::load('UserCenter', 'MemberLib')->memberInfo(['member_id' => $member_id]);
    		}
    		if (empty($member_info['last_login_time'])) {
    			$first_login = 1;
    		}
    		
    		$member_info['first_login'] = $first_login;
    		
    		if ($company_id) {
    			$app = RpcHelper::load('AppCenter', 'Rpc');
    			$app_list = $app->do('CompanyAppRoleLib', 'memberApp', [$member_info['company_id'], $member_info['member_id']]);
    			$member_info['manage_apps'] = $app_list;
    		}
    		
    		$member_info['is_supper_admin'] = $admin_id == $member_id ? 1 : 0;//企业管理员
    		
    	}
    	return $this->do('AccountLib', 'check', [$token]);
    }
}