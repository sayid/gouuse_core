<?php
namespace GouuseCore\Rpcs\AppCenter\Libraries;

use GouuseCore\Rpcs\AppCenter\Rpc;

class AppLib extends Rpc
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getAllApp($type = "all", $company_id = "0")
    {
        return $this->do('AppLib', 'getAllApp', [$type, $company_id]);
    }
}