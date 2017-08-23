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
	
	
	public function __construct()
	{
		
	}
	
}
