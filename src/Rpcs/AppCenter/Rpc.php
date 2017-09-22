<?php
namespace GouuseCore\Rpcs\AppCenter;

use GouuseCore\Rpcs\BaseRpc;

/**
 * API SDK基类
 * @author zhangyubo
 *
 */
class Rpc extends BaseRpc
{
	protected $host_pre = '/app_center/';
	
	protected $service_id = 1012;

    //私有host，各个服务可以自己定义不一样的host地址
    protected $_private_host = '';
}
