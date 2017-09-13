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
        	if (!isset($this->member_info['manage_apps'])) {
        		return ['code' => CodeLib::AUTH_DENY];
        	}
        	foreach ($this->member_info['manage_apps'] as $row) {
        		if (($type == 1 && $row['super_manage'] == 1) || ($type == 2 && $row['super_manage'] == 0)) {
        			return true;
        		}
        	}
        	return ['code' => CodeLib::AUTH_DENY];
        }
        return true;
    }
    
    /**
     * 获取应用管理员设置权限和数据权限
     * @param unknown $app_id 应用id
     * @param string $set_auth 获取应用设置权限
     * @param string $data_auth 获取数据权限
     * @return number[]|boolean
     */
    public function needCompanyAppSetAuth($app_id, $set_auth = false, $data_auth = false)
    {
        if (empty($this->member_info['member_id'])) {
            return ['code' => CodeLib::AUTH_DENY];
        }
        if (!isset($this->member_info['manage_apps'])) {
        	return ['code' => CodeLib::AUTH_DENY];
        }
        $app_ids = array_column($this->member_info['manage_apps'], 'app_id');
        if (!in_array($app_id, $app_ids)) {
            //当前用户不能管理该应用
            return ['code' => CodeLib::AUTH_DENY];
        }
    
        foreach ($this->member_info['manage_apps'] as $row) {
            if ($row['app_id'] == $app_id) {
                $app_info = $row;
            }
        }
        if (!isset($app_info) || empty($app_info)) {
            return ['code' => CodeLib::AUTH_DENY];
        }
    
        if ($set_auth === true && $data_auth === true) {    //获取设置权限，数据权限
            if ($app_info['set_permission'] == 1 && $app_info['data_permission']) {
                return true;
            }
        } elseif ($set_auth === true && $data_auth === false) { //获取设置权限
            if ($app_info['set_permission'] == 1) {
                return true;
            }
        } elseif ($set_auth === false && $data_auth === true) { //获取数据权限
            if ($app_info['data_permission'] == 1) {
                return true;
            }
        }
        return ['code' => CodeLib::AUTH_DENY];
    }
    
    /**
     * 获取应用对应权限
     * @param 应用id $app_id
     * @param 用户id $member_id
     * @param 权限字段 $field set_permission:设置权限,data_permission:数据权限,admin_permission:管理权限,reimburse_permission:报销存根权限
     *   $field  eg: "set_permission, admin_permission" 多个字段用逗号分隔
     */
    public function needMemberAppSetAuth($app_id, $member_id, $field)
    {
        $field = explode(',', $field);
        foreach ($field as $item) {
            if (!in_array(trim($item), ['set_permission', 'data_permission', 'admin_permission', 'reimburse_permission'])) {
                return ['code' => CodeLib::AUTH_PARAM_ERROR];
            }
        }
    
        if (empty($this->member_info['member_id'])) {
            return ['code' => CodeLib::AUTH_DENY];
        }
        if (!isset($this->member_info['manage_apps'])) {
            return ['code' => CodeLib::AUTH_DENY];
        }
        $app_ids = array_column($this->member_info['manage_apps'], 'app_id');
        if (empty($app_ids)) {
            return ['code' => CodeLib::AUTH_DENY];
        }
        if (!in_array($app_id, $app_ids)) {
            //当前用户不能管理该应用
            return ['code' => CodeLib::AUTH_DENY];
        }
    
        foreach ($this->member_info['manage_apps'] as $val) {
            if ($val['app_id'] == $app_id) {
                $app_info = $val;
            }
        }
    
        if (!isset($app_info) || empty($app_info)) {
            return ['code' => CodeLib::AUTH_DENY];
        }
        $auth = false;
        foreach ($field as $row) {
            if($app_info[trim($row)] == 1) {
                $auth = true;
            } else {
                $auth = false;
            }
        }
        //应用超管有权限
        if ($auth === true || $app_info['super_manage'] == 1) {
            return ['code' => 0];
        }
        return ['code' => CodeLib::AUTH_DENY];
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
            //是平台管理员 并且 type=1
        	return true;
        }
        return ['code' => CodeLib::AUTH_DENY];
    }
    
    
}