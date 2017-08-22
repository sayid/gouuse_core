<?php
namespace GouuseCore\Rpcs\UserCenter\Libraries;

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
}