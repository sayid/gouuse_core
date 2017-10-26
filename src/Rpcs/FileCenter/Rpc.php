<?php
namespace GouuseCore\Rpcs\FileCenter;

use GouuseCore\Rpcs\BaseRpc;


/**
 * API SDK基类
 * @author zhangyubo
 *
 */
class Rpc extends BaseRpc
{
	
	protected $host_pre = '/file_service/';
	
	protected $service_id = 1000;
	
	//私有host，各个服务可以自己定义不一样的host地址
	protected $_private_host = '';
	
	public function __construct()
	{
		
	}
	
}
