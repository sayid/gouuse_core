<?php
namespace GouuseCore\Rpcs\EmailCenter;

use GouuseCore\Rpcs\BaseRpc;

/**
 * API SDK基类
 * @author zhangyubo
 *
 */
class Rpc extends BaseRpc
{
	protected $host_pre = '/email_service/';
	
	protected $service_id = 1002;
	
	//私有host，各个服务可以自己定义不一样的host地址
	private $_private_host = '';
	
}
