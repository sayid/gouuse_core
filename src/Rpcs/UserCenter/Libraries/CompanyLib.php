<?php
namespace GouuseCore\Rpcs\UserCenter\Libraries;

use GouuseCore\Rpcs\UserCenter\Rpc;

class CompanyLib extends Rpc
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 添加公司默认角色
     * @param unknown $company_id
     */
    public function createDefaultRole($company_id)
    {
        return $this->do('CompanyLib', 'createDefaultRole', [$company_id]);
    }
    
    /**
     * 添加公司默认职位
     * @param unknown $company_id
     */
    public function createDefaultPosition($company_id)
    {
        return $this->do('CompanyLib', 'createDefaultPosition', [$company_id]);
    }
}