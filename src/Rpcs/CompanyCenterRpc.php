<?php
namespace GouuseCore\Rpcs;

use GouuseCore\Rpcs\BaseRpc;

/**
 * 用户中心SDK
 * @author zhangyubo
 *
 */
class CompanyCenterRpc extends BaseRpc
{
	protected $host_pre = '/company_center/';
	
	function __construct()
	{
		
	}
	
	function getById($company_id = 0)
	{
		$url = '/company_center/v3/view';
		$result = $this->post($url, [], ['company_id' => $company_id]);
		return $result;
	}	

}