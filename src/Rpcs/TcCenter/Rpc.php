<?php
namespace GouuseCore\Rpcs\TcCenter;

use GouuseCore\Rpcs\BaseRpc;

/**
 * API SDK基类
 * @author zhangyubo
 *
 */
class Rpc extends BaseRpc
{
	//服务URI前缀
	protected $host_pre = '/transaction_center/';
	
	protected $service_id = 1012;
	
	public function __construct()
	{
	}
}
