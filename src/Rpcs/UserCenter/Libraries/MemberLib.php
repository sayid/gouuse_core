<?php
namespace GouuseCore\Rpcs\UserCenter\Libraries;

use GouuseCore\Helpers\OptionHelper;
use GouuseCore\Rpcs\UserCenter\Rpc;

class MemberLib extends Rpc
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function memberInfo($extra = [])
	{
		
		return $this->do('MemberLib', 'memberInfo', [$extra]);
	}
	
	/**
	 * 查询全公司员工列表简要信息 可以依靠关键词、部门id、状态查询
	 * @param array $extra
	 * @return boolean|unknown
	 */
	public function getMemberListSimple($extra = [])
	{
		$company_id = $extra['company_id'] ?? 0;
		$member_list = $this->MemberModel->getAllSimple($company_id);
		$member_result = [];
		if (isset($extra['keywords']) || isset($extra['department_id'])) {
			foreach ($member_list as $member_row) {
				$is_match = false;
				if (isset($extra['keywords'])) {
					if (strpos($member_row['member_name'], $extra['keywords']) !== false  ||
							strpos($member_row['name_initial_all'], $extra['keywords']) !== false
							) {
								$is_match = true;
							}
							if (strpos($member_row['work_number'], $extra['keywords']) !== false) {
								$is_match = true;
							}
				}
				if (isset($extra['department_id']) && $member_row['department_id'] == $extra['department_id']) {
					$is_match = true;
				}
				if ($is_match) {
					$member_result[] = $member_row;
				}
			}
		} else {
			$member_result = $member_list;
		}
		
		return $member_result;
	}


    /**
     * 获取用户信息
     * @param $extra   查询条件-需要包含公司id，用户状态默认是在职用户
     * @param string   $need_field  用户需要查询的字段，默认为空为全部
     * @return array
     */
    public function getMemberList($extra, $need_field = '')
    {
        if (!empty($need_field)) {
            $need_field = explode(',', $need_field);
        }
        $company_id = $extra['company_id'] ?? 0;
        $status = $extra['status'] ?? 1;
        $member_list = $this->MemberModel->getAllSimple($company_id, $status);
        // 获取部门列表
        $department_list = $this->do('DepartmentModel', 'getAllWithId', [$company_id]);
        // 获取职位列表
        $position_list = $this->do('CompanyPositionLib', 'getNameList', [$company_id]);
        // 转换
        $member_opt = OptionHelper::getOption('member');
        $sex = $member_opt['sex'];
        $blood_type = $member_opt['blood_type'];
        $list = [];
        if (!empty($member_list)) {
            foreach ($member_list as $key => $val) {
                // 处理用户数据
                $val['department_name'] = $department_list[$val['department_id']]['department_name'] ?? '';
                $val['position_name'] = $position_list[$val['position_id']] ?? '';
                $val['sex_text'] = $sex[$val['sex']] ?? '';
                $val['blood_type_text'] = $blood_type[$val['blood_type']] ?? '';
                // 过滤
                // 部门查询
                if (!empty($extra['department_id'])) {
                    $department_ids = explode(',', $extra['department_id']);
                    if (!in_array($val['department_id'], $department_ids)) {
                        continue;
                    }
                }
                // 角色id查询
                if (isset($extra['role_id'])) {
                    $role_ids = explode(',', $extra['role_id']);
                    $in_role = false;
                    if (!empty($val['role_group'])) {
                        $role_info = json_decode($val['role_group'], true);
                        foreach ($role_info as $role_id) {
                            if (array_intersect($role_ids, $role_id)) {
                                $in_role = true;
                            }
                        }
                    }
                    if (!$in_role) {
                        continue;
                    }
                }
                // 职位id查询
                if (!empty($extra['position_id'])) {
                    $position_ids = explode(',', $extra['position_id']);
                    if (!in_array($val['position_id'], $position_ids)) {
                        continue;
                    }
                }
                // 用户id查询
                if (!empty($extra['member_id'])) {
                    $member_ids = explode(',', $extra['member_id']);
                    if (!in_array($val['member_id'], $member_ids)) {
                        continue;
                    }
                }
                // 过滤前面已经查询了的条件
                $filter_field = 'member_id,company_id,position_id,status,role_id,department_id';
                $search_extra = ArrayHelper::filterArray($filter_field, $extra, 'nokeep');
                $match = true;
                if (!empty($search_extra)) {
                    foreach ($search_extra as $search_key => $search_value) {
                        if (array_key_exists($search_key, $val) == false) {
                            continue;
                        } else {
                            if ($val[$search_key] != $search_value) {
                                $match = false;
                                continue;
                            }
                        }
                    }
                }
                if ($match == false) {
                    continue;
                }
                // 过滤字段
                if (!empty($need_field)) {
                    $val = ArrayHelper::filterArray($need_field, $val);
                }
                $list[] = $val;
            }
        }
        return $list;
    }
}