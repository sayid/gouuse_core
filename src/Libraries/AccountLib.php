<?php

namespace GouuseCore\Libraries;


/**
 * 判断是否有权限
 */
class AccountLib extends Lib
{
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 需要公司超级管理员权限
	 */
	public function needCompanyAdminAuth()
	{
		if (empty($this->member_info)) {
			return ['code' => CodeLib::AUTH_DENY];
		}
		if ($this->member_info['member_id'] != $this->company_info['admin_id']) {
			return ['code' => CodeLib::AUTH_DENY];
		}
		return true;
	}
	
	/**
	 * 需要应用管理员权限
	 * @return number
	 */
	public function needCompanyAppAdminAuth()
	{
		if (empty($this->member_info)) {
			return ['code' => CodeLib::AUTH_DENY];
		}
		if (!in_array(env('SERVICE_ID'), $this->member_info['manage_app'])) {
			//当前用户不能管理该应用
			return ['code' => CodeLib::AUTH_DENY];
		}
		return true;
	}
	
	
}