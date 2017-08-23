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
}
