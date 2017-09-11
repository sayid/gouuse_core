<?php
namespace GouuseCore\Rpcs\FlowCenter;

use GouuseCore\Rpcs\BaseRpc;

/**
 * API SDK基类
 * @author zhangyubo
 *
 */
class Rpc extends BaseRpc
{
	protected $host_pre = '/flow_center/';
	
	protected $service_id = 1004;
	
	//私有host，各个服务可以自己定义不一样的host地址
	private $_private_host = '';
}
