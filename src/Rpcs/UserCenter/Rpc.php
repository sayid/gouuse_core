<?php
namespace GouuseCore\Rpcs\UserCenter;

use GouuseCore\Rpcs\BaseRpc;


/**
 * API SDK基类
 * @author zhangyubo
 *
 */
class Rpc extends BaseRpc
{
	
	protected $host_pre = '/user_center/';
	
	protected $service_id = 1005;
	
	//私有host，各个服务可以自己定义不一样的host地址
	private $_private_host = '';
	
	public function __construct()
	{
		
	}
	
}
