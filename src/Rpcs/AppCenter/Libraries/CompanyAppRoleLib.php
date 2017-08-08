<?php
namespace GouuseCore\Rpcs\AppCenter\Libraries;

use GouuseCore\Rpcs\AppCenter\Rpc;

class CompanyAppRoleLib extends Rpc
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 分布式调用初始化公司应用信息
     * @param unknown $company_id
     * @param unknown $member_id
     * @return unknown
     */
    public function addAppAndManage($company_id, $member_id)
    {
    	return $this->do('CompanyAppRoleLib', 'addAppAndManage', [$company_id, $member_id]);
    }
}
