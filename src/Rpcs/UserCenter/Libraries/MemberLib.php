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
}