<?php
namespace GouuseCore\Rpcs\AppCenter\Libraries;

use GouuseCore\Rpcs\AppCenter\Rpc;

class CompanyAppLib extends Rpc
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 检测应用是否安装
     * @param unknown $app_id
     * @param unknown $company_id
     * @return unknown
     */
    public function checkInstall($app_id, $company_id)
    {
    	return $this->do('CompanyAppLib', 'checkInstall', [$app_id, $company_id]);
    }
}
