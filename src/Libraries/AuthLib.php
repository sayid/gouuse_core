<?php

namespace GouuseCore\Libraries;


/**
 * 判断是否有权限
 */
class AuthLib extends Lib
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
        if (empty($this->member_info['member_id'])) {
            return ['code' => CodeLib::AUTH_DENY];
        }
        
        if ($this->member_info['member_id'] != $this->company_info['admin_id']) {
            return ['code' => CodeLib::AUTH_DENY];
        }
        return true;
    }
    
    /**
     * 需要应用管理员权限
     * @param $type 0=全部，1=超管，2=子管理员
     * @return number
     */
    public function needCompanyAppAdminAuth($app_id, $type = 0)
    {
        if (empty($this->member_info['member_id'])) {
            return ['code' => CodeLib::AUTH_DENY];
        }
        $app_ids = array_column($this->member_info['manage_apps'], 'app_id');
        if (!in_array($app_id, $app_ids)) {
            //当前用户不能管理该应用
            return ['code' => CodeLib::AUTH_DENY];
        }
        if ($type > 0) {
        	foreach ($this->member_info['manage_apps'] as $row) {
        		if (($type == 1 && $row['super_manage'] == 1) || ($type == 2 && $row['super_manage'] == 0)) {
        			return true;
        		}
        	}
        	return false;
        }
        return true;
    }
    
    
    /**
     * 平台管理员
     * @return number
     */
    public function needSuperAdminAuth()
    {
        if (empty($this->member_info['member_id'])) {
            return ['code' => CodeLib::AUTH_DENY];
        }
        if (isset($this->member_info['super_admin']) && $this->member_info['type'] == 1) {
            return ['code' => CodeLib::AUTH_DENY];
        }
        return true;
    }
    
    
}